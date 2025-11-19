<?php
<<<<<<< HEAD
declare(strict_types=1);
$pageTitle = "A - Dashboard";
=======
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['admin']);
// Set the page title dynamically
$pageTitle = "A - Dashboard"; 
>>>>>>> main

$root = dirname(__DIR__, 2);

/** @var PDO $pdo */
$pdo = require $root . '/config/db.php';
require_once $root . '/config/data_helpers.php';

$activeSessions = fetchDashboardActiveSessions($pdo, 7);
$bandwidthUsage = fetchDashboardBandwidthUsage($pdo);
$systemEvents = fetchDashboardEventLog($pdo, 8);
$activeDeviceCount = fetchActiveDeviceCount($pdo);
$deviceUsage = fetchDeviceUsage($pdo, 5);
$recentPayments = fetchRecentPayments($pdo, 5);
$balanceAlerts = fetchBalanceAlerts($pdo, 5);

$chartLabels = array_column($bandwidthUsage, 'full_name');
$chartData = array_map(function (array $row): float {
    return (float)$row['total_gb'];
}, $bandwidthUsage);
$totalBandwidth = array_sum($chartData);
$deviceLabels = array_column($deviceUsage, 'label');
$deviceData = array_map(static fn(array $row): float => $row['total_gb'], $deviceUsage);

include '../asset_for_pages/admin_header.php';
?>

<div class="page-inner">
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

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Total Bandwidth (GB)</div>
                </div>
                <div class="card-body">
                    <canvas id="bandwidthChart"></canvas>
                    <div class="mt-3">Total Used: <?php echo htmlspecialchars(number_format($totalBandwidth, 2)); ?> GB</div>
                </div>
            </div>
        </div>

        <div class="col-md-4 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">Recent Sessions</div>
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
                                <th>Data Rate (KB/s)</th>
                                <th>Last Active</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($activeSessions)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No recent sessions to display.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($activeSessions as $session): ?>
                                    <tr>
                                        <td>
                                            <a style="text-decoration: none; color: inherit;"
                                               href="<?php echo $adminBase; ?>/auser_info_add_and_edit.php?user=<?php echo htmlspecialchars($session['user_id']); ?>">
                                                <?php echo htmlspecialchars($session['full_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars(number_format((float)$session['avg_kb_s'], 1)); ?></td>
                                        <td><?php echo htmlspecialchars($session['last_activity_label']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
                    <?php if (empty($deviceUsage)): ?>
                        <p class="text-muted mt-3 mb-0">No device usage recorded yet.</p>
                    <?php else: ?>
                        <div class="mt-3">Devices Connected: <?php echo (int)$activeDeviceCount; ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-6">
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
                                <th>Total Data (GB)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($bandwidthUsage)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No usage data available.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bandwidthUsage as $usage): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($usage['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars(number_format((float)$usage['total_gb'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Recent Payments</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Time</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($recentPayments)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No payments recorded.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentPayments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['full_name']); ?></td>
                                        <td>$<?php echo htmlspecialchars(number_format((float)$payment['amount_charged'], 2)); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime((string)$payment['payment_datetime']))); ?></td>
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

    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Balance Alerts</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>User</th>
                                <th>Queue</th>
                                <th>Balance</th>
                                <th>Updated</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($balanceAlerts)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No negative balances detected.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($balanceAlerts as $alert): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($alert['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($alert['queue_name']); ?></td>
                                        <td class="<?php echo $alert['monetary_balance'] < 0 ? 'text-danger' : 'text-warning'; ?>">
                                            $<?php echo htmlspecialchars(number_format((float)$alert['monetary_balance'], 2)); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime((string)$alert['last_update_timestamp']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Latest System Events</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Event</th>
                                <th>Occurred</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($systemEvents)): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No recent events.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($systemEvents as $event): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($event['event_type']); ?>:</strong> <?php echo htmlspecialchars($event['description']); ?></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime((string)$event['occurred_at']))); ?></td>
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
</div>

<?php include '../asset_for_pages/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('bandwidthChart').getContext('2d');
const dashboardData = {
    labels: <?php echo json_encode($chartLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
    datasets: [{
        label: "Bandwidth Used (GB)",
        data: <?php echo json_encode($chartData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
        borderColor: "#007bff",
        backgroundColor: "rgba(0, 123, 255, 0.1)",
        borderWidth: 2,
        fill: true,
        tension: 0.3,
        pointRadius: 5,
        pointBackgroundColor: "#007bff"
    }]
};

const config = {
    type: "line",
    data: dashboardData,
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, position: "top" },
            tooltip: {
                callbacks: {
                    label: (ctx) => `${ctx.parsed.y.toFixed(2)} GB`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: "Bandwidth (GB)" }
            },
            x: {
                title: { display: true, text: "Users" }
            }
        }
    }
};

new Chart(ctx, config);

const devicesCtx = document.getElementById('devicesChart').getContext('2d');
new Chart(devicesCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($deviceLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
        datasets: [{
            label: 'Data Used (GB)',
            data: <?php echo json_encode($deviceData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
            backgroundColor: '#ffc107',
            borderRadius: 4,
            maxBarThickness: 42
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Data Used (GB)' }
            },
            x: {
                title: { display: true, text: 'Devices' }
            }
        }
    }
});

document.getElementById('toggleQuickLinks').addEventListener('click', function () {
    const quickLinks = document.getElementById('quickLinks');
    quickLinks.style.display = quickLinks.style.display === 'none' || quickLinks.style.display === '' ? 'block' : 'none';
});
</script>
