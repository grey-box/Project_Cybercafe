<?php
declare(strict_types=1);
$pageTitle = "Guest - Home";

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

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
          <!-- Card 1: Allowed Sites -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Allowed Sites</div>
            <span class="info-icon" data-toggle="tooltip" title="As a guest user you can only access these listed sites.">
              <i class="fas fa-info-circle"></i>
            </span>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Item List</th>
                    <th>Visit</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><a href="https://example.com" target="_blank" rel="noopener">https://example.com</a></td>
                    <td><a class="btn btn-info btn-sm" href="https://example.com" target="_blank" rel="noopener">Visit</a></td>
                  </tr>
                  <tr>
                    <td><a href="https://anotherurl.com" target="_blank" rel="noopener">https://anotherurl.com</a></td>
                    <td><a class="btn btn-info btn-sm" href="https://anotherurl.com" target="_blank" rel="noopener">Visit</a></td>
                  </tr>
                </tbody>
              </table>
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