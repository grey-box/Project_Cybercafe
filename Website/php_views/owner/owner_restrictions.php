<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db_functions.php';

require_roles(['owner']);
// Set the page title dynamically
$pageTitle = "O - Restrictions";

// Include the header
include $_SERVER['DOCUMENT_ROOT'] . '/Website/php_views/asset_for_pages/owner_header.php';


$users = [
    ['username' => 'Admin', 'currentSpeedLane' => 'High'],
    ['username' => 'Regular User', 'currentSpeedLane' => 'Mid'],
    ['username' => 'Guest User', 'currentSpeedLane' => 'Mid'],
];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['add_url'])) {
    $url = trim($_POST['add_url']);
    if (!str_starts_with($url, 'http')) $url = 'https://' . $url;

    blockUrl($url, $_SESSION['user_id'] ?? 'ADMIN');
    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => 'URL has been blocked: <strong>' . htmlspecialchars($url) . '</strong>'
    ];
    echo "<script>
    setTimeout(() => {
        window.location.href = window.location.pathname;
    }, 100);
</script>";
    exit;
}

// ————— REMOVE URL —————
if (isset($_GET['remove'])) {
    $url = trim($_GET['remove']);
    get_db()->prepare("DELETE FROM url_restriction WHERE url = ?")->execute([$url]);

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => 'URL has been removed: <strong>' . htmlspecialchars($url) . '</strong>'
    ];

    echo "<script>
    setTimeout(() => {
        window.location.href = window.location.pathname;
    }, 100);
</script>";
    exit;
}

if (isset($_POST['block_device'])) {
    $mac = strtoupper(trim($_POST['mac_address']));

    if (preg_match('/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/', $mac)) {
        blockDevice($mac, 'Blocked via admin panel');
        $_SESSION['flash'] = [
            'type' => 'success',
            'msg'  => "Device blocked: <strong>$mac</strong>"
        ];
    } else {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'msg'  => 'Invalid MAC address!'
        ];
    }
    echo "<script>setTimeout(() => location.href = location.pathname, 100);</script>";
    exit;
}

// ————— UNBLOCK DEVICE —————
if (isset($_GET['unblock_device'])) {
    $mac = strtoupper(trim($_GET['unblock_device']));
    unblockDevice($mac);
    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => "Device unblocked: <strong>$mac</strong>"
    ];
    echo "<script>setTimeout(() => location.href = location.pathname, 100);</script>";
    exit;
}


$restrictedevices = getBlockedDevices();

$blockedUrls = getBlockedUrls();

echo '<pre>';
var_dump($blockedUrls);
var_dump($restrictedevices);
echo '</pre>';
//die();

?>

<style>
    .info-icon {
        font-size: 1.5em;
        color: blue;
        cursor: pointer;
    }

    .tooltip-inner {
        font-size: 1.25em;
    }
</style>

<div class="page-header">
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="#">
                <i class="fas fa-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="fas fa-chevron-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Restrictions</a>
        </li>
        <li class="separator">
            <i class="fas fa-chevron-right"></i>
        </li>
    </ul>
</div>

<!-- Restriction Cards Section -->
<div class="row">

    <!-- Card 1: URL Restrictions -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">URL Block List</div>
                <span class="info-icon" data-toggle="tooltip" title="Restrict specific URLs by adding them here. You can also remove them from the list below.">
                    <i class="fas fa-info-circle"></i>
                </span>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="input-group mb-3">
                        <input
                            type="text"
                            name="add_url"
                            class="form-control"
                            placeholder="Add URL to block"
                            list="urlSuggestions"
                            required>
                        <button type="submit" class="btn btn-danger">Block</button>
                    </div>
                </form>

                <!-- Auto-suggestions from database -->
                <datalist id="urlSuggestions">
                    <?php foreach ($blockedUrls as $url): ?>
                        <option value="<?= htmlspecialchars($url) ?>">
                        <?php endforeach; ?>
                </datalist>

                <?php if (isset($_SESSION['flash'])): ?>
                    <?php
                    $f = $_SESSION['flash'];
                    unset($_SESSION['flash']);
                    ?>
                    <div class="alert alert-<?= $f['type'] ?> alert-dismissible fade show">
                        <?= $f['msg'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                    <script>
                        setTimeout(() => {
                            window.location.href = window.location.pathname + window.location.search;
                        }, 100);
                    </script>
                <?php endif; ?>



                <!-- Dynamic Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Blocked URL</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blockedUrls as $url): ?>
                                <tr>
                                    <td><?= htmlspecialchars($url) ?></td>
                                    <td>
                                        <a href="?remove=<?= urlencode($url) ?>"
                                            class="btn btn-danger btn-sm">
                                            Remove
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>


    <!-- Card 2: Allowed URLs for Guest Users -->
    <!-- Restricted Devices Card -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">Restricted Devices (MAC)</div>
                <span class="info-icon" data-toggle="tooltip" title="Block devices by MAC address. Use format: AA:BB:CC:DD:EE:FF">
                    <i class="fas fa-info-circle"></i>
                </span>
            </div>
            <div class="card-body">

                <!-- Add Device Form -->
                <form method="POST" class="mb-4">
                    <div class="input-group">
                        <input
                            type="text"
                            name="mac_address"
                            class="form-control"
                            placeholder="AA:BB:CC:DD:EE:FF"
                            pattern="([0-9A-F]{2}:){5}[0-9A-F]{2}"
                            title="Format: AA:BB:CC:DD:EE:FF"
                            list="restrictedDevices"
                            required>
                        <button type="submit" name="block_device" class="btn btn-danger">Block Device</button>
                    </div>
                </form>

                <!-- Auto-suggestions from blocked devices -->
                <datalist id="restrictedDevices">
                    <?php foreach (getBlockedDevices() as $device): ?>
                        <option value="<?= htmlspecialchars($device['mac_address']) ?>">
                        <?php endforeach; ?>
                </datalist>

                <!-- Flash Message (same system as URLs) -->
                <?php if (isset($_SESSION['flash'])): ?>
                    <?php $f = $_SESSION['flash'];
                    unset($_SESSION['flash']); ?>
                    <div class="alert alert-<?= $f['type'] ?> alert-dismissible fade show mb-3">
                        <?= $f['msg'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <script>
                        setTimeout(() => location.href = location.pathname, 100);
                    </script>
                <?php endif; ?>

                <!-- Blocked Devices Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>MAC Address</th>
                                <th>Reason</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty(getBlockedDevices())): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No devices blocked</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (getBlockedDevices() as $device): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($device['mac_address']) ?></code></td>
                                        <td><?= htmlspecialchars($device['reason'] ?? '—') ?></td>
                                        <td>
                                            <a href="?unblock_device=<?= urlencode($device['mac_address']) ?>"
                                                class="btn btn-danger btn-sm"> 
                                                Unblock
                                            </a>
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

    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">Speed Lane Assigning</div>
                <span class="info-icon" data-toggle="tooltip" title="Assign different bandwidth tiers (Low, Mid, High) to users, controlling their internet speed based on their needs.">
                    <i class="fas fa-info-circle"></i>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Left Side: Speed Lane Input Fields -->
                    <div class="col-md-6">
                        <h5>Speed Lane</h5>
                        <p>Define bandwidth limits for different user categories.</p>
                        <div class="mb-3">
                            <label for="lowSpeed">Low:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="lowSpeed" value="13434">
                                <span class="input-group-text">Mbps</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="midSpeed">Mid:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="midSpeed" value="234">
                                <span class="input-group-text">Mbps</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="highSpeed">High:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="highSpeed" value="234">
                                <span class="input-group-text">Mbps</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Assigning Users -->
                    <div class="col-md-6">
                        <h5>Assign Users</h5>
                        <p>Select a speed lane for each user below.</p>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Speed Lane</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user) : ?>
                                    <tr>
                                        <td><?php echo $user['username']; ?></td>
                                        <td>
                                            <select class="form-control">
                                                <option <?php echo $user['currentSpeedLane'] == 'Low' ? 'selected' : ''; ?>>Low</option>
                                                <option <?php echo $user['currentSpeedLane'] == 'Mid' ? 'selected' : ''; ?>>Mid</option>
                                                <option <?php echo $user['currentSpeedLane'] == 'High' ? 'selected' : ''; ?>>High</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- speed late assigning end -->
</div>

<?php
// Include the footer
include $_SERVER['DOCUMENT_ROOT'] . '/Website/php_views/asset_for_pages/footer.php';
?>

<script>
    // Function to confirm and remove a row
    function removeRow(button) {
        const confirmation = confirm("Do you really want to remove this item?");
        if (confirmation) {
            const row = button.closest('tr');
            row.parentNode.removeChild(row);
        }
    }

    // Enable tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>