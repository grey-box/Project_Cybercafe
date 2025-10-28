<?php
// Set the page title dynamically
$pageTitle = "O - Report"; 

// Include the header
include $_SERVER['DOCUMENT_ROOT'] . '/Website/php_views/asset_for_pages/owner_header.php';

?>



<!-- Start of the Container -->

<div class="container">
  <div class="page-inner">
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
          <a href="#">Report</a>
        </li>
        <li class="separator">
          <i class="icon-arrow-right"></i>
        </li>
      </ul>
    </div>

    <!-- Report Filter Form Section -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="card-title">Create Report</div>
          </div>
          <div class="card-body">
            <form id="reportForm">
              
              <!-- Username Filter -->
              <div class="form-group row">
                <div class="col-sm-2">
                  <input type="checkbox" id="usernameCheck" onclick="toggleDropdown('usernameDropdown')">
                  <label for="usernameCheck">Username</label>
                </div>
                <div class="col-sm-4">
                  <select class="form-control" id="usernameDropdown" disabled>
                    <option>Select Username</option>
                    <option>User1</option>
                    <option>User2</option>
                    <option>User3</option>
                  </select>
                </div>
              </div>

              <!-- Device Filter -->
              <div class="form-group row">
                <div class="col-sm-2">
                  <input type="checkbox" id="deviceCheck" onclick="toggleDropdown('deviceDropdown')">
                  <label for="deviceCheck">Device</label>
                </div>
                <div class="col-sm-4">
                  <select class="form-control" id="deviceDropdown" disabled>
                    <option>Select Device</option>
                    <option>Desktop</option>
                    <option>Smartphone</option>
                    <option>Tablet</option>
                  </select>
                </div>
              </div>

              <!-- Data Usage Assigned Filter -->
              <div class="form-group row">
                <div class="col-sm-2">
                  <input type="checkbox" id="dataUsageCheck" onclick="toggleDropdown('dataUsageDropdown')">
                  <label for="dataUsageCheck">Data Usage Assigned</label>
                </div>
                <div class="col-sm-4">
                  <select class="form-control" id="dataUsageDropdown" disabled>
                    <option>Select Data Usage</option>
                    <option>10 GB</option>
                    <option>20 GB</option>
                    <option>50 GB</option>
                  </select>
                </div>
              </div>

              <!-- Bandwidth Assigned Filter -->
              <div class="form-group row">
                <div class="col-sm-2">
                  <input type="checkbox" id="bandwidthCheck" onclick="toggleDropdown('bandwidthDropdown')">
                  <label for="bandwidthCheck">Bandwidth Assigned</label>
                </div>
                <div class="col-sm-4">
                  <select class="form-control" id="bandwidthDropdown" disabled>
                    <option>Select Bandwidth</option>
                    <option>50 Mbps</option>
                    <option>100 Mbps</option>
                    <option>150 Mbps</option>
                  </select>
                </div>
              </div>

              <!-- Reset and Run Report Buttons -->
              <div class="form-group row">
                <div class="col-sm-12 text-right">
                  <button type="reset" class="btn btn-secondary">Reset</button>
                  <a href="report_display.php">
                    <button type="button" class="btn btn-primary">Run Report</button>
                  </a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<?php
// Include the footer
include $_SERVER['DOCUMENT_ROOT'] .'/Website/php_views/asset_for_pages/footer.php';
?>