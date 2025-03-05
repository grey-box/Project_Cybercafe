<?php
// Set the page title dynamically
$pageTitle = "Guest - Home"; 

// Include the header
include('../asset_for_pages/guest_header.php');
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
            <a href="#">Guest Login</a>
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
                    <td>https://example.com</td>
                    <td><button class="btn btn-info btn-sm" onclick="removeRow(this)">Visit</button></td>
                  </tr>
                  <tr>
                    <td>https://anotherurl.com</td>
                    <td><button class="btn btn-info btn-sm" onclick="removeRow(this)">Visit</button></td>
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
include('../asset_for_pages/footer.php');
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