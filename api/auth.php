<?php
require_once __DIR__ . '/config.php';

session_set_cookie_params(SESSION_LIFETIME);
session_start();

$action = $_GET['action'] ?? 'me';

if ($action === 'me') {
    if (empty($_SESSION['user_id'])) {
        respond(['success' => false, 'authenticated' => false]);
    }
    respond(['success' => true, 'authenticated' => true, 'user' => $_SESSION['user']]);
}

if ($action === 'login') {
    $body  = json_decode(file_get_contents('php://input'), true) ?? [];
    $email = trim($body['email'] ?? '');
    $pass  = trim($body['password'] ?? '');

    if (!$email || !$pass) {
        respond(['success' => false, 'error' => 'Email and password are required.'], 400);
    }

    $stmt = db()->prepare(
        "SELECT u.*, m.name AS manager_name
         FROM users u
         LEFT JOIN users m ON m.id = u.manager_id
         WHERE u.email = ? LIMIT 1"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password_hash'])) {
        respond(['success' => false, 'error' => 'Invalid email or password.'], 401);
    }

    $session = [
        'id'             => $user['id'],
        'employee_id'    => $user['employee_id'],
        'name'           => $user['name'],
        'email'          => $user['email'],
        'phone'          => $user['phone'],
        'department'     => $user['department'],
        'business_unit'  => $user['business_unit'],
        'location'       => $user['location'],
        'role'           => $user['role'],
        'manager_id'     => $user['manager_id'],
        'manager_name'   => $user['manager_name'],
        'points'         => $user['points'],
        'avatar_initials' => $user['avatar_initials'] ?? strtoupper(substr($user['name'], 0, 1)),
    ];

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user']    = $session;

    respond(['success' => true, 'user' => $session]);
}

if ($action === 'logout') {
    session_destroy();
    respond(['success' => true]);
}

respond(['success' => false, 'error' => 'Unknown action'], 400);
