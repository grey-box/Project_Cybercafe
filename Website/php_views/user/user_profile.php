<?php
// Set the page title dynamically
$pageTitle = "User Profile"; 

// Include the header
include('../asset_for_pages/user_header.php');

// Sample device data (you can fetch this from a database)
$devices = [
    ['device_name' => 'Device 2', 'data_used' => '3 GB'],
    ['device_name' => 'Device 3', 'data_used' => '1 GB']
];
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
            <a href="#">User Profile</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
    </ul>
</div>

<!-- User Form Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Profile</div>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <!-- Username Input -->
                    <div class="form-group row">
                        <label for="username" class="col-sm-2 col-form-label">Username:</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="username" name="username" value="Sudeep" readonly>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <!-- <div class="form-group row">
                        <label for="password" class="col-sm-2 col-form-label">Password:</label>
                        <div class="col-sm-4">
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div> -->

                    <!-- Confirm Password Input -->
                    <!-- <div class="form-group row">
                        <label for="confirmPassword" class="col-sm-2 col-form-label">Confirm Password:</label>
                        <div class="col-sm-4">
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        </div>
                    </div> -->

                   

                    <!-- Password Field -->

                    <div class="form-group row">
                        <label for="password" class="col-sm-2 col-form-label">Password:</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="password" name="password" readonly required>
                        </div>
                    </div>

                    <!-- Bandwidth Select -->

                    <div class="form-group row">
                        <label for="bandwidth" class="col-sm-2 col-form-label">Bandwidth Allocated:</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="bandwidth" name="bandwidth" value="150" readonly>
                        </div>
                    </div>

                    <!-- Data Transfer Rate Select -->

                    <div class="form-group row">
                        <label for="transferRate" class="col-sm-2 col-form-label">Data Transfer Rate:</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="transferRate" name="transferRate" value="Slow Lane" readonly>
                        </div>
                    </div>

                    <!-- Save and Cancel Buttons -->
                    <!-- <div class="form-group row">
                        <div class="col-sm-12 text-right">
                            <button type="button" class="btn btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div> -->
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Devices Table Section -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Assigned Devices</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Data Used</th>
                                <th>Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Device 1</td>
                                <td>2 GB</td>
                                <td>Main Device</td>
                            </tr>
                             <?php foreach ($devices as $device) : ?>
                                <tr>
                                    <td><?php echo $device['device_name']; ?></td>
                                    <td><?php echo $device['data_used']; ?></td>
                                    <td><button class="btn btn-danger btn-sm" onclick="removeDevice(this)">Remove</button></td>
                                </tr>
                            <?php endforeach; ?> 
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>

<script>
// JavaScript function to generate a random access code
function generateAccessCode() {
    var code = '';
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for (var i = 0; i < 10; i++) {
        code += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    document.getElementById("accessCode").value = code;
}

function generatePassword() {
    var words = ["Horse", "Battery", "Staple", "Cloud", "Secure", "Bridge", "River", "Table", "Laptop", "Coffee"];
    var password = words[Math.floor(Math.random() * words.length)] + 
                   words[Math.floor(Math.random() * words.length)] + 
                   words[Math.floor(Math.random() * words.length)] + 
                   Math.floor(Math.random() * 100);
    document.getElementById("password").value = password;
}

// JavaScript function to remove a device with confirmation
function removeDevice(button) {
    const confirmation = confirm("Do you really want to remove this device?");
    if (confirmation) {
        const row = button.closest('tr');
        row.parentNode.removeChild(row);
    }
}
</script>

<script>
// Form validation and submission
$(document).ready(function () {
    $("#delete").on("submit", function (event) {
        event.preventDefault();
        
        showNotification("Device deleted successfully!", "success");
        $("#delete")[0].reset();
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
