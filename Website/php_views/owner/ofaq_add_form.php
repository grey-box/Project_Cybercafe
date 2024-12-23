<?php
// Set the page title dynamically
$pageTitle = "O - FAQ Add"; 

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
          <a href="#">Owner/Admin</a>
        </li>
        <li class="separator">
          <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
          <a href="#">Add/Edit Q&A</a>
        </li>
        <li class="separator">
          <i class="icon-arrow-right"></i>
        </li>
      </ul>
    </div>
    
    <!-- Form for Adding/Editing Q&A -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="card-title" id="formTitle">Add New / Edit Q&A</div>
          </div>
          <div class="card-body">
            <form id="qaForm" onsubmit="saveFaq(event)">
              <input type="hidden" id="faqId">
              <div class="form-group">
                <label for="question">Question</label>
                <input type="text" class="form-control" id="question" placeholder="Enter the question" required>
              </div>
              <div class="form-group">
                <label for="answer">Answer</label>
                <textarea class="form-control" id="answer" rows="3" placeholder="Enter the answer" required></textarea>
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