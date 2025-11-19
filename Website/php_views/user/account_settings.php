<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';
$pageTitle = 'Account Settings';

$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db.php';
$userId = current_user_id();

$flash = null;

// Load current profile
$profileStmt = $pdo->prepare("
    SELECT user_id, full_name, email, phone_number
      FROM user
     WHERE user_id = :uid
     LIMIT 1
");
$profileStmt->execute([':uid' => $userId]);
$profile = $profileStmt->fetch();

if (!$profile) {
    http_response_code(500);
    echo "User record not found for current session.";
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_profile') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $phone    = trim($_POST['phone_number'] ?? '');

            if ($fullName === '' || $email === '') {
                throw new RuntimeException('Name and email are required.');
            }

            $stmt = $pdo->prepare("
                UPDATE user
                   SET full_name = :name,
                       email = :email,
                       phone_number = :phone
                 WHERE user_id = :uid
            ");
            $stmt->execute([
                ':name'  => $fullName,
                ':email' => $email,
                ':phone' => $phone,
                ':uid'   => $userId,
            ]);

            // refresh profile in memory
            $profile['full_name']    = $fullName;
            $profile['email']        = $email;
            $profile['phone_number'] = $phone;

            $flash = ['ok' => true, 'msg' => 'Profile updated.'];

        } elseif ($action === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($new !== $confirm) {
                throw new RuntimeException('New passwords do not match.');
            }
            if (strlen($new) < 8) {
                throw new RuntimeException('New password must be at least 8 characters.');
            }

            $stmt = $pdo->prepare("SELECT access_code FROM user WHERE user_id = :uid");
            $stmt->execute([':uid' => $userId]);
            $row = $stmt->fetch();

            if (!$row || !password_verify($current, $row['access_code'])) {
                throw new RuntimeException('Current password is incorrect.');
            }

            $hash = password_hash($new, PASSWORD_DEFAULT);
            $upd  = $pdo->prepare("UPDATE user SET access_code = :hash WHERE user_id = :uid");
            $upd->execute([':hash' => $hash, ':uid' => $userId]);

            $flash = ['ok' => true, 'msg' => 'Password updated.'];

        } elseif ($action === 'revoke_session') {
            $sessionId = $_POST['session_id'] ?? '';

            if ($sessionId === '') {
                throw new RuntimeException('Missing session id.');
            }

            $stmt = $pdo->prepare("
                UPDATE internet_session
                   SET logout_timestamp = DATETIME('now')
                 WHERE session_id = :sid
                   AND user_id = :uid
                   AND logout_timestamp IS NULL
            ");
            $stmt->execute([':sid' => $sessionId, ':uid' => $userId]);

            if ($stmt->rowCount() > 0) {
                $flash = ['ok' => true, 'msg' => 'Session signed out.'];
            } else {
                $flash = ['ok' => false, 'msg' => 'Session not found or already closed.'];
            }

        } elseif ($action === 'revoke_all') {

            $stmt = $pdo->prepare("
                UPDATE internet_session
                   SET logout_timestamp = DATETIME('now')
                 WHERE user_id = :uid
                   AND logout_timestamp IS NULL
            ");
            $stmt->execute([':uid' => $userId]);

            $flash = ['ok' => true, 'msg' => 'All active sessions signed out.'];

        } elseif ($action === 'request_deactivation') {

            $stmt = $pdo->prepare("
                INSERT INTO system_event(event_type, description, details)
                VALUES(
                    'DEACTIVATION_REQUEST',
                    'User requested account deactivation',
                    :details
                )
            ");
            $details = sprintf('user_id=%s; requested_at=%s', $userId, date('c'));
            $stmt->execute([':details' => $details]);

            $flash = ['ok' => true, 'msg' => 'Deactivation request submitted to admin.'];

        }
    } catch (Throwable $e) {
        $flash = ['ok' => false, 'msg' => $e->getMessage()];
    }
}

// Load active sessions dynamically
$sessionStmt = $pdo->prepare("
    SELECT s.session_id,
           s.host_name,
           s.ip_address,
           s.login_timestamp,
           s.speed_queue_id,
           q.queue_name
      FROM internet_session s
 LEFT JOIN speed_queue q ON q.queue_id = s.speed_queue_id
     WHERE s.user_id = :uid
       AND s.logout_timestamp IS NULL
  ORDER BY s.login_timestamp DESC
");
$sessionStmt->execute([':uid' => $userId]);
$sessions = $sessionStmt->fetchAll();

require_once VIEWS_ROOT . '/asset_for_pages/user_header.php';
?>

<div class="container">
  <div class="page-inner">

    <?php if ($flash): ?>
      <div class="alert <?= $flash['ok'] ? 'alert-success' : 'alert-danger' ?> rounded-4 mt-2">
        <?= htmlspecialchars($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-3 mt-2">
      <div>
        <h3 class="fw-bold mb-1"><?= htmlspecialchars($pageTitle) ?></h3>
        <div class="text-muted">Manage profile, password, preferences, and sessions.</div>
      </div>
    </div>

    <!-- Profile -->
    <div class="card mb-4" style="border:0; box-shadow:0 8px 20px rgba(0,0,0,.06); border-radius:1rem;">
      <div class="card-body">
        <h5 class="mb-3">Profile</h5>
        <form id="profileForm" method="post">
          <input type="hidden" name="action" value="update_profile">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full name</label>
              <input type="text" class="form-control" name="full_name"
                     value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email"
                     value="<?= htmlspecialchars($profile['email'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="tel" class="form-control" name="phone_number"
                     value="<?= htmlspecialchars($profile['phone_number'] ?? '') ?>"
                     placeholder="+1 480 555 1234">
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><i class="fa fa-save me-1"></i> Save</button>
              <button class="btn btn-outline-secondary ms-1" type="reset">Reset</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Security -->
    <div class="card mb-4" style="border:0; box-shadow:0 8px 20px rgba(0,0,0,.06); border-radius:1rem;">
      <div class="card-body">
        <h5 class="mb-3">Security</h5>
        <form id="passwordForm" method="post">
          <input type="hidden" name="action" value="change_password">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Current password</label>
              <input type="password" class="form-control" name="current_password" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">New password</label>
              <input id="newPass" type="password" class="form-control" name="new_password" required>
              <div class="form-text">At least 8 characters.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Confirm new password</label>
              <input id="confirmPass" type="password" class="form-control" name="confirm_password" required>
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><i class="fa fa-key me-1"></i> Change password</button>
            </div>
          </div>
        </form>

        <hr class="my-4">
      </div>
    </div>

    <!-- Preferences (still mostly placeholder, easy to wire to a table later) -->
    <div class="card mb-4" style="border:0; box-shadow:0 8px 20px rgba(0,0,0,.06); border-radius:1rem;">
      <div class="card-body">
        <h5 class="mb-3">Preferences</h5>
        <form id="prefForm" onsubmit="event.preventDefault(); alert('Preferences would be saved to a preferences table.');">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Language</label>
              <select class="form-select" name="lang">
                <option value="en" selected>English</option>
                <option value="es">Spanish</option>
              </select>
            </div>
            <div class="col-md-8 d-flex align-items-center">
              <div class="form-check me-4">
                <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                <label class="form-check-label" for="emailNotif">Email notifications</label>
              </div>
              <div class="form-check me-4">
                <input class="form-check-input" type="checkbox" id="billingNotif" checked>
                <label class="form-check-label" for="billingNotif">Billing alerts</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="promoNotif">
                <label class="form-check-label" for="promoNotif">Promotions</label>
              </div>
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><i class="fa fa-save me-1"></i> Save</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Active sessions -->
    <div class="card mb-4" style="border:0; box-shadow:0 8px 20px rgba(0,0,0,.06); border-radius:1rem;">
      <div class="card-body">
        <h5 class="mb-3">Active sessions</h5>
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>Device</th>
                <th>IP</th>
                <th>Login time</th>
                <th>Queue</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="sessionTable">
              <?php if ($sessions): ?>
                <?php foreach ($sessions as $s): ?>
                  <tr>
                    <td><?= htmlspecialchars($s['host_name'] ?: 'Unknown device') ?></td>
                    <td><?= htmlspecialchars($s['ip_address'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($s['login_timestamp'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($s['queue_name'] ?: $s['speed_queue_id'] ?: '-') ?></td>
                    <td class="text-end">
                      <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="revoke_session">
                        <input type="hidden" name="session_id" value="<?= htmlspecialchars($s['session_id']) ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Sign out</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center text-muted">No active sessions.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php if ($sessions): ?>
          <div class="mt-3">
            <form method="post">
              <input type="hidden" name="action" value="revoke_all">
              <button class="btn btn-outline-danger btn-sm" type="submit">Sign out from all devices</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Danger zone -->
    <div class="card mb-5" style="border:0; box-shadow:0 8px 20px rgba(0,0,0,.06); border-radius:1rem;">
      <div class="card-body">
        <h5 class="mb-2 text-danger">Danger zone</h5>
        <div class="text-muted mb-3 small">
          Request account deactivation. An admin will review the request.
        </div>
        <form method="post" onsubmit="return confirm('Send deactivation request to admin?');">
          <input type="hidden" name="action" value="request_deactivation">
          <button class="btn btn-danger" type="submit">Request deactivation</button>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
  // Client side password check, server still validates
  document.getElementById('passwordForm')?.addEventListener('submit', function (e) {
    const a = document.getElementById('newPass').value;
    const b = document.getElementById('confirmPass').value;
    if (a !== b) {
      e.preventDefault();
      alert('Passwords do not match');
    }
  });
</script>

<?php require_once VIEWS_ROOT . '/asset_for_pages/footer.php'; ?>
