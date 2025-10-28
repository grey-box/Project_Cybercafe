<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GreyBox · Bad Request</title>
  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/bootstrap.min.css">
  <style>
    body {background: #f0f2f4; min-height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; font-family: "Public Sans", "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, "Helvetica Neue", sans-serif;}
    .card {background: #ffffff; width: 360px; border-radius: 20px; box-shadow: 0 24px 45px rgba(28, 39, 51, 0.12); padding: 44px 36px; text-align: center;}
    .stamp {display: inline-flex; align-items: center; justify-content: center; padding: 8px 20px; border-radius: 999px; font-size: 0.78rem; letter-spacing: 0.18em; text-transform: uppercase; background: rgba(33, 164, 96, 0.12); color: #218f5e; font-weight: 700; margin-bottom: 20px;}
    .code {font-size: 3.2rem; font-weight: 800; color: #2c3a45; margin-bottom: 12px;}
    h1 {font-size: 1.9rem; font-weight: 700; color: #2c3a45; margin-bottom: 8px;}
    p {color: #5f6b74; font-size: 0.98rem; line-height: 1.6; margin-bottom: 30px;}
    .btn-stack {display: grid; gap: 12px;}
    .btn-stack button {padding: 14px 16px; border-radius: 12px; border: 0; font-weight: 600; font-size: 0.98rem;}
    .btn-primary {background: #26a269; color: #ffffff; box-shadow: 0 12px 24px rgba(38,162,105,0.28);} 
    .btn-outline {background: #e8eded; color: #2c3a45; border: 1px solid rgba(44,58,69,0.16);}
    .btn-stack button:disabled {opacity: 0.75; cursor: default;} 
    footer {margin-top: 22px; font-size: 0.78rem; color: #8a959d;}
  </style>
</head>
<body>
  <main class="card">
    <div class="stamp">Action Required</div>
    <div class="code">Error 400</div>
    <h1>Bad request detected</h1>
    <p>The request sent to the server couldn’t be processed. Please verify the address or reload the page before trying again.</p>
    <div class="btn-stack">
      <button type="button" class="btn-primary" disabled>Refresh</button>
      <button type="button" class="btn-outline" disabled>Return Home</button>
      <button type="button" class="btn-outline" disabled>Contact Support</button>
    </div>
    <footer>Reference #<?= substr(md5($_SERVER['REMOTE_ADDR'] ?? 'client'), 0, 6) ?> · <?= date('Y-m-d H:i') ?></footer>
  </main>
</body>
</html>
