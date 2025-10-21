<?php
// Set the page title dynamically
$pageTitle = "A - Dashboard"; 

// Include the header
include('../asset_for_pages/admin_header.php');

// Define table data arrays
$activeUsers = [
    ["John Doe", "25 KB/s", "4 mins ago."],
    ["Alex Doe", "13 KB/s", "1 min ago."],
    ["Guest User 1", "9 KB/s", "Active Now."]
];

$bandwidthUsage = [
    ["John Doe", 50],
    ["Jane Smith", 40],
    ["Michael Lee", 30]
];

$deviceStatus = [
  ["Hotspot Uptime (Last 24 hrs)", "23h 45m"],
  ["Hotspot Restarts (Last 24 hrs)", "1"],
  ["Time Since Last Reboot", "5h 20m"],
  ["Speed Tier (Low) - Bandwidth Used", "50 GB"],
  ["Speed Tier (Medium) - Bandwidth Used", "40 GB"],
  ["Speed Tier (High) - Bandwidth Used", "30 GB"]
];

?>

<div class="page-inner">
    <!-- Quick Links Section -->
    <div class="mb-4">
        <button class="btn btn-primary" id="toggleQuickLinks">Quick Links</button>
        <div id="quickLinks" class="mt-3">
            <div class="d-flex justify-content-around">
                <a href="<?php echo $adminBase; ?>/add_user.php" class="text-center text-decoration-none">
                    <i class="fas fa-user-plus fa-2x"></i>
                    <div>Add User</div>
                </a>
                <a href="<?php echo $adminBase; ?>/bandwidth_usage.php" class="text-center text-decoration-none">
                    <i class="fas fa-chart-line fa-2x"></i>
                    <div>Bandwidth Usage</div>
                </a>
                <a href="<?php echo $adminBase; ?>/device_management.php" class="text-center text-decoration-none">
                    <i class="fas fa-desktop fa-2x"></i>
                    <div>Device Management</div>
                </a>
                <a href="<?php echo $adminBase; ?>/help_support.php" class="text-center text-decoration-none">
                    <i class="fas fa-life-ring fa-2x"></i>
                    <div>Help & Support</div>
                </a>
            </div>
        </div>
    </div>

    <!-- Dashboard Main Content -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Total Bandwidths Used Jan 2025 4th Week</div>
                </div>
                <div class="card-body">
                    <canvas id="bandwidthChart"></canvas>
                    <div class="mt-3">Total Used: 120 GB</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">Active Users</div>
                    <a href="<?php echo $adminBase; ?>/add_user.php">
                        <button class="btn btn-success">Add User</button>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Names</th>
                                    <th>Data Rate [Avg of each 5mins]</th>
                                    <th>Last Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeUsers as $user): ?>
                                    <tr>
                                        <td><a style="text-decoration: none; color: inherit;" href="<?php echo $adminBase; ?>/auser_info_add_and_edit.php"><?= $user[0] ?></a></td>
                                        <td><?= $user[1] ?></td>
                                        <td><?= $user[2] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Active/Online Devices</div>
                </div>
                <div class="card-body">
                    <canvas id="devicesChart"></canvas>
                    <div class="mt-3">Device Connected: <span id="totalDevices">5</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bandwidth Usage Section -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Bandwidth Usage by Users</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User Name</th>
                                    <th>Data Used Per Day (GB)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bandwidthUsage as $usage): ?>
                                    <tr>
                                        <td><?= $usage[0] ?></td>
                                        <td><?= $usage[1] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Status Section -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Device Status</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deviceStatus as $status): ?>
                                    <tr>
                                        <td><?= $status[0] ?></td>
                                        <td><?= $status[1] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../asset_for_pages/footer.php'); ?>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
        const ctx = document.getElementById('bandwidthChart').getContext('2d');
        const data = {
            labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"], // Updated labels
            datasets: [{
                label: "Bandwidth Used (GB)",
                data: [15, 17, 12, 22, 20, 25, 30],
                borderColor: "#007bff",
                backgroundColor: "rgba(0, 123, 255, 0.1)",
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 5,
                pointBackgroundColor: "#007bff"
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var ctx = document.getElementById('devicesChart').getContext('2d');

        var deviceLabels = ["Device 1", "Device 2", "Device 3", "Device 4", "Device 5"];
        var deviceData = [10, 15, 5, 20, 25]; // Data usage in GB

        var devicesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: deviceLabels,
                datasets: [{
                    label: 'Data Used (GB)',
                    data: deviceData,
                    backgroundColor: '#ffc107',
                    borderColor: '#ffc107',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Update total devices count
        document.getElementById('totalDevices').innerText = deviceData.length;
    });
</script>
