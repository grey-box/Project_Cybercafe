<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db_functions.php';

require_roles(['owner']);
// Set the page title dynamically
$pageTitle = "Report Page";

// Include the header
include $_SERVER['DOCUMENT_ROOT'] . '/Website/php_views/asset_for_pages/owner_header.php';


// Example array of demo data (replace with actual project data later)
// $reportData = [
//   [
//     'User ID'      => 'Saniket',
//     'Usage (MB)'   => 750,
//     'Session Time' => '1h 20m',
//     'Status'       => 'Active'
//   ],
//   [
//     'User ID'      => 'GBale',
//     'Usage (MB)'   => 1200,
//     'Session Time' => '2h 05m',
//     'Status'       => 'Active'
//   ],
//   [
//     'User ID'      => 'RLouis',
//     'Usage (MB)'   => 500,
//     'Session Time' => '0h 45m',
//     'Status'       => 'Inactive'
//   ],
// ];


$result = getUserSessionSummary();

var_dump($result);

?>

<div class="page-header">
  <h3 class="fw-bold mb-3">Report Page</h3>
  <ul class="breadcrumbs mb-3">
    <li class="nav-home">
      <a href="#">
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
  </ul>
</div>

<div class="row">
  <div class="col-md-12">
    <!-- Icons for Download and Export -->
    <div class="d-flex justify-content-end mb-3">
      <button class="btn btn-secondary me-2">
        <i class="fas fa-download"></i> Download
      </button>
      <button class="btn btn-secondary">
        <i class="fas fa-file-export"></i> Export
      </button>
    </div>

    <!-- Report Results Section -->
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Report Results</h4>
      </div>
      <div class="card-body">
        <p class="small">
          <strong>This is DEMO data.</strong> This section will show the final report based on owner/admin selections.
        </p>

        <?php if (!empty($reportData)): ?>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>User ID</th>
                  <th>Usage (MB)</th>
                  <th>Session Time</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($reportData as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['User ID']) ?></td>
                    <td><?= htmlspecialchars($row['Usage (MB)']) ?></td>
                    <td><?= htmlspecialchars($row['Session Time']) ?></td>
                    <td><?= htmlspecialchars($row['Status']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p>No report data available yet.</p>
        <?php endif; ?>
      </div>
    </div>
    <!-- content end -->
  </div>
</div>

<?php
// Include the footer
include $_SERVER['DOCUMENT_ROOT'] .'/Website/php_views/asset_for_pages/footer.php';
?>
