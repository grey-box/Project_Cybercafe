<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

$pageTitle = "Guest Login";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="icon" href="<?= WEB_BASE ?>/assets/greybox-logo.png" type="image/x-icon" />

  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/plugins.min.css" />
  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/kaiadmin.min.css" />
  <style>
    body { background: #f4f6f8; }
    .login-card {
      border: 0;
      box-shadow: 0 12px 28px rgba(0,0,0,.08);
      border-radius: 1rem;
      overflow: hidden;
    }
    .login-img {
      width: 100%;
      height: auto;
      display: block;
      object-fit: contain;
      background-color: #fff;
    }
    .btn-success {
      background-color: #13a463;
      border-color: #13a463;
    }
    .btn-success:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    .terms-box {
      height: 180px;
      overflow-y: auto;
      background: #fff;
      border: 1px solid #dee2e6;
      border-radius: .5rem;
      padding: 1rem;
    }
  </style>
</head>
<body>

  <div class="min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="card login-card" style="width: 100%; max-width: 430px;">
      <img src="<?= WEB_BASE ?>/assets/greybox-logo.png" alt="Guest Access" class="login-img">

      <div class="card-body py-4 px-4">
        <h3 class="fw-bold mb-4">Sign In</h3>

        <h4 class="fw-bold mb-2">Terms and Conditions</h4>
        <div id="termsBox" class="terms-box mb-3">
          <ol class="mb-0">
            <li>You agree to use this service responsibly and abide by all applicable laws.</li>
            <li>Guest access is provided “as is” without guarantees of uptime or speed.</li>
            <li>Activity may be logged for security and abuse prevention.</li>
            <li>Do not attempt to access accounts or data that are not yours.</li>
            <li>We may suspend guest access at any time without notice.</li>
            <li>You acknowledge that public networks are not fully secure.</li>
            <li>By continuing you accept the privacy and usage policies.</li>
          </ol>
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="agreeChk">
          <label class="form-check-label" for="agreeChk">
            I agree to the Terms and Conditions
          </label>
        </div>

        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="">
          <div class="d-grid">
            <button id="guestBtn" type="submit" class="btn btn-success" disabled>
              Login as Guest
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    const agreeChk = document.getElementById('agreeChk');
    const guestBtn = document.getElementById('guestBtn');

    agreeChk.addEventListener('change', function() {
      guestBtn.disabled = !this.checked;
    });
  </script>
</body>
</html>
