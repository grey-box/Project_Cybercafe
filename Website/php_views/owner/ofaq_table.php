<?php
// Set the page title dynamically
$pageTitle = "O - FAQ"; 

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
                  <a href="#">Owner/Admin</a>
                </li>
                <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li>
                <li class="nav-item">
                  <a href="#">Manage Q&A</a>
                </li>
                <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li>
              </ul>
            </div>
            
            <!-- Q&A Table Section -->
            <div class="row">
              <div class="col-md-12">
                <div class="card">
                  <div class="card-header d-flex justify-content-between">
                    <div class="card-title">Manage Q&A</div>
                    <button class="btn btn-primary" onclick="window.location.href='addEditForm.html'">Add New</button>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-striped" id="qaTable">
                        <thead>
                          <tr>
                            <th>SN</th>
                            <th>Question</th>
                            <th>Answer</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>1</td>
                            <td>What is the return policy?</td>
                            <td>Our return policy allows returns within 30 days of purchase.</td>
                            <td>
                              <button class="btn btn-warning btn-sm" onclick="editFaq(1)">Edit</button>
                              <button class="btn btn-danger btn-sm" onclick="removeRow(this)">Delete</button>
                            </td>
                          </tr>
                          <tr>
                            <td>2</td>
                            <td>How can I reset my password?</td>
                            <td>You can reset your password by clicking on the 'Forgot Password' link.</td>
                            <td>
                              <button class="btn btn-warning btn-sm" onclick="editFaq(2)">Edit</button>
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