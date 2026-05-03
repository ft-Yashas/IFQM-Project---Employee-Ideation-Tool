<?php
require_once __DIR__ . '/config.php';

$user   = requireAuth();
$action = $_GET['action'] ?? '';
$pdo    = db();

function voteStats(int $ideaId, int $userId, PDO $pdo): array {
    $s = $pdo->prepare(
        "SELECT COUNT(*) AS vote_count,
                ROUND(AVG(rating), 1) AS avg_rating,
                MAX(CASE WHEN user_id = ? THEN rating ELSE NULL END) AS user_rating
         FROM idea_votes WHERE idea_id = ?"
    );
    $s->execute([$userId, $ideaId]);
    $row = $s->fetch();
    $vc  = (int)($row['vote_count'] ?? 0);
    $ar  = $vc > 0 ? (float)($row['avg_rating'] ?? 0) : 0.0;
    $ur  = ($row['user_rating'] !== null) ? (int)$row['user_rating'] : null;
    return ['vote_count' => $vc, 'avg_rating' => $ar, 'user_rating' => $ur];
}

// POST ?action=vote  {idea_id, rating}
if ($action === 'vote' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $b      = json_decode(file_get_contents('php://input'), true) ?? [];
    $ideaId = (int)($b['idea_id'] ?? 0);
    $rating = (int)($b['rating']  ?? 0);

    if (!$ideaId || $rating < 1 || $rating > 5) {
        respond(['success' => false, 'error' => 'Invalid request — idea_id and rating (1–5) required.'], 400);
    }

    $row = $pdo->prepare("SELECT submitter_id FROM ideas WHERE id = ?");
    $row->execute([$ideaId]);
    $idea = $row->fetch();
    if (!$idea) respond(['success' => false, 'error' => 'Idea not found.'], 404);
    if ((int)$idea['submitter_id'] === (int)$user['id']) {
        respond(['success' => false, 'error' => 'You cannot vote on your own idea.'], 403);
    }

    $pdo->prepare(
        "INSERT INTO idea_votes (idea_id, user_id, rating)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE rating = ?, updated_at = NOW()"
    )->execute([$ideaId, $user['id'], $rating, $rating]);

    respond(array_merge(['success' => true], voteStats($ideaId, $user['id'], $pdo)));
}

// GET ?action=stats&idea_id=X
if ($action === 'stats') {
    $ideaId = (int)($_GET['idea_id'] ?? 0);
    if (!$ideaId) respond(['success' => false, 'error' => 'Missing idea_id.'], 400);
    respond(array_merge(['success' => true], voteStats($ideaId, $user['id'], $pdo)));
}

respond(['success' => false, 'error' => 'Unknown action.'], 400);
