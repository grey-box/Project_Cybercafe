<?php
<<<<<<< HEAD
declare(strict_types=1);

=======
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['admin']);
>>>>>>> main
// Set the page title dynamically
$pageTitle = "A - Bandwidth Usage";

$root = dirname(__DIR__, 2);

/** @var PDO $pdo */
$pdo = require $root . '/config/db.php';
require_once $root . '/config/data_helpers.php';

$rows = fetchPerUserBandwidthSummary($pdo);

// Shape data for the existing table rendering logic.
$bandwidthData = [
    ['User Name', 'Current Usage (GB)', 'Allocated (GB)', 'Usage %', 'Status'],
];

$totalUsed = 0.0;
$totalAllocated = 0.0;
$percentSum = 0.0;
$percentCount = 0;
$criticalCount = 0;

foreach ($rows as $row) {
    $name = trim((string)($row['full_name'] ?? ''));
    if ($name === '') {
        $name = (string)($row['user_id'] ?? 'Unknown User');
    }

    $used = (float)($row['used_gb'] ?? 0.0);
    $allocated = (float)($row['allocated_gb'] ?? 0.0);
    $percent = $row['usage_percent'];
    $status = (string)($row['usage_status'] ?? 'Unknown');

    $totalUsed += $used;
    $totalAllocated += $allocated;

    if ($percent !== null) {
        $percentSum += (float)$percent;
        $percentCount++;
    }

    if ($status === 'Critical') {
        $criticalCount++;
    }

    $percentLabel = $percent !== null
        ? number_format((float)$percent, 1) . '%'
        : 'N/A';

    $bandwidthData[] = [
        $name,
        number_format($used, 2),
        $allocated > 0.0 ? number_format($allocated, 2) : 'N/A',
        $percentLabel,
        $status,
    ];
}

$totalUsedLabel = number_format($totalUsed, 2) . ' GB';
$totalAllocatedLabel = $totalAllocated > 0.0
    ? number_format($totalAllocated, 2) . ' GB'
    : 'N/A';
$averageUsageLabel = $percentCount > 0
    ? number_format($percentSum / $percentCount, 1) . '%'
    : 'N/A';

// Include the header
include('../asset_for_pages/admin_header.php');
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Bandwidth Usage Report</h3>
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
                <a href="#">Reports</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">Bandwidth Usage</a>
            </li>
        </ul>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-primary"><?php echo htmlspecialchars($totalUsedLabel); ?></h4>
                    <p class="mb-0">Total Used</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-success"><?php echo htmlspecialchars($totalAllocatedLabel); ?></h4>
                    <p class="mb-0">Total Allocated</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-warning"><?php echo htmlspecialchars($averageUsageLabel); ?></h4>
                    <p class="mb-0">Average Usage</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-danger"><?php echo htmlspecialchars((string)$criticalCount); ?></h4>
                    <p class="mb-0">Critical Users</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bandwidth Usage Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">User Bandwidth Usage</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User Name</th>
                                    <th>Current Usage (GB)</th>
                                    <th>Allocated (GB)</th>
                                    <th>Usage %</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for($i = 1; $i < count($bandwidthData); $i++): ?>
                                    <tr>
                                        <td><?= $bandwidthData[$i][0] ?></td>
                                        <td><?= $bandwidthData[$i][1] ?></td>
                                        <td><?= $bandwidthData[$i][2] ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar <?= $bandwidthData[$i][4] == 'Critical' ? 'bg-danger' : ($bandwidthData[$i][4] == 'Warning' ? 'bg-warning' : 'bg-success') ?>" 
                                                     style="width: <?= $bandwidthData[$i][3] ?>"><?= $bandwidthData[$i][3] ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?= $bandwidthData[$i][4] == 'Critical' ? 'bg-danger' : ($bandwidthData[$i][4] == 'Warning' ? 'bg-warning' : 'bg-success') ?>">
                                                <?= $bandwidthData[$i][4] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">View Details</button>
                                            <button class="btn btn-sm btn-warning">Adjust Limit</button>
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
</div>

<?php include('../asset_for_pages/footer.php'); ?>
