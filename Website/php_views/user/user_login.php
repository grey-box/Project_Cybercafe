<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';

require_session();
if (!empty($_SESSION['role'])) {
    redirect_for_role($_SESSION['role']);
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = 'Enter both email/user ID and password.';
    } else {
        $user = authenticate($identifier, $password);
        if ($user && in_array($user['user_role'], ['admin', 'owner', 'user'], true)) {
            login_user($user);
            redirect_for_role($user['user_role']);
        } else {
            $error = 'Credentials not recognised. Sample accounts: user@example.com/userpass, admin@example.com/adminpass, owner@example.com/ownerpass.';
        }
    }
}

$pageTitle = "Sign In";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="icon" href="<?= WEB_BASE ?>/assets/greybox-logo.png" type="image/x-icon" />

  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/bootstrap.min.css">
  <style>
    body { background: #f4f6f8; }
    .login-card { border: 0; box-shadow: 0 12px 28px rgba(0,0,0,.08); border-radius: 1rem; }
    .login-img {
      width: 100%;
      height: auto;
      display: block;
      object-fit: contain;
      background-color: #fff;
    }
    .btn-success { background-color: #13a463; border-color: #13a463; }
    .btn-outline-success { color: #13a463; border-color: #13a463; }
    .btn-outline-success:hover { background-color: #13a463; color: #fff; }
    .form-control { border-radius: .5rem; }
    .input-group-text { background: #fff; cursor: pointer; }
  </style>
</head>
<body>

  <!-- Center the card on the page -->
  <div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="card login-card" style="width: 100%; max-width: 430px;">
      <!-- Hero image INSIDE the card -->
      <img src="<?= WEB_BASE ?>/assets/greybox-logo.png" alt="Sign In" class="login-img">

      <div class="card-body py-4 px-4">
        <h3 class="fw-bold mb-4">Sign In</h3>

        <form method="POST" action="" novalidate>
          <input type="hidden" name="csrf_token" value="">
          <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <!-- Username -->
          <div class="mb-3">
            <label for="username" class="form-label">Email or User ID</label>
            <input
              type="text"
              class="form-control"
              id="username"
              name="username"
              placeholder="user@example.com"
              required
              autocomplete="username">
          </div>

          <!-- Password WITH icon inside the field -->
          <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                placeholder="Password"
                required
                autocomplete="current-password">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                Show
                </button>
            </div>
          </div>

          <!-- Continue -->
          <div class="d-grid mb-3">
            <button type="submit" class="btn btn-success">Continue</button>
          </div>

          <!-- Guest -->
          <div class="d-grid">
            <a class="btn btn-outline-success" href="<?= WEB_BASE ?>/php_views/guest_user/guest_login.php">
              Login as Guest
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

<script>
  document.getElementById('togglePassword')?.addEventListener('click', function () {
    const pw = document.getElementById('password');
    if (!pw) return;
    const showing = pw.type === 'text';
    pw.type = showing ? 'password' : 'text';
    this.textContent = showing ? 'Show' : 'Hide';
  });
</script>
</body>
</html>
