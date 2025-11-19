<?php
declare(strict_types=1);
$pageTitle = "User Profile";

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

/** @var PDO $pdo */
$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/data_helpers.php';

$requestedUserId = $_GET['user'] ?? 'user.mia';
$profile = fetchUserProfile($pdo, $requestedUserId);
$balances = $profile ? fetchUserBalances($pdo, $profile['user_id']) : [];
$sessions = $profile ? fetchUserSessions($pdo, $profile['user_id']) : [];

if ($profile === null) {
    $pageTitle = "User Not Found";
}

$primaryBalance = $balances[0] ?? null;
$bandwidthAllocated = $primaryBalance
    ? $primaryBalance['download_speed_limit'] . ' Mbps'
    : 'N/A';
$transferRate = $primaryBalance
    ? $primaryBalance['upload_speed_limit'] . ' Mbps'
    : 'N/A';
$latestStatus = $profile['current_status'] ?? 'Unknown';
$statusChangedAt = $profile['status_changed_at']
    ? date('Y-m-d H:i', strtotime((string)$profile['status_changed_at']))
    : 'N/A';
$monetaryBalance = $primaryBalance
    ? number_format((float)$primaryBalance['monetary_balance'], 2)
    : '0.00';

require_once VIEWS_ROOT . '/asset_for_pages/user_header.php';
?>

<div class="page-header">
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="<?= WEB_BASE ?>/php_views/user/user_profile.php">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">User</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">User Profile</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
    </ul>
</div>

<!-- User Form Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Profile</div>
            </div>
            <div class="card-body">
                <?php if ($profile === null): ?>
                    <div class="alert alert-warning mb-0">
                        No profile data found for user ID <strong><?= htmlspecialchars($requestedUserId) ?></strong>.
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="form-group row">
                            <label for="username" class="col-sm-2 col-form-label">User ID:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($profile['user_id']) ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="fullName" class="col-sm-2 col-form-label">Full Name:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="fullName" value="<?= htmlspecialchars($profile['full_name']) ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Role:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($profile['role_name']) ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Current Status:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="<?= htmlspecialchars($latestStatus) ?> (updated <?= htmlspecialchars($statusChangedAt) ?>)" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-sm-2 col-form-label">Access Code:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="password" name="password" value="<?= htmlspecialchars($profile['access_code'] ?? '') ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="bandwidth" class="col-sm-2 col-form-label">Bandwidth Allocated:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="bandwidth" name="bandwidth" value="<?= htmlspecialchars($bandwidthAllocated) ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="transferRate" class="col-sm-2 col-form-label">Upload Rate:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="transferRate" name="transferRate" value="<?= htmlspecialchars($transferRate) ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Balance:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" value="$<?= htmlspecialchars($monetaryBalance) ?>" readonly>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Devices Table Section -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Assigned Devices</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Total Data (GB)</th>
                                <th>Last Activity</th>
                                <th>Session</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sessions)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No sessions recorded yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($sessions as $session): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($session['host_name'] ?: $session['mac_address']) ?></td>
                                        <td><?= htmlspecialchars(number_format((float)$session['total_gb'], 3)) ?></td>
                                        <td>
                                            <?php if ($session['logout_timestamp'] === null): ?>
                                                Active now
                                            <?php else: ?>
                                                <?= htmlspecialchars(date('Y-m-d H:i', strtotime((string)$session['logout_timestamp']))) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($session['session_id']) ?></td>
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

<script>
// Form validation and submission
$(document).ready(function () {
    $("#delete").on("submit", function (event) {
        event.preventDefault();
        
        showNotification("Device deleted successfully!", "success");
        $("#delete")[0].reset();
    });
});

// Function to show notification
function showNotification(message, type) {
    $.notify({
        title: "Notification",
        message: message,
        icon: "fa fa-bell"
    }, {
        type: type,
        placement: {
            from: "top",
            align: "center"
        },
        animate: {
            enter: "animated fadeInDown",
            exit: "animated fadeOutUp"
        },
        delay: 4000
    });
}
</script>
