<?php
// Set the page title dynamically
$pageTitle = "O - Restrictions Page"; 

// Include the header
include('../asset_for_pages/header.php');

?>

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
          <a href="#">Restrictions</a>
        </li>
        <li class="separator">
          <i class="icon-arrow-right"></i>
        </li>
      </ul>
    </div>
    
    <!-- Restriction Cards Section -->
    <div class="row">
      
      <!-- Card 1: URLs -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <div class="card-title">URL Restrictions</div>
          </div>
          <div class="card-body">
            <input type="text" class="form-control mb-3" placeholder="Add URL" onfocus="showSuggestions('urlSuggestions')" list="urlSuggestions">
            <datalist id="urlSuggestions">
              <option value="https://example.com">
              <option value="https://anotherurl.com">
              <option value="https://website.com">
            </datalist>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>SN</th>
                    <th>Item List</th>
                    <th>Edit</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>1</td>
                    <td>https://example.com</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                  </tr>
                  <tr>
                    <td>2</td>
                    <td>https://anotherurl.com</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Card 2: Usernames -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <div class="card-title">Username Restrictions</div>
          </div>
          <div class="card-body">
            <input type="text" class="form-control mb-3" placeholder="Add Username" onfocus="showSuggestions('usernameSuggestions')" list="usernameSuggestions">
            <datalist id="usernameSuggestions">
              <option value="JohnDoe">
              <option value="JaneSmith">
              <option value="MichaelLee">
            </datalist>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>SN</th>
                    <th>Item List</th>
                    <th>Edit</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>1</td>
                    <td>JohnDoe</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                  </tr>
                  <tr>
                    <td>2</td>
                    <td>JaneSmith</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Card 3: Devices -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <div class="card-title">Device Restrictions</div>
          </div>
          <div class="card-body">
            <input type="text" class="form-control mb-3" placeholder="Add Device" onfocus="showSuggestions('deviceSuggestions')" list="deviceSuggestions">
            <datalist id="deviceSuggestions">
              <option value="Desktop">
              <option value="Laptop">
              <option value="Smartphone">
            </datalist>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>SN</th>
                    <th>Item List</th>
                    <th>Edit</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>1</td>
                    <td>Desktop</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                  </tr>
                  <tr>
                    <td>2</td>
                    <td>Smartphone</td>
                    <td><button class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- JavaScript for Removing Table Rows -->
<script>
  function removeRow(button) {
    const row = button.parentElement.parentElement;
    row.remove();
  }
</script>



<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>