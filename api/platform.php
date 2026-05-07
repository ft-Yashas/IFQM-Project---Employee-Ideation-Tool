<?php
require_once __DIR__ . '/config.php';

$user   = requirePlatformAuth();
$action = $_GET['action'] ?? 'tenants';
$master = masterDb();

// ── Helper: open a read-only connection to a tenant DB ────────────────────
function tenantPdo(array $t): PDO {
    return new PDO(
        'mysql:host=' . $t['db_host'] . ';dbname=' . $t['db_name'] . ';charset=utf8mb4',
        $t['db_user'], $t['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}

// ── Helper: strip sensitive DB credentials before sending to client ────────
function safeTenant(array $t): array {
    unset($t['db_host'], $t['db_name'], $t['db_user'], $t['db_pass']);
    return $t;
}

// ── GET ?action=tenants ───────────────────────────────────────────────────
// Returns all tenants with aggregate stats only — no idea content.
if ($action === 'tenants') {
    $tenants = $master->query("SELECT * FROM tenants ORDER BY created_at ASC")->fetchAll();

    foreach ($tenants as &$t) {
        try {
            $pdo = tenantPdo($t);

            $t['user_count']        = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role != 'super_admin'")->fetchColumn();
            $t['idea_count']        = (int)$pdo->query("SELECT COUNT(*) FROM ideas WHERE status != 'Draft'")->fetchColumn();
            $t['implemented_count'] = (int)$pdo->query("SELECT COUNT(*) FROM ideas WHERE status = 'Implemented'")->fetchColumn();
            $t['last_activity']     = $pdo->query("SELECT MAX(submitted_at) FROM ideas WHERE status != 'Draft'")->fetchColumn() ?: null;

            // Submission trend — last 6 months (counts only, no content)
            $t['trend'] = $pdo->query(
                "SELECT DATE_FORMAT(submitted_at,'%Y-%m') AS month, COUNT(*) AS cnt
                 FROM ideas WHERE submitted_at IS NOT NULL AND status != 'Draft'
                 GROUP BY month ORDER BY month DESC LIMIT 6"
            )->fetchAll();

        } catch (Exception $e) {
            $t['user_count']        = 0;
            $t['idea_count']        = 0;
            $t['implemented_count'] = 0;
            $t['last_activity']     = null;
            $t['trend']             = [];
            $t['db_error']          = true;
        }

        $t = safeTenant($t);
    }
    unset($t);

    respond(['success' => true, 'tenants' => $tenants]);
}

// ── GET ?action=tenant_hierarchy&id=X ────────────────────────────────────
// Returns the user tree for one tenant.
// Privacy contract: names, roles, departments, manager name, idea COUNT.
// No idea titles, descriptions, content, or scores are returned.
if ($action === 'tenant_hierarchy') {
    $tenantId = (int)($_GET['id'] ?? 0);
    if (!$tenantId) respond(['success' => false, 'error' => 'Missing tenant id.'], 400);

    $stmt = $master->prepare("SELECT * FROM tenants WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$tenantId]);
    $t = $stmt->fetch();
    if (!$t) respond(['success' => false, 'error' => 'Tenant not found.'], 404);

    try {
        $pdo   = tenantPdo($t);
        $users = $pdo->query(
            "SELECT u.id, u.employee_id, u.name, u.department, u.business_unit,
                    u.location, u.role, u.manager_id,
                    m.name AS manager_name,
                    (SELECT COUNT(*) FROM ideas
                     WHERE submitter_id = u.id AND status != 'Draft') AS idea_count
             FROM users u
             LEFT JOIN users m ON m.id = u.manager_id
             WHERE u.role != 'super_admin'
             ORDER BY FIELD(u.role,'admin','executive','manager','employee'), u.name"
        )->fetchAll();

        respond([
            'success' => true,
            'tenant'  => ['id' => $t['id'], 'name' => $t['name'], 'slug' => $t['slug'], 'domain' => $t['domain']],
            'users'   => $users,
        ]);
    } catch (Exception $e) {
        respond(['success' => false, 'error' => 'Tenant database is unavailable.'], 503);
    }
}

respond(['success' => false, 'error' => 'Unknown action.'], 400);
