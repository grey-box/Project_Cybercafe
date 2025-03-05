<?php
// Set the page title dynamically
$pageTitle = "User Profile"; 

// Include the header
include('../asset_for_pages/header.php');
?>


<div class="container">
          <div class="page-inner">
            <div class="page-header">
              <h3 class="fw-bold mb-3">User Profile</h3>
              <ul class="breadcrumbs mb-3">
                <li class="nav-home">
                  <a href="#">
                    <i class="icon-home"></i>
                  </a>
                </li>
                <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li>
                <!-- <li class="nav-item">
                  <a href="#">User Tables</a>
                </li> -->
                <!-- <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li> -->
                <li class="nav-item">
                    <a href="#">User Info</a>
                </li>
                <li class="separator">
                    <i class="icon-arrow-right"></i>
                </li>
              </ul>
            </div>
            <div class="row"></div>
            </div>
                    </div>
                    <!-- User Information along with Data table Start -->
                    <title>User Profile</title>
                    <style>
                        /* Add some basic styling to make the page look decent */
                        body {
                            font-family: Arial, sans-serif;
                        }
                        .user-info {
                            width: 80%;
                            margin: 40px auto;
                            padding: 20px;
                            border: 1px solid #ddd;
                            border-radius: 10px;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        }
                        .user-image {
                            width: 100px;
                            height: 100px;
                            border-radius: 50%;
                            margin: 20px;
                        }
                        .user-data-table {
                            border-collapse: collapse;
                            width: 100%;
                        }
                        .user-data-table th, .user-data-table td {
                            border: 1px solid #ddd;
                            padding: 10px;
                            text-align: left;
                        }
                        .user-data-table th {
                            background-color: #f0f0f0;
                        }
                    </style>
                    <div class="table-responsive">
                        <div class="user-info">
                            <h2>User Information</h2>
                            <p>Name: Lionel Scaloni</p> 
                            <p>Access Code: 123456</p>
                            <!-- <textarea rows="5" cols="50" placeholder="Description"></textarea> -->
                            <table class="user-data-table">
                                <thead>
                                    <tr>
                                        <th>SN.</th>
                                        <th>Devices</th>
                                        <th>MAC Address</th>
                                        <th>Bandwidth Allocated</th>
                                        
                                        <!-- <th>Edit</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1.</td>
                                        <td>Desktop</td>
                                        <td>00:11:22:33:44:55</td>
                                        <td>1 GB</td>
                                        <!-- <td><a href="#" class="edit-btn">Edit</a></td> -->
                                    </tr>
                                    <tr>
                                        <td>2.</td>
                                        <td>Smartphone</td>
                                        <td>66:77:88:99:12:07</td>
                                        <td>50 MB</td>
                                        <!-- <td><a href="#" class="edit-btn">Edit</a></td> -->
                                    </tr>
                                    <tr>
                                      <td>3.</td>
                                      <td>Laptop</td>
                                      <td>00:11:22:33:55:77</td>
                                      <td>10 GB</td>
                                      <!-- <td><a href="#" class="edit-btn">Edit</a></td> -->
                                  </tr>
                                  <tr>
                                    <td>4.</td>
                                    <td>Tablet</td>
                                    <td>00:11:22:33:55:88</td>
                                    <td>100 MB</td>
                                    <!-- <td><a href="#" class="edit-btn">Edit</a></td> -->
                                </tr>
                                </tbody>
                            </table>
                        </div>
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
