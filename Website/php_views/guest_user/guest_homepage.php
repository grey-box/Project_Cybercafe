<?php
declare(strict_types=1);
$pageTitle = "Guest - Home";

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

/** @var PDO $pdo */
$pdo = require $_SERVER['DOCUMENT_ROOT'] . '/Website/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/data_helpers.php';

$allowedSites = fetchAllowedSites($pdo);
$blockedSites = fetchBlockedSites($pdo);

require_once VIEWS_ROOT . '/asset_for_pages/guest_header.php';
?>

<style>
  .info-icon {
    font-size: 1.5em; /* Increase icon size */
    color: blue; /* Change icon color to blue */
    cursor: pointer; /* Change cursor to pointer */
  }
  .tooltip-inner {
    font-size: 1.25em; /* Increase tooltip text size */
  }
</style>

<div class="page-header">
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
            <a href="#">Allowed Sites</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
    </ul>
</div>
      <!--content goes here-->
<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">Allowed Sites</div>
        <span class="info-icon" data-toggle="tooltip" title="As a guest you can browse these pre-approved destinations.">
          <i class="fas fa-info-circle"></i>
        </span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Website</th>
                <th>Added On</th>
                <th>Visit</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($allowedSites)): ?>
                <tr>
                  <td colspan="3" class="text-center text-muted">No sites have been approved yet.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($allowedSites as $row): ?>
                  <tr>
                    <td>
                      <a href="<?= htmlspecialchars($row['url']) ?>" target="_blank" rel="noopener">
                        <?= htmlspecialchars($row['url']) ?>
                      </a>
                    </td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime((string)$row['created_at']))) ?></td>
                    <td>
                      <a class="btn btn-info btn-sm" href="<?= htmlspecialchars($row['url']) ?>" target="_blank" rel="noopener">
                        Visit
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

  <div class="col-md-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div class="card-title">Restricted Sites</div>
        <span class="info-icon" data-toggle="tooltip" title="These destinations are blocked for safety or policy reasons.">
          <i class="fas fa-info-circle"></i>
        </span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Website</th>
                <th>Added On</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($blockedSites)): ?>
                <tr>
                  <td colspan="2" class="text-center text-muted">No restrictions are currently in place.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($blockedSites as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['url']) ?></td>
                    <td><?= htmlspecialchars(date('Y-m-d', strtotime((string)$row['created_at']))) ?></td>
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
      <!-- content end -->


<?php
// Include the footer
require_once VIEWS_ROOT . '/asset_for_pages/footer.php'
?>

<script>
// Enable tooltips
document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
