<?php
// api/users.php  –  Users, leaderboard, notifications, analytics
require_once __DIR__ . '/config.php';

$user   = requireAuth();
$action = $_GET['action'] ?? 'list';
$pdo    = db();

// ── List employees (for co-suggester search) ──────────────────────
if ($action === 'list') {
    $q    = '%' . trim($_GET['q'] ?? '') . '%';
    $stmt = $pdo->prepare(
        "SELECT id, employee_id, name, department, email, role, avatar_initials
         FROM users WHERE (name LIKE ? OR employee_id LIKE ? OR email LIKE ?)
         AND id != ? LIMIT 20"
    );
    $stmt->execute([$q, $q, $q, $user['id']]);
    respond(['success' => true, 'users' => $stmt->fetchAll()]);
}

// ── Leaderboard ───────────────────────────────────────────────────
if ($action === 'leaderboard') {
    $period = $_GET['period'] ?? 'all';   // all | monthly | quarterly | yearly

    $dateFilter = '';
    switch ($period) {
        case 'monthly':
            $dateFilter = "AND MONTH(i.submitted_at)=MONTH(NOW()) AND YEAR(i.submitted_at)=YEAR(NOW())";
            break;
        case 'quarterly':
            $dateFilter = "AND QUARTER(i.submitted_at)=QUARTER(NOW()) AND YEAR(i.submitted_at)=YEAR(NOW())";
            break;
        case 'yearly':
            $dateFilter = "AND YEAR(i.submitted_at)=YEAR(NOW())";
            break;
    }

    // Individual leaderboard — includes average AI quality score
    $stmt = $pdo->query(
        "SELECT u.id, u.name, u.department, u.business_unit, u.points, u.avatar_initials,
                COUNT(DISTINCT i.id) AS idea_count,
                SUM(CASE WHEN i.status='Implemented' THEN 1 ELSE 0 END) AS implemented_count,
                ROUND(AVG(CASE WHEN i.status != 'Draft' THEN i.ai_score ELSE NULL END), 1) AS avg_score
         FROM users u
         LEFT JOIN ideas i ON i.submitter_id = u.id $dateFilter
         WHERE u.role = 'employee'
         GROUP BY u.id
         ORDER BY u.points DESC
         LIMIT 20"
    );
    $individuals = $stmt->fetchAll();

    // Department leaderboard
    $dstmt = $pdo->query(
        "SELECT u.department,
                SUM(u.points)          AS dept_points,
                COUNT(DISTINCT u.id)   AS member_count,
                COUNT(DISTINCT i.id)   AS idea_count,
                ROUND(AVG(CASE WHEN i.status != 'Draft' THEN i.ai_score ELSE NULL END), 1) AS avg_score
         FROM users u
         LEFT JOIN ideas i ON i.submitter_id = u.id $dateFilter
         WHERE u.role = 'employee'
         GROUP BY u.department
         ORDER BY dept_points DESC"
    );
    $departments = $dstmt->fetchAll();

    // Top ideas by AI quality score
    $topIdeasStmt = $pdo->query(
        "SELECT i.id, i.idea_code, i.title, i.ai_score, i.status,
                i.impact_level, i.impact_areas,
                u.name AS submitter_name, u.department
         FROM ideas i
         JOIN users u ON u.id = i.submitter_id
         WHERE i.status != 'Draft' AND i.ai_score > 0
         ORDER BY i.ai_score DESC
         LIMIT 5"
    );
    $topIdeas = $topIdeasStmt->fetchAll();

    respond([
        'success'     => true,
        'individuals' => $individuals,
        'departments' => $departments,
        'top_ideas'   => $topIdeas,
    ]);
}

// ── Notifications ─────────────────────────────────────────────────
if ($action === 'notifications') {
    $stmt = $pdo->prepare(
        "SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20"
    );
    $stmt->execute([$user['id']]);
    $notifs = $stmt->fetchAll();
    $unread = array_filter($notifs, fn($n) => !$n['is_read']);
    respond(['success' => true, 'notifications' => $notifs, 'unread_count' => count($unread)]);
}

// ── Mark notifications read ───────────────────────────────────────
if ($action === 'mark_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$user['id']]);
    respond(['success' => true]);
}

// ── Analytics (admin / executive / manager) ───────────────────────
if ($action === 'analytics') {
    requireRole('admin', 'executive', 'manager');

    // Monthly submission trend (last 12 months)
    $trend = $pdo->query(
        "SELECT DATE_FORMAT(submitted_at,'%Y-%m') AS month,
                COUNT(*) AS total,
                SUM(CASE WHEN status='Implemented' THEN 1 ELSE 0 END) AS implemented,
                ROUND(AVG(ai_score), 1) AS avg_score
         FROM ideas WHERE submitted_at IS NOT NULL
         GROUP BY month ORDER BY month DESC LIMIT 12"
    )->fetchAll();

    // Impact area distribution
    $impactRaw = $pdo->query(
        "SELECT impact_areas FROM ideas WHERE impact_areas IS NOT NULL"
    )->fetchAll(PDO::FETCH_COLUMN);
    $impactCount = [];
    foreach ($impactRaw as $row) {
        foreach (explode(',', $row) as $area) {
            $area = trim($area);
            if ($area) $impactCount[$area] = ($impactCount[$area] ?? 0) + 1;
        }
    }
    arsort($impactCount);

    // Status summary
    $statusSummary = $pdo->query(
        "SELECT status, COUNT(*) AS cnt FROM ideas GROUP BY status"
    )->fetchAll();

    // Overall quality score distribution
    $scoreStmt = $pdo->query(
        "SELECT
            SUM(CASE WHEN ai_score >= 75 THEN 1 ELSE 0 END) AS high_quality,
            SUM(CASE WHEN ai_score >= 50 AND ai_score < 75 THEN 1 ELSE 0 END) AS medium_quality,
            SUM(CASE WHEN ai_score > 0 AND ai_score < 50 THEN 1 ELSE 0 END) AS low_quality,
            ROUND(AVG(CASE WHEN status != 'Draft' THEN ai_score ELSE NULL END), 1) AS overall_avg
         FROM ideas WHERE status != 'Draft'"
    );
    $scoreStats = $scoreStmt->fetch() ?: [];

    respond([
        'success'            => true,
        'trend'              => $trend,
        'impact_distribution'=> $impactCount,
        'status_summary'     => $statusSummary,
        'score_stats'        => $scoreStats,
    ]);
}

// ── Audit trail ───────────────────────────────────────────────────
if ($action === 'audit') {
    requireRole('admin', 'manager', 'executive');
    $stmt = $pdo->query(
        "SELECT w.*, u.name AS actor_name, u.role AS actor_role,
                i.idea_code, i.title AS idea_title,
                s.name AS submitter_name, s.department
         FROM idea_workflow w
         JOIN users u ON u.id = w.actor_id
         JOIN ideas i ON i.id = w.idea_id
         JOIN users s ON s.id = i.submitter_id
         ORDER BY w.created_at DESC LIMIT 200"
    );
    respond(['success' => true, 'audit' => $stmt->fetchAll()]);
}

// ── Profile update ────────────────────────────────────────────────
if ($action === 'profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $b     = json_decode(file_get_contents('php://input'), true) ?? [];
    $phone = trim($b['phone'] ?? '');
    $pdo->prepare("UPDATE users SET phone=? WHERE id=?")->execute([$phone, $user['id']]);
    $_SESSION['user']['phone'] = $phone;
    respond(['success' => true]);
}

respond(['success' => false, 'error' => 'Unknown action'], 400);
