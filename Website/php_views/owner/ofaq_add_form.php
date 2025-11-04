<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['owner']);
// Set the page title dynamically
$pageTitle = "A - FAQ Add";



// Include the header
include $_SERVER['DOCUMENT_ROOT'] . '/Website/php_views/asset_for_pages/owner_header.php';

// Sample FAQ data for demo purposes (this would come from your database in a real application)
$faq = [
    'question' => 'What is the CyberCafe service?',
    'answer' => 'CyberCafe provides a network for users to access the internet through Wi-Fi in public spaces. Users must register to access the internet.'
];
?>

<!-- Start of the Container -->

    <!-- Move the page header to the left of the page-inner -->
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
            <form id="qaForm">
              <input type="hidden" id="faqId">
              
              <!-- Question Field -->
              <div class="form-group">
                <label for="question">Question</label>
                <input type="text" class="form-control" id="question" placeholder="Enter the question" value="<?php echo $faq['question']; ?>" required>
              </div>
              
              <!-- Answer Field -->
              <div class="form-group">
                <label for="answer">Answer</label>
                <textarea class="form-control" id="answer" rows="3" placeholder="Enter the answer" required><?php echo $faq['answer']; ?></textarea>
              </div>

              <!-- Save and Cancel Buttons -->
              <div class="form-group text-right">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

<?php
// Include the footer
include $_SERVER['DOCUMENT_ROOT'] .'/Website/php_views/asset_for_pages/footer.php';
?>


<script>
// Form validation and submission
$(document).ready(function () {
    $("#qaForm").on("submit", function (event) {
        event.preventDefault();
        
        showNotification("FAQ added successfully!", "success");
        $("#qaForm")[0].reset();
    });
});

// Function to show notification
function showNotification(message, type) {
    $.notify({
        title: "Notification",
        message: message,
        icon: "fa fa-bell"
    }, {
        type: type,
        placement: {
            from: "top",
            align: "center"
        },
        animate: {
            enter: "animated fadeInDown",
            exit: "animated fadeOutUp"
        },
        delay: 4000
    });
}
</script>

<script src="../assets/js/core/jquery-3.7.1.min.js"></script>
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
