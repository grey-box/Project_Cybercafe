<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['admin']);
// Set the page title dynamically
$pageTitle = "A - Bandwidth Usage"; 

// Include the header
include('../asset_for_pages/admin_header.php');

// Sample bandwidth usage data
$bandwidthData = [
    ['User Name', 'Current Usage (GB)', 'Allocated (GB)', 'Usage %', 'Status'],
    ['John Doe', '45', '100', '45%', 'Normal'],
    ['Jane Smith', '78', '100', '78%', 'Warning'],
    ['Mike Johnson', '95', '100', '95%', 'Critical'],
    ['Sarah Wilson', '32', '50', '64%', 'Normal'],
    ['Tom Brown', '48', '50', '96%', 'Critical']
];
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
                    <h4 class="text-primary">298 GB</h4>
                    <p class="mb-0">Total Used</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-success">400 GB</h4>
                    <p class="mb-0">Total Allocated</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-warning">74.5%</h4>
                    <p class="mb-0">Average Usage</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-danger">2</h4>
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
