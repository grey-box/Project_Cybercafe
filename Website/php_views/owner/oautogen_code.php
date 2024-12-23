<?php
// Set the page title dynamically
$pageTitle = "O - Autogenerate Code"; 

// Include the header
include('../asset_for_pages/header.php');

?>

<div class="form-group row">
                <label for="accessCode" class="col-sm-2 col-form-label">Access Code:</label>
                <div class="col-sm-3">
                  <input type="text" class="form-control" id="accessCode" readonly>
                </div>
                <div class="col-sm-1">
                  <button type="button" class="btn btn-primary" onclick="generateCode()">Generate</button>
                </div>
              </div>

<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>