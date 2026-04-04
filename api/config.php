<?php
// ── config.php ────────────────────────────────────────────────
// Central configuration.
// Place the entire ifqm/ folder inside your web root (e.g. C:\xampp\htdocs\).
// Access via http://localhost/ifqm/

define('OPENAI_API_KEY', getenv('OPENAI_API_KEY'));
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // default XAMPP user
define('DB_PASS', '');              // default XAMPP password (empty)
define('DB_NAME', 'ifqm_ideation');

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_MB', 10);

// Session lifetime: 8 hours
define('SESSION_LIFETIME', 28800);

// Points config
define('POINTS_SUBMIT',      10);
define('POINTS_APPROVED',    25);
define('POINTS_IMPLEMENTED', 65);  // total = 100 when implemented

// ── Derive the public base URL dynamically (works on any host) ───
function getAppBaseUrl(): string {
    $proto  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Walk up from /ifqm/api/ to /ifqm/
    $script = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/ifqm/api/config.php'));
    $base   = rtrim($script, '/\\');
    return $proto . '://' . $host . $base . '/';
}
define('UPLOAD_URL', getAppBaseUrl() . 'api/uploads/');

// ── Database connection ───────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            // Do not expose DB internals to clients in production.
            error_log('DB connection failed: ' . $e->getMessage());
            die(json_encode(['success' => false, 'error' => 'Database connection failed.']));
        }
    }
    return $pdo;
}

// ── Helpers ───────────────────────────────────────────────────────
function respond(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function requireAuth(): array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user_id'])) {
        respond(['success' => false, 'error' => 'Not authenticated'], 401);
    }
    return $_SESSION['user'];
}

function requireRole(string ...$roles): array {
    $user = requireAuth();
    if (!in_array($user['role'], $roles, true)) {
        respond(['success' => false, 'error' => 'Insufficient permissions'], 403);
    }
    return $user;
}

function generateIdeaCode(): string {
    $year = (int)date('Y');
    $pdo  = db();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ideas WHERE YEAR(created_at) = ?");
    $stmt->execute([$year]);
    $n = (int)$stmt->fetchColumn() + 1;
    return 'IDA-' . $year . '-' . str_pad($n, 3, '0', STR_PAD_LEFT);
}

function addNotification(int $userId, string $title, string $msg, ?int $ideaId = null): void {
    $pdo = db();
    try {
        $pdo->prepare("INSERT INTO notifications (user_id,title,message,idea_id) VALUES (?,?,?,?)")
            ->execute([$userId, $title, $msg, $ideaId]);
    } catch (\Exception $e) {
        // Fallback if idea_id column doesn't exist yet
        $pdo->prepare("INSERT INTO notifications (user_id,title,message) VALUES (?,?,?)")
            ->execute([$userId, $title, $msg]);
    }
}

function addWorkflow(int $ideaId, int $actorId, string $action, ?string $comment = null): void {
    $pdo = db();
    $pdo->prepare("INSERT INTO idea_workflow (idea_id,actor_id,action,comment) VALUES (?,?,?,?)")
        ->execute([$ideaId, $actorId, $action, $comment]);
}

function addPoints(int $userId, int $pts): void {
    db()->prepare("UPDATE users SET points = points + ? WHERE id = ?")->execute([$pts, $userId]);
}

function callOpenAI(string $prompt): ?string
{
    $apiKey = OPENAI_API_KEY;
    if (!$apiKey) return null;

    $payload = [
        'model'       => 'gpt-4o-mini',
        'messages'    => [
            [
                'role'    => 'system',
                'content' => 'You are an expert evaluator. Always respond with valid JSON only. No markdown, no code fences, no extra text.',
            ],
            ['role' => 'user', 'content' => $prompt],
        ],
        'temperature' => 0.3,   // low = more deterministic JSON
        'max_tokens'  => 250,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_TIMEOUT        => 20,   // total request timeout (seconds)
        CURLOPT_CONNECTTIMEOUT => 10,   // connection timeout
    ]);

    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        error_log('OpenAI cURL error: ' . $curlErr);
        return null;
    }
    if ($httpCode !== 200) {
        error_log('OpenAI HTTP ' . $httpCode . ': ' . $raw);
        return null;
    }

    $decoded = json_decode($raw, true);
    $content = $decoded['choices'][0]['message']['content'] ?? null;

    if ($content !== null) {
        error_log('OpenAI raw content: ' . $content);
    }

    return $content;
}

// End of config.php
