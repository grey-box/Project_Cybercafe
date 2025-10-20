<?php
// Set the page title dynamically
$pageTitle = "O - Feature Toggle"; 

// Include the header
include $_SERVER['DOCUMENT_ROOT'] . '/php_views/asset_for_pages/owner_header.php';

// Simulate getting the Data Sharing Percentage from the database or other source
$dataSharingPercentage = 50; // This can be dynamically fetched from the database

// Simulate user list data for speed lanes (you could dynamically fetch this from the database)

?>

<!-- Start of the Container -->
    <div class="page-header">
      <h3 class="fw-bold mb-3">Feature List</h3>
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
          <a href="#">Feature Toggle</a>
        </li>
        <li class="separator">
          <i class="icon-arrow-right"></i>
        </li>
      </ul>
    </div>

    <div class="row">

      <!-- New Card for Payments -->
      <div class="col-md-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Payments</div>
              <span class="info-icon" data-toggle="tooltip" title="Toggle this switch to enable or disable payment functionality for users, allowing them to manage their payments and access services based on payment status.">
                <i class="fas fa-info-circle"></i>
              </span>
            </div>
          <div class="card-body">
            <p>This toggle enables or disables payment functionality.</p>
              <div class="form-check form-switch" style="--bs-form-switch-width:60px;--bs-form-switch-height:24px">
                <input class="form-check-input" type="checkbox" role="switch" id="switch"/>
              </div>
          </div>
        </div>
      </div>

      <!-- New Card for User Restrictions -->
      <div class="col-md-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">User Restrictions</div>
              <span class="info-icon" data-toggle="tooltip" title="Toggle this switch to apply restrictions for specific users, managing their access based on device usage, data limits, or predefined rules.">
                <i class="fas fa-info-circle"></i>
              </span>
            </div>
          <div class="card-body">
            <p>Toggle user restrictions for specific users based on device usage or data limits.</p>
              <div class="form-check form-switch" style="--bs-form-switch-width:60px;--bs-form-switch-height:24px">
                <input class="form-check-input" type="checkbox" role="switch" id="switch"/>
              </div>
          </div>
        </div>
      </div>

      <!-- New Card for URL Restrictions -->
      <div class="col-md-12">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div class="card-title">URL Allowed/Block</div>
      <span class="info-icon" data-toggle="tooltip" title="Toggle this switch to enable or disable URL-based restrictions, allowing control over user access to specific websites or online content.">
        <i class="fas fa-info-circle"></i>
      </span>
    </div>
    <div class="card-body">
      <p>Enable or disable URL-based restrictions for user access.</p>
      <div class="form-check form-switch" style="--bs-form-switch-width:60px;--bs-form-switch-height:24px">
        <input class="form-check-input" type="checkbox" role="switch" id="switch" checked/>
      </div>
    </div>
  </div>
</div>

      <!-- New Card for Guest Access -->
      <div class="col-md-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Guest Internet Access Restrictions</div>
              <span class="info-icon" data-toggle="tooltip" title="Toggle this switch to allow or restrict guest users from accessing the internet, managing their connectivity as needed.">
                <i class="fas fa-info-circle"></i>
              </span>
            </div>
          <div class="card-body">
            <p>This toggle helps you to allow the guest user to access the internet or not.</p>
              <div class="form-check form-switch" style="--bs-form-switch-width:60px;--bs-form-switch-height:24px">
                <input class="form-check-input" type="checkbox" role="switch" id="switch"/>
              </div>
          </div>
        </div>
      </div>

      <div class="col-md-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Speed Lane</div>
              <span class="info-icon" data-toggle="tooltip" title="Toggle this switch to allow or restrict guest users from accessing the internet, managing their connectivity as needed.">
                <i class="fas fa-info-circle"></i>
              </span>
            </div>
          <div class="card-body">
            <p>This toggle helps you assign the speed lane aka Data transfer rate to each users.</p>
              <div class="form-check form-switch" style="--bs-form-switch-width:60px;--bs-form-switch-height:24px">
                <input class="form-check-input" type="checkbox" role="switch" id="switch"/>
              </div>
          </div>
        </div>
      </div>

      

      

      <!-- New Card for Data Sharing Percentage -->
      <div class="col-md-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title">Data Sharing Percentage</div>
              <span class="info-icon" data-toggle="tooltip" title="Adjust the slider to set the percentage of your data (out of 200 GB) to share with other users. Use the number field for precise adjustments.">
                <i class="fas fa-info-circle"></i>
              </span>
            </div>
          <div class="card-body">
            <p>This toggle enables or disables the data sharing percentage with other users. Adjust the slider to control the data you want to share.</p>

            <!-- Slider and Text Field for Data Sharing Percentage -->
            <div class="form-group d-flex justify-content-between align-items-center">
              <div style="flex: 1; display: flex; align-items: center;">
                <!-- Slider takes 60% width -->
                <input type="range" class="form-control-range" id="dataSharingSlider" min="10" max="100" value="<?php echo $dataSharingPercentage; ?>" oninput="updateDataSharingValue(this.value)" style="width: 60%;"/>
                
                <!-- Text field takes 30% width -->
                <input type="number" class="form-control" id="dataSharingValue" min="10" max="100" value="<?php echo $dataSharingPercentage; ?>" oninput="updateSliderValue(this.value)" style="width: 30%; margin-left: 10px;" />
                
                <!-- Percentage sign takes 10% width -->
                <span style="margin-left: 5px; font-size: 16px;">% of 200 GB</span>
              </div>
            </div>

          </div>
        </div>
      </div>

      

    </div>
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

// Function to capitalize the first letter of a string (for dynamic tooltip IDs)
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Function to update the value of the text field based on slider
function updateDataSharingValue(value) {
    document.getElementById('dataSharingValue').value = value;
}

// Function to update the slider value based on text field input
function updateSliderValue(value) {
    if (value >= 10 && value <= 100) {
        document.getElementById('dataSharingSlider').value = value;
    }
}
</script>

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
