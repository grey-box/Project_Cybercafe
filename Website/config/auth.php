<?php
declare(strict_types=1);

require_once __DIR__ . '/paths.php';

function get_db(): PDO {
    /** @var PDO $pdo */
    $pdo = require __DIR__ . '/db.php';
    return $pdo;
}

function authenticate(string $identifier, string $password): ?array {
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'SELECT user_id, full_name, email, access_code, user_role
         FROM User
         WHERE email = :id OR user_id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => $identifier]);
    $user = $stmt->fetch();
    if (!$user) {
        return null;
    }
    if (!hash_equals((string) $user['access_code'], $password)) {
        return null;
    }
    return $user;
}

function require_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function destination_for_role(string $role): string {
    $base = WEB_BASE;
    switch ($role) {
        case 'admin':
            return $base . '/php_views/final_admin/adashboard.php';
        case 'owner':
            return $base . '/php_views/owner/odashboard.php';
        case 'guest':
            return $base . '/php_views/guest_user/guest_homepage.php';
        default:
            return $base . '/php_views/user/user_profile.php';
    }
}

function login_user(array $user): void {
    require_session();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['user_role'];
}

function logout_user(): void {
    require_session();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function redirect_for_role(string $role): void {
    header('Location: ' . destination_for_role($role));
    exit;
}
