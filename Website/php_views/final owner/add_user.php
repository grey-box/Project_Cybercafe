<?php
// Set the page title dynamically
$pageTitle = "O - Add User"; 

// Include the header
include('../asset_for_pages/header.php');
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
            <li class="separator">
                <i class="icon-arrow-right"></i>
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
                    <form method="POST" action="">

                        <div class="form-group row">
                            <label for="username" class="col-sm-2 col-form-label">Username:</label>
                            <div class="col-sm-4">
                                <input class="form-control" id="username" name="username" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="username" class="col-sm-2 col-form-label">Password:</label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" id="username" name="username" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="username" class="col-sm-2 col-form-label">Confirm Password:</label>
                            <div class="col-sm-4">
                                <input type="password" class="form-control" id="username" name="username" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="email" class="col-sm-2 col-form-label">Email:</label>
                            <div class="col-sm-4">
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="accessCode" class="col-sm-2 col-form-label">Access Code:</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" id="accessCode" name="accessCode" readonly>
                            </div>
                            <div class="col-sm-1">
                                <button type="button" class="btn btn-secondary" onclick="generateAccessCode()">Generate</button>
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
                            <label for="bandwidth" class="col-sm-2 col-form-label">Bandwidth Allocated:</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="bandwidth" name="bandwidth">
                                    <option>50</option>
                                    <option>100</option>
                                    <option>150</option>
                                    <option>Custom</option>
                                </select>
                            </div>
                        </div>
<!-- 
                        <div class="form-group row">
                            <label for="dataUsage" class="col-sm-2 col-form-label">Data Cap:</label>
                            <div class="col-sm-4">
                                <select class="form-control" id="dataUsage" name="dataUsage">
                                    <option>10 GB</option>
                                    <option>20 GB</option>
                                    <option>Custom</option>
                                </select>
                            </div>
                        </div> -->

                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="button" class="btn btn-secondary">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </div>
                    </form>
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