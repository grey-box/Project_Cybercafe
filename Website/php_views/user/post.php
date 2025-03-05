<?php
// Set the page title dynamically
$pageTitle = "Broadcast Message - CyberCafe"; 

// Include the header
include('../asset_for_pages/header.php');
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
                    <form method="POST" action="">

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
include('../asset_for_pages/footer.php');
?>
