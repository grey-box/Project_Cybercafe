<?php
<<<<<<< HEAD
declare(strict_types=1);

=======
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['admin']);
>>>>>>> main
// Set the page title dynamically
$pageTitle = "A - Device Management";

$root = dirname(__DIR__, 2);

/** @var PDO $pdo */
$pdo = require $root . '/config/db.php';
require_once $root . '/config/data_helpers.php';

$rows = fetchDeviceStatusList($pdo, 50);

$devices = [
    ['Device ID', 'Device Name', 'User', 'Status', 'Last Seen', 'IP Address'],
];

$onlineDevices = 0;
$offlineDevices = 0;
$maintenanceDevices = 0;

foreach ($rows as $row) {
    $deviceId = (string)($row['session_id'] ?? '');
    $deviceName = trim((string)($row['host_name'] ?? ''));
    if ($deviceName === '') {
        $deviceName = 'Unknown Device';
    }
    $userName = trim((string)($row['full_name'] ?? ''));
    if ($userName === '') {
        $userName = (string)($row['user_id'] ?? 'Unknown User');
    }
    $status = (string)($row['status'] ?? 'Unknown');
    $lastSeen = (string)($row['last_seen_label'] ?? 'Unknown');
    $ip = (string)($row['ip_address'] ?? 'N/A');

    if ($status === 'Online') {
        $onlineDevices++;
    } elseif ($status === 'Offline') {
        $offlineDevices++;
    } elseif ($status === 'Maintenance') {
        $maintenanceDevices++;
    }

    $devices[] = [
        $deviceId,
        $deviceName,
        $userName,
        $status,
        $lastSeen,
        $ip,
    ];
}

$totalDevices = count($rows);

// Include the header
include('../asset_for_pages/admin_header.php');
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Device Management</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="<?php echo $adminBase; ?>/adashboard.php">
                    <i class="icon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">Management</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">Devices</a>
            </li>
        </ul>
    </div>

    <!-- Device Status Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-success"><?php echo htmlspecialchars((string)$onlineDevices); ?></h4>
                    <p class="mb-0">Online Devices</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-danger"><?php echo htmlspecialchars((string)$offlineDevices); ?></h4>
                    <p class="mb-0">Offline Devices</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-warning"><?php echo htmlspecialchars((string)$maintenanceDevices); ?></h4>
                    <p class="mb-0">Maintenance</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-info"><?php echo htmlspecialchars((string)$totalDevices); ?></h4>
                    <p class="mb-0">Total Devices</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Management Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="card-title">Device Status</div>
                    <button class="btn btn-primary" onclick="addDevice()">Add Device</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Device ID</th>
                                    <th>Device Name</th>
                                    <th>Current User</th>
                                    <th>Status</th>
                                    <th>Last Seen</th>
                                    <th>IP Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for($i = 1; $i < count($devices); $i++): ?>
                                    <tr>
                                        <td><?= $devices[$i][0] ?></td>
                                        <td><?= $devices[$i][1] ?></td>
                                        <td><?= $devices[$i][2] ?></td>
                                        <td>
                                            <span class="badge <?= $devices[$i][3] == 'Online' ? 'bg-success' : ($devices[$i][3] == 'Offline' ? 'bg-danger' : 'bg-warning') ?>">
                                                <?= $devices[$i][3] ?>
                                            </span>
                                        </td>
                                        <td><?= $devices[$i][4] ?></td>
                                        <td><?= $devices[$i][5] ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">View Details</button>
                                            <button class="btn btn-sm btn-warning">Restart</button>
                                            <button class="btn btn-sm btn-danger">Shutdown</button>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Settings -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Network Settings</div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Network Range</label>
                        <input type="text" class="form-control" value="192.168.1.1/24" readonly>
                    </div>
                    <div class="form-group">
                        <label>Gateway</label>
                        <input type="text" class="form-control" value="192.168.1.1" readonly>
                    </div>
                    <div class="form-group">
                        <label>DNS Server</label>
                        <input type="text" class="form-control" value="8.8.8.8" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">System Actions</div>
                </div>
                <div class="card-body">
                    <button class="btn btn-warning btn-block mb-2">Restart All Devices</button>
                    <button class="btn btn-info btn-block mb-2">Update Device Software</button>
                    <button class="btn btn-success btn-block mb-2">Backup Device Configs</button>
                    <button class="btn btn-danger btn-block">Emergency Shutdown All</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addDevice() {
    alert('Add Device functionality would be implemented here');
}
</script>

<?php include('../asset_for_pages/footer.php'); ?>
