<?php
// Set the page title dynamically
$pageTitle = "O - Instructions Add/Edit"; 

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
            <a href="#">Add/Edit Instruction</a>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>
        </ul>
      </div>
      
      <!-- Form for Adding/Editing Instructions -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-title" id="formTitle">Add New Instruction</div>
            </div>
            <div class="card-body">
              <form id="instructionForm" onsubmit="saveInstruction(event)">
                <input type="hidden" id="instructionId">
                <div class="form-group">
                  <label for="description">Description</label>
                  <textarea class="form-control" id="description" rows="3" placeholder="Enter the description" required></textarea>
                </div>
                <div class="form-group">
                  <label for="image">Upload Image</label>
                  <input type="file" class="form-control-file" id="image" accept="image/*" required>
                </div>
                <div class="form-group text-right">
                  <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save</button>
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
include('../asset_for_pages/footer.php');
?>