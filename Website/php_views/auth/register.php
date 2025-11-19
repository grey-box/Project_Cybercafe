<?php
declare(strict_types=1);

// Connect to database
$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db.php';

$message = '';

// Ensure foreign keys are enforced
$pdo->exec('PRAGMA foreign_keys = ON');

// Make sure the default role exists in user_role
$pdo->exec("
    INSERT OR IGNORE INTO user_role (role_id, role_name, role_description, permission_set)
    VALUES ('user', 'User', 'Standard user role', 'read_only')
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $access_code = trim($_POST['access_code'] ?? '');
    $user_role = 'user'; 
    $account_creation_date = date('Y-m-d H:i:s');

    if ($full_name === '' || $email === '' || $access_code === '') {
        $message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
    } else {
        $hashed_password = password_hash(
            $access_code,
            defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT
        );

        try {
            $stmt = $pdo->prepare('
                INSERT INTO user (user_id, full_name, email, phone_number, access_code, user_role, account_creation_date)
                VALUES (:user_id, :full_name, :email, :phone_number, :access_code, :user_role, :account_creation_date)
            ');

            $stmt->execute([
                ':user_id' => $user_name,
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone_number' => $phone_number ?: null,
                ':access_code' => $hashed_password,
                ':user_role' => $user_role,
                ':account_creation_date' => $account_creation_date
            ]);

            $message = 'Registration successful!';
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                $message = 'Email already exists.';
            } else {
                $message = 'Database error: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body { font-family: Arial; background:#f7f7f7; display:flex; justify-content:center; align-items:center; height:100vh; }
        form { background:#fff; padding:2em; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:350px; }
        input { width:100%; padding:0.7em; margin:0.5em 0; border:1px solid #ccc; border-radius:5px; }
        button { width:100%; padding:0.8em; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer; }
        button:hover { background:#0056b3; }
        .msg { text-align:center; margin-top:1em; font-size:0.9em; }
    </style>
</head>
<body>
    <form method="POST" action="">
        <h2>Create Account</h2>
        <input type="text" name="user_name" placeholder="User Name" required>
        <input type="text" name="full_name" placeholder="Full Name">
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone_number" placeholder="Phone Number (optional)">
        <input type="password" name="access_code" placeholder="Password" required>
        <button type="submit">Register</button>
        <?php if ($message): ?>
            <div class="msg"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </form>
</body>
</html>
