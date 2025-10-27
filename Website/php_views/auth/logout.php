<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';

logout_user();

$pageTitle = 'Signed Out';
$loginLinks = [
    'Admin / Owner / User Login' => WEB_BASE . '/php_views/user/user_login.php',
    'Guest Login' => WEB_BASE . '/php_views/guest_user/guest_login.php',
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/bootstrap.min.css">
  <style>
    body { background: #f4f6f8; }
    .card { border: 0; box-shadow: 0 12px 28px rgba(0,0,0,.08); border-radius: 1rem; }
  </style>
</head>
<body>
  <div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="card" style="max-width: 420px; width: 100%;">
      <div class="card-body p-4 text-center">
        <h2 class="fw-bold mb-3">You have been logged out</h2>
        <p class="text-muted">Choose where you’d like to sign in again:</p>
        <div class="d-grid gap-2 mt-4">
          <?php foreach ($loginLinks as $label => $href): ?>
            <a class="btn btn-success" href="<?= htmlspecialchars($href, ENT_QUOTES) ?>"><?= htmlspecialchars($label) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
