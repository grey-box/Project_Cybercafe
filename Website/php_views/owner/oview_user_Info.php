<?php
// Set the page title dynamically
$pageTitle = "O - User Info"; 

// Include the header
include('../asset_for_pages/header.php');

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
          <a href="#">User</a>
        </li>
        <li class="separator">
          <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
          <a href="#">User Information</a>
        </li>
        <li class="separator">
          <i class="icon-arrow-right"></i>
        </li>
      </ul>
    </div>
    
    <!-- User Form Section -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="card-title">User Information</div>
          </div>
          <div class="card-body">
            <form>
              <div class="form-group row">
                <label for="name" class="col-sm-2 col-form-label">Name:</label>
                <div class="col-sm-4">
                  <input type="text" class="form-control" id="name" value="Aanchal Panchal">
                </div>
              </div>

              <div class="form-group row">
                <label for="accessCode" class="col-sm-2 col-form-label">Access Code:</label>
                <div class="col-sm-3">
                  <input type="text" class="form-control" id="accessCode" readonly>
                </div>
                <div class="col-sm-1">
                  <button type="button" class="btn btn-primary" onclick="generateCode()">Generate</button>
                </div>
              </div>

              <div class="form-group row">
                <label for="role" class="col-sm-2 col-form-label">Role:</label>
                <div class="col-sm-4">
                  <select class="form-control" id="role">
                    <option>Admin</option>
                    <option>Regular User</option>
                  </select>
                </div>
              </div>

              <div class="form-group row">
                <label for="bandwidth" class="col-sm-2 col-form-label">Bandwidth Allocated [Mbps]:</label>
                <div class="col-sm-4">
                  <select class="form-control" id="bandwidth">
                    <option>50</option>
                    <option>100</option>
                    <option>150</option>
                    <option>Custom</option>
                  </select>
                </div>
              </div>

              <div class="form-group row">
                <label for="dataUsage" class="col-sm-2 col-form-label">Data Usage:</label>
                <div class="col-sm-4">
                  <select class="form-control" id="dataUsage">
                    <option>10 GB</option>
                    <option>20 GB</option>
                    <option>Custom</option>
                    <option>Slider</option> <!-- Added Slider option -->
                  </select>
                </div>
              </div>

              <div class="form-group row">
                <label for="dataUsageSlider" class="col-sm-2 col-form-label">Data Usage %:</label>
                <div class="col-sm-4">
                  <input type="range" class="form-control-range" id="dataUsageSlider" min="0" max="100" step="1" oninput="updateSliderValue(this.value)">
                  <span id="sliderValue">0%</span>
                </div>
              </div>

              <div class="form-group row">
                <label for="transferRate" class="col-sm-2 col-form-label">Data Transfer Rate:</label>
                <div class="col-sm-4">
                  <select class="form-control" id="transferRate">
                    <option>Slow Lane</option>
                    <option>Fast Lane</option>
                  </select>
                </div>
              </div>

              <!-- Save and Cancel Buttons -->
              <div class="form-group row">
                <div class="col-sm-12 text-right">
                  <button type="button" class="btn btn-secondary">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Device Table Section -->
    <div class="row mt-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="card-title">Devices</div>
          </div>
          <div class="card-body">
            <!-- Responsive Table Wrapper -->
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Serial Number</th>
                    <th>Devices</th>
                    <th>MAC Address</th>
                    <th>Bandwidth Allocated [Mbps]</th>
                    <th>Data Usage</th> <!-- Added Data Usage column -->
                    <th>Data Transfer Rate</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>GKDGG</td>
                    <td>Desktop</td>
                    <td>03:43:5G:H6:43:53</td>
                    <td>50</td>
                    <td>10 GB</td> <!-- Example data for Data Usage -->
                    <td>Slow Lane</td>
                  </tr>
                  <tr>
                    <td>SN4345</td>
                    <td>Smartphone</td>
                    <td>04:63:4G:K2:45:21</td>
                    <td>50</td>
                    <td>20 GB</td> <!-- Example data for Data Usage -->
                    <td>Fast Lane</td>
                  </tr>
                </tbody>
              </table>
            </div> <!-- End of table-responsive -->
          </div>
        </div>
      </div>
    </div>


<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>