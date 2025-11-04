<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['owner']);
// Set the page title dynamically
$pageTitle = "O - Add User"; 

// Include the header
include $_SERVER['DOCUMENT_ROOT'] . '/Website/php_views/asset_for_pages/owner_header.php';
?>

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
            <a href="#">User</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Add User</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Add User</div>
            </div>
            <div class="card-body">
                <form id="addUserForm">
                    <div class="form-group row">
                        <label for="fullName" class="col-sm-2 col-form-label">Full Name:</label>
                        <div class="col-sm-4">
                            <input class="form-control" id="fullName" name="fullName" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="phoneNumber" class="col-sm-2 col-form-label">Phone Number:</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="username" class="col-sm-2 col-form-label">Username:</label>
                        <div class="col-sm-4">
                            <input class="form-control" id="username" name="username" required>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="password" class="col-sm-2 col-form-label">Password:</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="password" name="password" readonly required>
                        </div>
                        <div class="col-sm-1">
                            <button type="button" class="btn btn-secondary" onclick="generatePassword()">Generate</button>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="email" class="col-sm-2 col-form-label">Email:</label>
                        <div class="col-sm-4">
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    
                    <div class="form-group row">
                        <label for="role" class="col-sm-2 col-form-label">Role:</label>
                        <div class="col-sm-4">
                            <select class="form-control" id="role" name="role">
                                <option>Admin</option>
                                <option>Regular User</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="bandwidthCap" class="col-sm-2 col-form-label">Bandwidth Cap (User Level):</label>
                        <div class="col-sm-4">
                            <select class="form-control" id="bandwidthCap" name="bandwidthCap">
                                <option value="50">50 GB</option>
                                <option value="100">100 GB</option>
                                <option value="150">150 GB</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <div class="col-sm-12 text-right">
                            <button type="button" class="btn btn-secondary">Cancel</button>
                            <button id="addUserBtn" type="submit" class="btn btn-primary">Add</button>
                        </div>
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
// Function to generate a random access code
function generateAccessCode() {
    var code = '';
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for (var i = 0; i < 10; i++) {
        code += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    document.getElementById("accessCode").value = code;
}

// Function to generate a random password
function generatePassword() {
    var words = ["Horse", "Battery", "Staple", "Cloud", "Secure", "Bridge", "River", "Table", "Laptop", "Coffee"];
    var password = words[Math.floor(Math.random() * words.length)] + 
                   words[Math.floor(Math.random() * words.length)] + 
                   words[Math.floor(Math.random() * words.length)] + 
                   Math.floor(Math.random() * 100);
    document.getElementById("password").value = password;
}

// Form validation and submission
$(document).ready(function () {
    $("#addUserForm").on("submit", function (event) {
        event.preventDefault();
        var email = $("#email").val();
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!emailPattern.test(email)) {
            showNotification("Invalid email format!", "danger");
            return;
        }
        
        showNotification("User added successfully!", "success");
        $("#addUserForm")[0].reset();
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
