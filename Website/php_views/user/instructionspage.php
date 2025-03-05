
<?php
// Set the page title dynamically
$pageTitle = "Detailed Instructions"; 

// Include the header
include('../asset_for_pages/user_header.php');
?>

<div class="container">
    <div class="page-inner">
        <div class="page-header">
            <h3 class="fw-bold mb-3">Instructions</h3>
            <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                    <a href="#">
                        <i class="icon-home"></i>
                    </a>
                </li>
                <li class="separator">
                    <i class="icon-chevron-right"></i>
                </li>
                <li class="nav-item">
                    <a href="#">Instructions</a>
                </li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Important Usage Guidelines</div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Below are detailed instructions for using the system effectively. Follow these steps to avoid any issues.</p>
                        <table class="table table-striped table-responsive">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Instruction</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Ensure that your device is connected to the network before accessing the system. Check your internet connection if necessary.</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Log in to your account with your username and password. If you have forgotten your credentials, use the 'Forgot Password' option to reset them.</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>On your dashboard, you will see a summary of your account. Review this information for accuracy.</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>If you face any issues, click on the Help and Support section. Here, you will find FAQs and guides to resolve common issues.</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Ensure you log out of your account after using the system, especially on public or shared devices, to protect your information.</td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>Contact customer support for assistance if you encounter an issue that is not addressed in the Help section. Use the provided email or phone number.</td>
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td>Regularly update your profile information to keep your account secure and up to date. Go to the Profile Management section to make changes.</td>
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td>Abide by the terms of service to avoid penalties or suspension of your account. Read the terms carefully when registering or updating your account.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>
