<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['owner']);
// Set the page title dynamically
$pageTitle = "Broadcast Message - CyberCafe"; 

// Include the header
include $_SERVER['DOCUMENT_ROOT'] . '/Website/php_views/asset_for_pages/owner_header.php';
?>

<div class="container">
    <h1>Broadcast Message</h1>
    
    <!-- Form for posting message -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Post a Message</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="broadcast">

                        <!-- Title Input Field -->
                        <div class="form-group row">
                            <label for="title" class="col-sm-2 col-form-label">Title:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="title" name="title" placeholder="Enter message title" required>
                            </div>
                        </div>

                        <!-- Message Textarea -->
                        <div class="form-group row">
                            <label for="message" class="col-sm-2 col-form-label">Message:</label>
                            <div class="col-sm-4">
                                <textarea class="form-control" id="message" name="message" rows="4" maxlength="250" placeholder="Enter your message (max 50 words)" required></textarea>
                                <small class="form-text text-muted">Max 50 words</small>
                            </div>
                        </div>

                        <!-- Post Button -->
                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="submit" class="btn btn-primary">Post</button>
                            </div>
                        </div>
                    </form>
                </div>
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
    $("#broadcast").on("submit", function (event) {
        event.preventDefault();
        
        showNotification("Message Broadcasted successfully!", "success");
        $("#broadcast")[0].reset();
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