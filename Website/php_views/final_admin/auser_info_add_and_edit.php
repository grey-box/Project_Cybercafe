<?php
// Set the page title dynamically
$pageTitle = "A - Edit User"; 
$randomUsername = "John Doe";
$randomEmail = "John.doe@flemingcollege.ca";

// Include the header
include('../asset_for_pages/admin_header.php');

// Generate random data for demonstration purposes
$randomAccessCode = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10));

// Sample device data (you can fetch this from a database)
$devices = [
    ['device_name' => 'Fancy Phone', 'data_used' => '40', 'data_allocated' => '100'],
    ['device_name' => 'Work Laptop', 'data_used' => '60', 'data_allocated' => '100']
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
            <a href="auser_table.php">User</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Edit User Information</a>
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
                <div class="card-title">Edit User Information</div>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <!-- Username Input -->
                    <div class="form-group row">
                        <label for="username" class="col-sm-2 col-form-label">Username:</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo $randomUsername; ?>">
                        </div>
                    </div>

                    <!-- Full Name Input -->
                    <div class="form-group row">
                        <label for="fullname" class="col-sm-2 col-form-label">Full Name:</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="fullname" name="fullname" value="John Doe">
                        </div>
                    </div>

                    <!-- Phone Number Input -->
                    <div class="form-group row">
                        <label for="phone" class="col-sm-2 col-form-label">Phone Number:</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="phone" name="phone" value="+1-123-456-7890">
                        </div>
                    </div>

                    <!-- Password Input with Generate Button -->
                    <div class="form-group row">
                        <label for="password" class="col-sm-2 col-form-label">Password:</label>
                        <div class="col-sm-4 d-flex">
                            <input type="text" class="form-control me-2" id="password" name="password" readonly>
                            <button type="button" class="btn btn-secondary" onclick="generatePassword()">Generate</button>
                        </div>
                    </div>

                    <!-- Email Input -->
                    <div class="form-group row">
                        <label for="email" class="col-sm-2 col-form-label">Email:</label>
                        <div class="col-sm-4">
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $randomEmail; ?>">
                        </div>
                    </div>

                    <!-- Bandwidth Allocated Field with Dropdown -->
                    <div class="form-group row">
    <label for="bandwidth" class="col-sm-2 col-form-label">Bandwidth Allocated:</label>
    <div class="col-sm-4 d-flex">
        <input type="text" class="form-control me-2" id="bandwidth" name="bandwidth" value="40 / 100 GB Used" readonly>
        <select class="form-control me-2" id="bandwidthOptions" style="width: 140px;">
            <option>20 GB</option>
            <option>40 GB</option>
            <option>50 GB</option>
            <option selected>100 GB</option>
            <option>Custom</option>
        </select>
        <button type="button" class="btn btn-primary" onclick="confirmRenewal()">Renew</button>
    </div>
</div>

<!-- Popup Modal for Confirmation -->
<div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renewModalLabel">Confirm Bandwidth Renewal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to renew the bandwidth allocation for this user?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="renewBandwidth()">Confirm</button>
            </div>
        </div>
    </div>
</div>


                    <!-- Data Transfer Rate Select -->
                    <div class="form-group row">
                        <label for="transferRate" class="col-sm-2 col-form-label">Data Transfer Rate:</label>
                        <div class="col-sm-4">
                            <select class="form-control" id="transferRate" name="transferRate">
                                <option selected>Slow Lane</option>
                                <option>Fast Lane</option>
                            </select>
                        </div>
                    </div>

                    <!-- Save and Cancel Buttons -->
                    <div class="form-group row">
                        <div class="col-sm-12 text-right">
                            <button type="button" class="btn btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
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
                <div class="card-title">Devices in Use</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Bandwidth (Used / Allocated)</th>
                                <th>Remove</th>
                                <th>Suspend</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($devices as $device) : ?>
                                <tr>
                                    <td><input type="text" class="form-control" value="<?php echo $device['device_name']; ?>"></td>
                                    <td><?php echo $device['data_used'] . ' / ' . $device['data_allocated'] . ' GB Used'; ?></td>
                                    <td><button class="btn btn-danger btn-sm" onclick="removeDevice(this)">Remove</button></td>
                                    <td><button class="btn btn-warning btn-sm">Suspend</button></td>
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
// JavaScript function to generate a random password
function generatePassword() {
    const words = ['apple', 'sky', 'river', 'horse', 'battery', 'ocean', 'forest'];
    const number = Math.floor(Math.random() * 1000);
    const password = words[Math.floor(Math.random() * words.length)] + words[Math.floor(Math.random() * words.length)] + number;
    document.getElementById("password").value = password;
}

// JavaScript function to generate a random access code
function generateAccessCode() {
    let code = '';
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for (let i = 0; i < 10; i++) {
        code += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    document.getElementById("accessCode").value = code;
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
// Function to show confirmation popup
function confirmRenewal() {
    var renewModal = new bootstrap.Modal(document.getElementById("renewModal"));
    renewModal.show();
}

// Function to process bandwidth renewal (Simulated action)
function renewBandwidth() {
    alert("Bandwidth has been successfully renewed.");
    var renewModal = bootstrap.Modal.getInstance(document.getElementById("renewModal"));
    renewModal.hide();
}
</script>
