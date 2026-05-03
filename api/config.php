<?php
// ── Master DB connection (tenant registry) ─────────────────────────
define('MASTER_DB_HOST', 'localhost');
define('MASTER_DB_USER', 'root');
define('MASTER_DB_PASS', '');
define('MASTER_DB_NAME', 'ifqm_master');

// ── Fallback for fresh installs without master DB ──────────────────
define('FALLBACK_DB_HOST', 'localhost');
define('FALLBACK_DB_USER', 'root');
define('FALLBACK_DB_PASS', '');
define('FALLBACK_DB_NAME', 'ifqm_ideation');

define('OPENAI_API_KEY', getenv('OPENAI_API_KEY'));

define('MAX_FILE_MB',       10);
define('SESSION_LIFETIME',  28800);
define('POINTS_SUBMIT',     10);
define('POINTS_APPROVED',   25);
define('POINTS_IMPLEMENTED',65);

// ── App base URL ───────────────────────────────────────────────────
function getAppBaseUrl(): string {
    $proto  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/ifqm/api/config.php'));
    return $proto . '://' . $host . rtrim($script, '/\\') . '/';
}

// ── Master DB connection ───────────────────────────────────────────
function masterDb(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO(
        'mysql:host=' . MASTER_DB_HOST . ';dbname=' . MASTER_DB_NAME . ';charset=utf8mb4',
        MASTER_DB_USER, MASTER_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    return $pdo;
}

// ── Tenant resolution ─────────────────────────────────────────────
function resolveTenant(): array {
    static $tenant = null;
    if ($tenant !== null) return $tenant;

    $host = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost'));

    try {
        $master = masterDb();
        $stmt   = $master->prepare("SELECT * FROM tenants WHERE domain=? AND status='active' LIMIT 1");
        $stmt->execute([$host]);
        $row = $stmt->fetch();

        if (!$row) {
            // Try default tenant
            $stmt = $master->prepare("SELECT * FROM tenants WHERE is_default=1 AND status='active' LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch();
        }

        if ($row) {
            $tenant = $row;
            return $tenant;
        }
    } catch (Exception $e) {
        // Master DB not available — use built-in defaults (fresh install)
        error_log('ifqm_master unavailable, using fallback tenant: ' . $e->getMessage());
    }

    // Built-in fallback — keeps existing single-tenant installs working
    $tenant = [
        'id'            => 0,
        'name'          => 'IFQM',
        'slug'          => 'ifqm',
        'domain'        => $host,
        'db_host'       => FALLBACK_DB_HOST,
        'db_name'       => FALLBACK_DB_NAME,
        'db_user'       => FALLBACK_DB_USER,
        'db_pass'       => FALLBACK_DB_PASS,
        'status'        => 'active',
        'is_default'    => 1,
        'primary_color' => '#4f46e5',
    ];
    return $tenant;
}

// ── Per-tenant DB connection ───────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $t = resolveTenant();
    try {
        $pdo = new PDO(
            'mysql:host=' . $t['db_host'] . ';dbname=' . $t['db_name'] . ';charset=utf8mb4',
            $t['db_user'], $t['db_pass'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        error_log('DB connection failed for tenant ' . $t['slug'] . ': ' . $e->getMessage());
        die(json_encode(['success' => false, 'error' => 'Database connection failed.']));
    }
    return $pdo;
}

// ── Per-tenant upload paths ────────────────────────────────────────
function uploadDir(): string {
    static $dir = null;
    if ($dir) return $dir;
    $slug = resolveTenant()['slug'] ?? 'ifqm';
    $dir  = __DIR__ . '/uploads/' . $slug . '/';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return $dir;
}

function uploadUrl(): string {
    $slug = resolveTenant()['slug'] ?? 'ifqm';
    return getAppBaseUrl() . 'api/uploads/' . $slug . '/';
}

// Keep constant for backward compat (base dir, non-tenant-specific)
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// ── Core helpers ──────────────────────────────────────────────────
function respond(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function requireAuth(): array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) respond(['success' => false, 'error' => 'Not authenticated'], 401);
    return $_SESSION['user'];
}

function requireRole(string ...$roles): array {
    $user = requireAuth();
    if (!in_array($user['role'], $roles, true)) respond(['success' => false, 'error' => 'Insufficient permissions'], 403);
    return $user;
}

function generateIdeaCode(): string {
    $year = (int)date('Y');
    $stmt = db()->prepare("SELECT COUNT(*) FROM ideas WHERE YEAR(created_at) = ?");
    $stmt->execute([$year]);
    $n = (int)$stmt->fetchColumn() + 1;
    return 'IDA-' . $year . '-' . str_pad($n, 3, '0', STR_PAD_LEFT);
}

function addNotification(int $userId, string $title, string $msg, ?int $ideaId = null): void {
    db()->prepare("INSERT INTO notifications (user_id,title,message,idea_id) VALUES (?,?,?,?)")
        ->execute([$userId, $title, $msg, $ideaId]);
}

function addWorkflow(int $ideaId, int $actorId, string $action, ?string $comment = null): void {
    db()->prepare("INSERT INTO idea_workflow (idea_id,actor_id,action,comment) VALUES (?,?,?,?)")
        ->execute([$ideaId, $actorId, $action, $comment]);
}

function addPoints(int $userId, int $pts): void {
    db()->prepare("UPDATE users SET points = points + ? WHERE id = ?")->execute([$pts, $userId]);
}

function callOpenAI(string $prompt): ?string {
    $apiKey = OPENAI_API_KEY;
    if (!$apiKey) return null;
    $payload = [
        'model'       => 'gpt-4o-mini',
        'messages'    => [
            ['role'=>'system','content'=>'You are an expert evaluator. Always respond with valid JSON only. No markdown, no code fences, no extra text.'],
            ['role'=>'user','content'=>$prompt],
        ],
        'temperature' => 0.3,
        'max_tokens'  => 250,
    ];
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json','Authorization: Bearer '.$apiKey],
        CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    $raw = curl_exec($ch); $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); $err = curl_error($ch); curl_close($ch);
    if ($err) { error_log('OpenAI cURL error: '.$err); return null; }
    if ($httpCode !== 200) { error_log('OpenAI HTTP '.$httpCode.': '.$raw); return null; }
    $decoded = json_decode($raw, true);
    $content = $decoded['choices'][0]['message']['content'] ?? null;
    if ($content !== null) error_log('OpenAI raw content: '.$content);
    return $content;
}
