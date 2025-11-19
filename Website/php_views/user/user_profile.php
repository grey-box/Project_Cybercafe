<?php
declare(strict_types=1);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';
require_roles(['user']);

$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db.php';

$userId = current_user_id();
// Set the page title dynamically
$pageTitle = "User Profile";

// fetch user
$uStmt = $pdo->prepare("
  SELECT user_id, full_name, email, phone_number, user_role, account_creation_date, last_login_timestamp
  FROM user WHERE user_id = :u LIMIT 1
");
$uStmt->execute([':u'=>$userId]);
$user = $uStmt->fetch();
if (!$user) { http_response_code(404); exit('User not found'); }

$notice = null; $errors = [];

function formatGB($bytes): string {
    $bytes = (float)($bytes ?? 0);
    $gb = $bytes / (1024*1024*1024);
    return ($gb >= 10) ? (string)round($gb) . ' GB' : number_format($gb, 1) . ' GB';
}
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'remove_device' && !empty($_POST['mac'])) {
        $mac = $_POST['mac'];

        $pdo->beginTransaction();

        // delete traffic for this user's device sessions
        $pdo->prepare("
            DELETE FROM traffic_data
            WHERE session_id IN (
              SELECT session_id FROM internet_session
              WHERE user_id = :u AND mac_address = :mac
            )
        ")->execute([':u'=>$userId, ':mac'=>$mac]);

        // delete sessions for this user's device
        $pdo->prepare("
            DELETE FROM internet_session
            WHERE user_id = :u AND mac_address = :mac
        ")->execute([':u'=>$userId, ':mac'=>$mac]);

        // optional: also clear any block flag you might have stored
        $pdo->prepare("DELETE FROM device_restriction WHERE mac_address = :mac")
            ->execute([':mac'=>$mac]);

        $pdo->commit();
        $notice = 'Device removed.';
        // PRG to avoid resubmits
        if (!headers_sent()) { header("Location: ".$_SERVER['REQUEST_URI']); exit; }
    }
}

$devStmt = $pdo->prepare("
  SELECT
    s.mac_address,
    COALESCE(s.host_name,'Unknown device') AS host_name,
    SUM(COALESCE(t.received_bytes,0) + COALESCE(t.transmitted_bytes,0)) AS bytes_used,
    MAX(COALESCE(q.bandwidth_quota,0)) AS quota_bytes,
    MAX(CASE WHEN s.logout_timestamp IS NULL THEN 1 ELSE 0 END) AS has_active,
    MAX(COALESCE(s.logout_timestamp, s.login_timestamp)) AS last_time
  FROM internet_session s
  LEFT JOIN traffic_data t ON t.session_id = s.session_id
  LEFT JOIN speed_queue  q ON q.queue_id   = s.speed_queue_id
  WHERE s.user_id = :u
  GROUP BY s.mac_address, host_name
  ORDER BY has_active DESC, last_time DESC
  LIMIT 10
");
$devStmt->execute([':u'=>$userId]);
$devices = $devStmt->fetchAll();

// Include the header
require_once VIEWS_ROOT . '/asset_for_pages/user_header.php';
?>

<?php foreach ($errors as $e): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<?php if ($notice): ?>
  <div class="alert alert-success"><?= htmlspecialchars($notice) ?></div>
<?php endif; ?>

<div class="page-header">
  <ul class="breadcrumbs mb-3">
    <li class="nav-home">
      <a href="<?= WEB_BASE ?>/php_views/user/user_profile.php"><i class="icon-home"></i></a>
    </li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">User</a></li>
    <li class="separator"><i class="icon-arrow-right"></i></li>
    <li class="nav-item"><a href="#">User Profile</a></li>
  </ul>
</div>

<div class="row">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <div class="card-title">Profile</div>
      </div>
      <div class="card-body">
        <!-- Read-only profile fields only -->
        <div class="form-group row mb-3">
          <label class="col-sm-3 col-form-label">User ID</label>
          <div class="col-sm-9">
            <input type="text" class="form-control"
                   value="<?= htmlspecialchars($user['user_id']) ?>" readonly>
          </div>
        </div>
        <div class="form-group row mb-3">
          <label class="col-sm-3 col-form-label">Full name</label>
          <div class="col-sm-9">
            <input type="text" class="form-control"
                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" readonly>
          </div>
        </div>
        <div class="form-group row mb-3">
          <label class="col-sm-3 col-form-label">Email</label>
          <div class="col-sm-9">
            <input type="email" class="form-control"
                   value="<?= htmlspecialchars($user['email']) ?>" readonly>
          </div>
        </div>
        <div class="form-group row mb-0">
          <label class="col-sm-3 col-form-label">Phone</label>
          <div class="col-sm-9">
            <input type="text" class="form-control"
                   value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" readonly>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Devices -->
<div class="row mt-4">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header"><div class="card-title">Recent devices</div></div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th style="width:50%">Device name</th>
                <th style="width:35%">Bandwidth (Used / Allocated)</th>
                <th class="text-end" style="width:15%">Remove</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$devices): ?>
                <tr>
                  <td colspan="3" class="text-muted">No devices yet.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($devices as $d): ?>
                  <tr class="<?= (int)$d['has_active'] ? 'table-light' : '' ?>">
                    <td>
                      <?= h($d['host_name']) ?>
                      <div class="small text-muted"><?= h($d['mac_address']) ?></div>
                    </td>
                    <td>
                      <?= formatGB($d['bytes_used'] ?? 0) ?>
                      /
                      <?= (int)$d['quota_bytes'] ? formatGB($d['quota_bytes']) : '—' ?>
                    </td>
                    <td class="text-end">
                      <form method="POST" class="d-inline"
                            onsubmit="return confirm('Remove this device and all its session data?');">
                        <input type="hidden" name="action" value="remove_device">
                        <input type="hidden" name="mac" value="<?= h($d['mac_address']) ?>">
                        <button class="btn btn-sm btn-danger">Remove</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Include the footer
require_once VIEWS_ROOT . '/asset_for_pages/footer.php'
?>
