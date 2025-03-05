<?php
// Set the page title dynamically
$pageTitle = "A - Choose Layout"; 

// Include the header
include('../asset_for_pages/header.php');

?>


<!-- Start of the Inner Container -->

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
            <a href="#">Admin</a>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>
          <li class="nav-item">
            <a href="#">Layout Configuration</a>
          </li>
        </ul>
      </div>
      
      <!-- Admin Layout Configuration Section -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-title">Choose Layout for Users</div>
            </div>
            <div class="card-body">
              <form id="layoutForm">
                <!-- Layout Type Selection -->
                <div class="form-group">
                  <label for="layoutType">Choose Layout Type</label>
                  <select class="form-control" id="layoutType">
                    <option value="grid">Grid Layout</option>
                    <option value="list">List Layout</option>
                  </select>
                </div>
  
                <!-- Theme Selection -->
                <div class="form-group">
                  <label for="theme">Choose Theme</label>
                  <select class="form-control" id="theme">
                    <option value="light">Light Theme</option>
                    <option value="dark">Dark Theme</option>
                    <option value="custom">Custom Theme</option>
                  </select>
                </div>
  
                <!-- Sidebar Visibility -->
                <div class="form-group">
                  <label for="sidebarVisibility">Show Sidebar</label>
                  <div>
                    <input type="checkbox" id="sidebarVisibility" checked>
                    <label for="sidebarVisibility">Enable Sidebar</label>
                  </div>
                </div>
  
                <!-- Header Layout -->
                <div class="form-group">
                  <label for="headerLayout">Header Layout</label>
                  <select class="form-control" id="headerLayout">
                    <option value="default">Default Header</option>
                    <option value="compact">Compact Header</option>
                    <option value="full-width">Full Width Header</option>
                  </select>
                </div>
  
                <!-- Save and Apply Layout -->
                <div class="form-group text-right">
                  <button type="button" class="btn btn-secondary" onclick="resetLayout()">Reset</button>
                  <button type="submit" class="btn btn-primary">Save and Apply</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Preview Section -->
      <div class="row mt-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-title">Preview</div>
            </div>
            <div class="card-body">
              <div id="layoutPreview" class="border p-3">
                <h4>Layout Preview</h4>
                <p>Select the layout options to see the changes.</p>
                <!-- Placeholder for layout preview -->
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