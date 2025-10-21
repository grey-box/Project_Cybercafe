<?php
// Set the page title dynamically
$pageTitle = "A - Help & Support"; 

// Include the header
include('../asset_for_pages/admin_header.php');

// Sample support topics
$supportTopics = [
    ['Category', 'Topic', 'Status', 'Last Updated'],
    ['Technical', 'How to reset user passwords', 'Active', '2 days ago'],
    ['Technical', 'Network troubleshooting guide', 'Active', '1 week ago'],
    ['Billing', 'How to process refunds', 'Active', '3 days ago'],
    ['General', 'Cafe operating hours', 'Active', '1 month ago'],
    ['Technical', 'Device maintenance schedule', 'Active', '2 weeks ago']
];
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Help & Support Center</h3>
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
                <a href="#">Support</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">Help Center</a>
            </li>
        </ul>
    </div>

    <!-- Quick Help Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-question-circle fa-3x text-primary mb-3"></i>
                    <h5>FAQ</h5>
                    <p class="text-muted">Frequently Asked Questions</p>
                    <a href="<?php echo $adminBase; ?>/afaq_table.php" class="btn btn-primary">View FAQ</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-comments fa-3x text-success mb-3"></i>
                    <h5>Live Chat</h5>
                    <p class="text-muted">Chat with support team</p>
                    <a href="<?php echo $adminBase; ?>/chat.php" class="btn btn-success">Start Chat</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-ticket-alt fa-3x text-warning mb-3"></i>
                    <h5>Support Tickets</h5>
                    <p class="text-muted">Manage support requests</p>
                    <a href="<?php echo $adminBase; ?>/support.php" class="btn btn-warning">View Tickets</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-bell fa-3x text-info mb-3"></i>
                    <h5>Notifications</h5>
                    <p class="text-muted">System notifications</p>
                    <a href="<?php echo $adminBase; ?>/notifications.php" class="btn btn-info">View Notifications</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Support Topics -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Support Topics</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Topic</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for($i = 1; $i < count($supportTopics); $i++): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary"><?= $supportTopics[$i][0] ?></span>
                                        </td>
                                        <td><?= $supportTopics[$i][1] ?></td>
                                        <td>
                                            <span class="badge bg-success"><?= $supportTopics[$i][2] ?></span>
                                        </td>
                                        <td><?= $supportTopics[$i][3] ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary">View</button>
                                            <button class="btn btn-sm btn-warning">Edit</button>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Quick Actions</div>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="createTicket()">Create Support Ticket</button>
                        <button class="btn btn-success" onclick="viewSystemLogs()">View System Logs</button>
                        <button class="btn btn-info" onclick="contactSupport()">Contact Support Team</button>
                        <button class="btn btn-warning" onclick="viewDocumentation()">View Documentation</button>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <div class="card-title">Emergency Contacts</div>
                </div>
                <div class="card-body">
                    <p><strong>Technical Support:</strong><br>
                    Phone: (555) 123-4567<br>
                    Email: tech@cybercafe.com</p>
                    
                    <p><strong>Management:</strong><br>
                    Phone: (555) 123-4568<br>
                    Email: manager@cybercafe.com</p>
                    
                    <p><strong>Emergency (24/7):</strong><br>
                    Phone: (555) 911-CAFE<br>
                    Email: emergency@cybercafe.com</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function createTicket() {
    alert('Create Support Ticket functionality would be implemented here');
}

function viewSystemLogs() {
    alert('View System Logs functionality would be implemented here');
}

function contactSupport() {
    alert('Contact Support Team functionality would be implemented here');
}

function viewDocumentation() {
    alert('View Documentation functionality would be implemented here');
}
</script>

<?php include('../asset_for_pages/footer.php'); ?>
