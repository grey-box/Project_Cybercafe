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

    if ($user && password_verify($password, $user['access_code'])) {
        return $user; 
    }
    else {
        $msg = 'Wrong username or password.';
        return null;
    }
   
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

function current_user_id(): ?string {
    require_session();
    return $_SESSION['user_id'] ?? null;
}

function current_role(): string {
    require_session();
    $role = $_SESSION['role'] ?? 'anonymous';
    return in_array($role, ['owner','admin','user','guest','anonymous'], true) ? $role : 'anonymous';
}

function is_logged_in(): bool {
    return current_user_id() !== null;
}

function role_rank(string $role): int {
    static $map = ['anonymous' => -1, 'guest' => 0, 'user' => 1, 'admin' => 2, 'owner' => 3];
    return $map[$role] ?? -1;
}

function has_min_role(string $minRole): bool {
    return role_rank(current_role()) >= role_rank($minRole);
}

function require_roles(array $allowedRoles): void {
    require_session();

    // If not logged in, send to the correct login screen
    if (!is_logged_in()) {
        $login = in_array('guest', $allowedRoles, true)
            ? WEB_BASE . '/php_views/guest_user/guest_login.php'
            : WEB_BASE . '/php_views/user/user_login.php';
        header('Location: ' . $login);
        exit;
    }

    $role = current_role();
    if (in_array($role, $allowedRoles, true)) {
        return;
    }

    http_response_code(403);
    require SITE_FS_ROOT . '/php_views/errors/403.php';
    exit;
}

function require_min_role(string $minRole): void {
    require_session();

    if (has_min_role($minRole)) {
        return;
    }

    if (!is_logged_in()) {
        header('Location: ' . WEB_BASE . '/php_views/user/user_login.php');
        exit;
    }

    http_response_code(403);
    require SITE_FS_ROOT . '/php_views/errors/403.php';
    exit;
}

function get_curr_user() {
    $pdo = get_db();
    $userId = current_user_id();
    $stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = :u LIMIT 1");
    $stmt->execute([':u' => $userId]);
    return $stmt->fetch();
}