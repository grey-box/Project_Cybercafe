<?php
// Set the page title dynamically
$pageTitle = "O - FAQ"; 

// Include the header
include('../asset_for_pages/owner_header.php');

// Sample data for the FAQ (in a real application, this data would come from a database)
$faqData = [
    [ 'question' => 'What is the return policy?', 'answer' => 'Our return policy allows returns within 30 days of purchase.'],
    [ 'question' => 'How can I reset my password?', 'answer' => 'You can reset your password by clicking on the "Forgot Password" link.']
];
?>

<!-- Start of the Container -->
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
        <button class="btn btn-primary" onclick="window.location.href='ofaq_add_form.php'">Add New</button> <!-- Updated to PHP -->
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped" id="qaTable">
            <thead>
              <tr>
                <th>Question</th>
                <th>Answer</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($faqData as $faq) : ?>
                <tr>
                  <td><?php echo $faq['question']; ?></td>
                  <td><?php echo $faq['answer']; ?></td>
                    <style>
                          .btn-action {
                          margin-right: 8px;
                          margin-bottom: 5px; /* To avoid overlap in stacked view */
                          }
                      </style>

                      <td>
                        <button class="btn btn-warning btn-sm btn-action" onclick="window.location.href='ofaq_add_form.php'">Edit</button>
                        <button class="btn btn-danger btn-sm btn-action" onclick="removeRow(this)">Delete</button>
                      </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>



<script>
// Function to remove a row from the table with confirmation
function removeRow(button) {
    // Show a confirmation prompt
    const confirmation = confirm("This change connot be recovered, do you really want to delete this??");
    if (confirmation) {
        // If confirmed, remove the row
        const row = button.closest('tr');
        row.parentNode.removeChild(row);
    }
    // If canceled, do nothing
}

// Function to edit FAQ (redirect to the edit page with the FAQ ID)
// function editFaq() {
//     window.location.href = 'ofaq_add_form.php';
// }
// 
</script>


<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>
