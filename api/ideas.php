<?php
// api/ideas.php  –  RESTful endpoint for ideas
// GET    ?action=list|get&id=X|my|review|dashboard
// POST   ?action=submit|draft|review_action
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/score.php';   // provides computeIdeaScore() / saveIdeaScore()

$user   = requireAuth();
$action = $_GET['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

// ── LIST all ideas ─────────────────────────────────────────────────
if ($action === 'list') {
    $where  = [];
    $params = [];

    // Employees only see their own; managers see their dept; admin/executive see all
    if ($user['role'] === 'employee') {
        $where[]  = '(i.submitter_id = ? OR i.co_suggester_1_id = ? OR i.co_suggester_2_id = ?)';
        $params   = array_merge($params, [$user['id'], $user['id'], $user['id']]);
    } elseif ($user['role'] === 'manager') {
        $where[]  = '(i.submitter_id IN (SELECT id FROM users WHERE manager_id = ?) OR i.submitter_id = ?)';
        $params   = array_merge($params, [$user['id'], $user['id']]);
    }

    if (!empty($_GET['status'])) {
        $where[]  = 'i.status = ?';
        $params[] = $_GET['status'];
    }
    if (!empty($_GET['search'])) {
        $where[]  = '(i.title LIKE ? OR i.idea_code LIKE ?)';
        $s        = '%' . $_GET['search'] . '%';
        $params   = array_merge($params, [$s, $s]);
    }
    if (!empty($_GET['impact'])) {
        $where[]  = 'i.impact_level = ?';
        $params[] = $_GET['impact'];
    }

    $sql = "SELECT i.*, u.name AS submitter_name, u.department, u.avatar_initials,
                   c1.name AS co1_name, c2.name AS co2_name
            FROM ideas i
            JOIN users u ON u.id = i.submitter_id
            LEFT JOIN users c1 ON c1.id = i.co_suggester_1_id
            LEFT JOIN users c2 ON c2.id = i.co_suggester_2_id"
         . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
         . " ORDER BY i.updated_at DESC LIMIT 100";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    respond(['success' => true, 'ideas' => $stmt->fetchAll()]);
}

// ── MY ideas ──────────────────────────────────────────────────────
if ($action === 'my') {
    $stmt = db()->prepare(
        "SELECT i.*, c1.name AS co1_name, c2.name AS co2_name
         FROM ideas i
         LEFT JOIN users c1 ON c1.id = i.co_suggester_1_id
         LEFT JOIN users c2 ON c2.id = i.co_suggester_2_id
         WHERE i.submitter_id = ? OR i.co_suggester_1_id = ? OR i.co_suggester_2_id = ?
         ORDER BY i.updated_at DESC"
    );
    $stmt->execute([$user['id'], $user['id'], $user['id']]);
    respond(['success' => true, 'ideas' => $stmt->fetchAll()]);
}

// ── REVIEW QUEUE (manager / admin / executive) ─────────────────────
if ($action === 'review') {
    requireRole('manager', 'admin', 'executive');
    $params = [];
    $extra  = '';
    if ($user['role'] === 'manager') {
        $extra    = 'AND i.submitter_id IN (SELECT id FROM users WHERE manager_id = ?)';
        $params[] = $user['id'];
    }
    $stmt = db()->prepare(
        "SELECT i.*, u.name AS submitter_name, u.department, u.avatar_initials
         FROM ideas i
         JOIN users u ON u.id = i.submitter_id
         WHERE i.status IN ('Submitted','Under Review') $extra
         ORDER BY i.ai_score DESC, i.submitted_at ASC"
    );
    $stmt->execute($params);
    respond(['success' => true, 'ideas' => $stmt->fetchAll()]);
}

// ── GET single idea detail ─────────────────────────────────────────
if ($action === 'get') {
    $id   = (int)($_GET['id'] ?? 0);
    $stmt = db()->prepare(
        "SELECT i.*, u.name AS submitter_name, u.department, u.business_unit,
                u.avatar_initials, u.email AS submitter_email,
                c1.name AS co1_name, c2.name AS co2_name,
                m.name AS manager_name
         FROM ideas i
         JOIN  users u  ON u.id  = i.submitter_id
         LEFT JOIN users c1 ON c1.id = i.co_suggester_1_id
         LEFT JOIN users c2 ON c2.id = i.co_suggester_2_id
         LEFT JOIN users m  ON m.id  = u.manager_id
         WHERE i.id = ?"
    );
    $stmt->execute([$id]);
    $idea = $stmt->fetch();
    if (!$idea) respond(['success' => false, 'error' => 'Idea not found'], 404);

    // Attachments
    $att = db()->prepare("SELECT * FROM idea_attachments WHERE idea_id = ?");
    $att->execute([$id]);
    $idea['attachments'] = $att->fetchAll();

    // Workflow timeline
    $wf = db()->prepare(
        "SELECT w.*, u.name AS actor_name, u.role AS actor_role
         FROM idea_workflow w JOIN users u ON u.id = w.actor_id
         WHERE w.idea_id = ? ORDER BY w.created_at ASC"
    );
    $wf->execute([$id]);
    $idea['workflow'] = $wf->fetchAll();

    respond(['success' => true, 'idea' => $idea]);
}

// ── SUBMIT / SAVE DRAFT ───────────────────────────────────────────
if (in_array($action, ['submit', 'draft'], true) && $method === 'POST') {
    $b = json_decode(file_get_contents('php://input'), true) ?? [];

    $title    = trim($b['title'] ?? '');
    $sit      = trim($b['present_situation'] ?? '');
    $sol      = trim($b['proposed_solution'] ?? '');
    $impacts  = trim($b['impact_areas'] ?? '');
    $impLvl   = $b['impact_level'] ?? 'Medium';
    $tangible = trim($b['tangible_benefit'] ?? '');
    $intang   = trim($b['intangible_benefit'] ?? '');
    $co1      = !empty($b['co_suggester_1_id']) ? (int)$b['co_suggester_1_id'] : null;
    $co2      = !empty($b['co_suggester_2_id']) ? (int)$b['co_suggester_2_id'] : null;
    $editId   = !empty($b['id']) ? (int)$b['id'] : null;

    if (!$title || !$sit || !$sol) {
        respond(['success' => false, 'error' => 'Title, present situation and proposed solution are required.'], 400);
    }

    // Compute AI score from submitted data
    $ideaData = [
        'title'              => $title,
        'present_situation'  => $sit,
        'proposed_solution'  => $sol,
        'impact_areas'       => $impacts,
        'impact_level'       => $impLvl,
        'tangible_benefit'   => $tangible,
        'intangible_benefit' => $intang,
        'co_suggester_1_id'  => $co1,
        'co_suggester_2_id'  => $co2,
    ];
    $ai = computeAIScoreWithReason($ideaData);
    $aiScore = $ai['score'];
    $aiReason = $ai['reason'];
    
     // If editing an existing idea, ensure user has permission

    $status      = $action === 'submit' ? 'Submitted' : 'Draft';
    $submittedAt = $action === 'submit' ? date('Y-m-d H:i:s') : null;
    $pdo         = db();

    if ($editId) {
        $pdo->prepare(
            "UPDATE ideas SET 
            title=?,present_situation=?,proposed_solution=?,
            impact_areas=?,impact_level=?,tangible_benefit=?,intangible_benefit=?,
            co_suggester_1_id=?,co_suggester_2_id=?,
            status=?,submitted_at=COALESCE(submitted_at,?),
            ai_score=?, ai_reason=?,
            updated_at=NOW()
            WHERE id=? AND submitter_id=?"
        )->execute([
            $title,$sit,$sol,$impacts,$impLvl,$tangible,$intang,
            $co1,$co2,$status,$submittedAt,
            $aiScore,$aiReason,
            $editId,$user['id']
        ]);
        $ideaId = $editId;
    } else {
        $code = generateIdeaCode();
        $pdo->prepare(
            "INSERT INTO ideas (
                idea_code,title,present_situation,proposed_solution,
                impact_areas,impact_level,tangible_benefit,intangible_benefit,
                co_suggester_1_id,co_suggester_2_id,status,submitter_id,submitted_at,
                ai_score, ai_reason
                )
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        )->execute([
            $code,$title,$sit,$sol,$impacts,$impLvl,$tangible,$intang,
            $co1,$co2,$status,$user['id'],$submittedAt,
            $aiScore,$aiReason
            ]);
        $ideaId = (int)$pdo->lastInsertId();
    }

    if ($action === 'submit') {
        addWorkflow($ideaId, $user['id'], 'Submitted');
        addPoints($user['id'], POINTS_SUBMIT);
        $_SESSION['user']['points'] += POINTS_SUBMIT;

        // Notify manager
        if ($user['manager_id']) {
            addNotification(
                $user['manager_id'],
                'New Idea Submitted',
                $user['name'] . ' submitted a new idea. Please review it in your queue.',
                $ideaId
            );
        }
    }

    $stmt = $pdo->prepare("SELECT idea_code FROM ideas WHERE id=?");
    $stmt->execute([$ideaId]);
    $row  = $stmt->fetch();

    respond([
        'success'      => true,
        'idea_id'      => $ideaId,
        'idea_code'    => $row['idea_code'],
        'ai_score'     => $aiScore,
        'points_added' => $action === 'submit' ? POINTS_SUBMIT : 0,
    ]);
}

// ── MANAGER REVIEW (approve / reject / implement) ─────────────────
if ($action === 'review_action' && $method === 'POST') {
    requireRole('manager', 'admin', 'executive');
    $b       = json_decode(file_get_contents('php://input'), true) ?? [];
    $ideaId  = (int)($b['idea_id'] ?? 0);
    $decision= $b['decision'] ?? '';   // Approved | Rejected | Implemented | Under Review
    $comment = trim($b['comment'] ?? '');

    if (!$ideaId || !in_array($decision, ['Approved','Rejected','Implemented','Under Review'], true)) {
        respond(['success' => false, 'error' => 'Invalid request.'], 400);
    }

    $pdo  = db();
    $stmt = $pdo->prepare("SELECT * FROM ideas WHERE id=?");
    $stmt->execute([$ideaId]);
    $idea = $stmt->fetch();
    if (!$idea) respond(['success' => false, 'error' => 'Idea not found.'], 404);

    // Prevent self-approval
    if ((int)$idea['submitter_id'] === (int)$user['id']) {
        respond(['success' => false, 'error' => 'You cannot review or approve your own idea.'], 403);
    }

    $wfAction = match($decision) {
        'Approved'    => 'Approved',
        'Rejected'    => 'Rejected',
        'Implemented' => 'Implemented',
        default       => 'Reviewed',
    };

    // Idempotency guard — prevent duplicate workflow entries within 10 s
    $dupCheck = $pdo->prepare(
        "SELECT COUNT(*) FROM idea_workflow WHERE idea_id=? AND actor_id=? AND action=? AND created_at > NOW() - INTERVAL 10 SECOND"
    );
    $dupCheck->execute([$ideaId, $user['id'], $wfAction]);
    if ((int)$dupCheck->fetchColumn() > 0) {
        respond(['success' => false, 'error' => 'Duplicate action detected. Please wait a moment before retrying.'], 429);
    }

    $pdo->prepare("UPDATE ideas SET status=?,updated_at=NOW() WHERE id=?")
        ->execute([$decision, $ideaId]);

    addWorkflow($ideaId, $user['id'], $wfAction, $comment ?: null);

    // Award points to submitter
    $pts = match($decision) {
        'Approved'    => POINTS_APPROVED,
        'Implemented' => POINTS_IMPLEMENTED,
        default       => 0,
    };
    if ($pts > 0) {
        addPoints($idea['submitter_id'], $pts);
        $pdo->prepare("UPDATE ideas SET points_awarded = points_awarded + ? WHERE id=?")
            ->execute([$pts, $ideaId]);
    }

    // Notify submitter
    $msg = match($decision) {
        'Approved'    => "Your idea {$idea['idea_code']} was Approved. +{$pts} points awarded.",
        'Rejected'    => "Your idea {$idea['idea_code']} was Rejected. Feedback: {$comment}",
        'Implemented' => "Your idea {$idea['idea_code']} is now Implemented. +{$pts} points awarded.",
        default       => "Your idea {$idea['idea_code']} is Under Review.",
    };
    addNotification($idea['submitter_id'], "Idea {$decision}", $msg, $ideaId);

    respond(['success' => true, 'decision' => $decision, 'points_awarded' => $pts]);
}

// ── DASHBOARD stats ───────────────────────────────────────────────
if ($action === 'dashboard') {
    $pdo  = db();
    $uid  = $user['id'];
    $role = $user['role'];

    if ($role === 'employee') {
        $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM ideas WHERE submitter_id=?");
        $totalStmt->execute([$uid]);
    } else {
        $totalStmt = $pdo->query("SELECT COUNT(*) FROM ideas WHERE status != 'Draft'");
    }
    $total = (int)$totalStmt->fetchColumn();

    $counts = [];
    foreach (['Submitted','Under Review','Approved','Implemented','Rejected'] as $s) {
        $q = $role === 'employee'
            ? $pdo->prepare("SELECT COUNT(*) FROM ideas WHERE submitter_id=? AND status=?")
            : $pdo->prepare("SELECT COUNT(*) FROM ideas WHERE status=?");
        if ($role === 'employee') $q->execute([$uid, $s]);
        else $q->execute([$s]);
        $counts[$s] = (int)$q->fetchColumn();
    }

    // Recent activity (last 10 workflow entries)
    $recent = $pdo->query(
        "SELECT w.*, u.name AS actor_name, i.idea_code, i.title
         FROM idea_workflow w
         JOIN users u ON u.id = w.actor_id
         JOIN ideas i ON i.id = w.idea_id
         ORDER BY w.created_at DESC LIMIT 10"
    )->fetchAll();

    // Fetch current points from DB (authoritative)
    $ptsStmt = $pdo->prepare("SELECT points FROM users WHERE id=?");
    $ptsStmt->execute([$uid]);
    $userPoints = (int)($ptsStmt->fetchColumn() ?: $user['points']);

    respond([
        'success'      => true,
        'total'        => $total,
        'counts'       => $counts,
        'recent'       => $recent,
        'user_points'  => $userPoints,
    ]);
}

respond(['success' => false, 'error' => 'Unknown action'], 400);
