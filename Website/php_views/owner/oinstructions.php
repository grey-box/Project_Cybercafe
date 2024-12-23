<?php
// Set the page title dynamically
$pageTitle = "O - Instructions"; 

// Include the header
include('../asset_for_pages/header.php');

?>



<!-- Start of the inner Container -->

 

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
          <a href="#">Owner/Admin</a>
        </li>
        <li class="separator">
          <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
          <a href="#">Manage Instructions</a>
        </li>
        <li class="separator">
          <i class="icon-arrow-right"></i>
        </li>
      </ul>
    </div>
    
    <!-- Instructions Table Section -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between">
            <div class="card-title">Manage Instructions</div>
            <button class="btn btn-primary" onclick="window.location.href='addEditInstruction.html'">Add New</button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped" id="instructionTable">
                <thead>
                  <tr>
                    <th>SN</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>1</td>
                    <td>Ensure proper handling of sensitive materials.</td>
                    <td><img src="sample1.jpg" alt="Instruction Image" style="width: 50px;"></td>
                    <td>
                      <button class="btn btn-warning btn-sm" onclick="editInstruction(1)">Edit</button>
                      <button class="btn btn-danger btn-sm" onclick="removeRow(this)">Delete</button>
                    </td>
                  </tr>
                  <tr>
                    <td>2</td>
                    <td>Follow safety guidelines at all times.</td>
                    <td><img src="sample2.jpg" alt="Instruction Image" style="width: 50px;"></td>
                    <td>
                      <button class="btn btn-warning btn-sm" onclick="editInstruction(2)">Edit</button>
                      <button class="btn btn-danger btn-sm" onclick="removeRow(this)">Delete</button>
                    </td>
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


<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>