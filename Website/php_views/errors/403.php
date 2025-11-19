<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';
http_response_code(403);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GreyBox · Forbidden</title>
  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/bootstrap.min.css">
  <style>
    body {background: #f0f2f4; min-height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; font-family: "Public Sans", "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, "Helvetica Neue", sans-serif;}
    .card {background: #ffffff; width: 360px; border-radius: 20px; box-shadow: 0 24px 45px rgba(28, 39, 51, 0.12); padding: 44px 36px; text-align: center;}
    .stamp {display: inline-flex; align-items: center; justify-content: center; padding: 8px 20px; border-radius: 999px; font-size: 0.78rem; letter-spacing: 0.18em; text-transform: uppercase; background: rgba(220, 53, 69, 0.12); color: #b02a37; font-weight: 700; margin-bottom: 20px;}
    .code {font-size: 3.2rem; font-weight: 800; color: #2c3a45; margin-bottom: 12px;}
    h1 {font-size: 1.9rem; font-weight: 700; color: #2c3a45; margin-bottom: 8px;}
    p {color: #5f6b74; font-size: 0.98rem; line-height: 1.6; margin-bottom: 30px;}
    .btn-stack {display: grid; gap: 12px;}
    .btn-stack a {padding: 14px 16px; border-radius: 12px; border: 0; font-weight: 600; font-size: 0.98rem; text-decoration: none; text-align: center;}
    .btn-primary {background: #26a269; color: #ffffff; box-shadow: 0 12px 24px rgba(38,162,105,0.28);} 
    .btn-outline {background: #e8eded; color: #2c3a45; border: 1px solid rgba(44,58,69,0.16);}
  </style>
</head>
<body>
  <main class="card">
    <div class="stamp">Access Denied</div>
    <div class="code">403</div>
    <h1>Forbidden</h1>
    <p>You do not have permission to view this page with your current role.</p>
    <div class="btn-stack">
      <a class="btn-primary" href="<?= WEB_BASE ?>/php_views/user/user_login.php">Sign in</a>
      <a class="btn-outline" href="<?= WEB_BASE ?>/php_views/guest_user/guest_homepage.php">Guest home</a>
    </div>
  </main>
</body>
</html>
