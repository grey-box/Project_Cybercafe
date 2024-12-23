<?php
// Set the page title dynamically
$pageTitle = "A - Edit User"; 

// Include the header
include('../asset_for_pages/header.php');

// Generate random data for demonstration purposes
$randomUsername = 'user_' . rand(1000, 9999);
$randomEmail = 'user_' . rand(1000, 9999) . '@example.com';
$randomAccessCode = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10));

// Sample device data (you can fetch this from a database)
$devices = [
    ['device_name' => 'Device 1', 'data_used' => '2 GB'],
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
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo $randomUsername; ?>" readonly>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="form-group row">
                        <label for="password" class="col-sm-2 col-form-label">Password:</label>
                        <div class="col-sm-4">
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="form-group row">
                        <label for="confirmPassword" class="col-sm-2 col-form-label">Confirm Password:</label>
                        <div class="col-sm-4">
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        </div>
                    </div>

                    <!-- Email Input -->
                    <div class="form-group row">
                        <label for="email" class="col-sm-2 col-form-label">Email:</label>
                        <div class="col-sm-4">
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $randomEmail; ?>" readonly>
                        </div>
                    </div>

                    <!-- Access Code Field -->
                    <div class="form-group row">
                        <label for="accessCode" class="col-sm-2 col-form-label">Access Code:</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" id="accessCode" name="accessCode" value="<?php echo $randomAccessCode; ?>" readonly>
                        </div>
                        <div class="col-sm-1">
                            <button type="button" class="btn btn-secondary" onclick="generateAccessCode()">Generate</button>
                        </div>
                    </div>

                    <!-- Bandwidth Select -->
                    <div class="form-group row">
                        <label for="bandwidth" class="col-sm-2 col-form-label">Bandwidth Allocated [Mbps]:</label>
                        <div class="col-sm-4">
                            <select class="form-control" id="bandwidth" name="bandwidth">
                                <option>50</option>
                                <option selected>100</option>
                                <option>150</option>
                                <option>Custom</option>
                            </select>
                        </div>
                    </div>

                    <!-- Data Usage Select -->
                    <div class="form-group row">
                        <label for="dataUsage" class="col-sm-2 col-form-label">Data Cap:</label>
                        <div class="col-sm-4">
                            <select class="form-control" id="dataUsage" name="dataUsage">
                                <option>10 GB</option>
                                <option selected>20 GB</option>
                                <option>Custom</option>
                            </select>
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
                            <?php foreach ($devices as $device) : ?>
                                <tr>
                                    <td><?php echo $device['device_name']; ?></td>
                                    <td><?php echo $device['data_used']; ?></td>
                                    <td><button class="btn btn-danger btn-sm">Remove</button></td>
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
</script>
