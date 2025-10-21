<?php
// Set the page title dynamically
$pageTitle = "A - Device Management"; 

// Include the header
include('../asset_for_pages/admin_header.php');

// Sample device data
$devices = [
    ['Device ID', 'Device Name', 'User', 'Status', 'Last Seen', 'IP Address'],
    ['DEV001', 'Gaming PC 1', 'John Doe', 'Online', '2 mins ago', '192.168.1.101'],
    ['DEV002', 'Laptop Station 2', 'Jane Smith', 'Online', '5 mins ago', '192.168.1.102'],
    ['DEV003', 'Workstation 3', 'Mike Johnson', 'Offline', '1 hour ago', '192.168.1.103'],
    ['DEV004', 'Gaming PC 2', 'Sarah Wilson', 'Online', '1 min ago', '192.168.1.104'],
    ['DEV005', 'Laptop Station 1', 'Tom Brown', 'Maintenance', 'N/A', '192.168.1.105']
];
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Device Management</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="adashboard.php">
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
                    <h4 class="text-success">4</h4>
                    <p class="mb-0">Online Devices</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-danger">1</h4>
                    <p class="mb-0">Offline Devices</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-warning">1</h4>
                    <p class="mb-0">Maintenance</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="text-info">5</h4>
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
