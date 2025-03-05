<?php
// Set the page title dynamically
$pageTitle = "O - Feature Toggle"; 

// Include the header
include('../asset_for_pages/header.php');

?>

        <div class="page-inner">
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

                <!-- End of the page header -->
             
            <div class="col-md-12">

                <div class="card">
                  <div class="card-header">
                    <div class="card-title">Payments</div>
                    <div class="form-check form-switch" style="--bs-form-switch-width:60px;--bs-form-switch-height:24px">
                      <input class="form-check-input" type="checkbox" role="switch" id="switchSizeLargeChecked" checked />
                    </div>
                  </div>
                </div>

                <div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="card-title">Payments</div>
            <div class="info-button-wrapper">
                <!-- Info button to show tooltip -->
                <button class="info-button" aria-label="More Info" onclick="toggleTooltip()">
                    <span>i</span>
                </button>
                <!-- Tooltip content -->
                <div class="info-tooltip">
                    This feature lets you use/disable the payments feature. When you enable this feature, this switches on the
                    payment options for the regular users, which makes the users pay for the access to this service.
                </div>
            </div>
        </div>
        <p>This toggle enables or disables payment functionality.</p>
        <div class="form-check form-switch" style="--bs-form-switch-width:60px;--bs-form-switch-height:24px">
            <input class="form-check-input" type="checkbox" role="switch" id="switchSizeLargeChecked" checked />
        </div>
    </div>
</div>





                <div class="card">
                  <div class="card-header">
                    <div class="card-title">User Restrictions</div>
                    <div class="form-check form-switch" style="--bs-form-switch-width:60px;--bs-form-switch-height:24px">
                      <input class="form-check-input" type="checkbox" role="switch" id="switchSizeLargeChecked" checked />
                    </div>
                  </div>
                </div>

                <div class="card">
                  <div class="card-header">
                    <div class="card-title">URL Restrictions</div>
                    <div class="form-check form-switch" style="--bs-form-switch-width:60px;--bs-form-switch-height:24px">
                      <input class="form-check-input" type="checkbox" role="switch" id="switchSizeLargeChecked" checked />
                    </div>
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
