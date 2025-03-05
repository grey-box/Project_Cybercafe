<?php
// Set the page title dynamically
$pageTitle = "User Profile"; 

// Include the header
include('../asset_for_pages/header.php');
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
                  <i class="icon-arrow-right"></i>
                </li>
                <!-- <li class="nav-item">
                  <a href="#">User Tables</a>
                </li>
                <li class="separator">
                  <i class="icon-arrow-right"></i>
                </li> -->
                <li class="nav-item">
                    <a href="#">Instructions</a>
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
                    <title>Instructions</title>
                    <style>
                        /* Add some basic styling to make the page look decent */
                        body {
                            font-family: 'Arial', sans-serif;
                            margin: 0;
                            padding: 0;
                            background-color: #f4f4f4;
                            color: #333;
                        }

                        .container {
                            max-width: 800px;
                            margin: 0 auto;
                            padding: 20px;
                            background: white;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                            border-radius: 8px;
                        }

                        .header {
                            text-align: center;
                            padding: 20px;
                            background: #007BFF;
                            color: white;
                            border-radius: 8px 8px 0 0;
                        }

                        .navigation {
                            margin: 20px 0;
                        }

                        .navigation ul {
                            list-style: none;
                            padding: 0;
                        }

                        .navigation li {
                            display: inline;
                            margin-right: 15px;
                        }

                        .navigation a {
                            text-decoration: none;
                            color: #007BFF;
                        }

                        .navigation a:hover {
                            text-decoration: underline;
                        }

                        .section {
                            margin: 20px 0;
                            padding: 15px;
                            border: 1px solid #ddd;
                            border-radius: 5px;
                            background-color: #f9f9f9;
                        }

                        .section h2 {
                            color: #007BFF;
                        }
                        .faq-categories {
                            display: flex;
                            justify-content: center;
                            flex-wrap: wrap;
                            margin: 20px 0;
                        }

                        .category-card {
                            background-color: white;
                            border: 1px solid #ccc;
                            border-radius: 10px;
                            padding: 20px;
                            margin: 10px;
                            width: 150px;
                            text-align: center;
                            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                            cursor: pointer;
                        }

                        .faq-section {
                            max-width: 800px;
                            margin: auto;
                            padding: 20px;
                        }

                        .faq-item {
                            background-color: white;
                            margin: 10px 0;
                            padding: 15px;
                            border-radius: 5px;
                            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                        }

                        .faq-question {
                            font-weight: bold;
                            cursor: pointer;
                        }

                        .faq-answer {
                            display: none; /* Initially hide answers */
                            padding-top: 10px;
                        }

                        .social-media a {
                            margin: 0 10
                        }    
                    </style>
                    <div class="container">
                        <header class="header">
                            <h1>Welcome to Our Service</h1>
                            <p>Your Guide to Navigating Our Features</p>
                        </header>
                
                        <nav class="navigation">
                            <ul>
                                <li><a href="#account">Creating an Account</a></li>
                                <li><a href="#login">Logging In</a></li>
                                <li><a href="#dashboard">Navigating the Dashboard</a></li>
                                <li><a href="#support">Contact Support</a></li>
                            </ul>
                        </nav>
                
                        <section id="account" class="section">
                            <h2>Creating an Account</h2>
                            <ol>
                                <!-- <li>Click on the "Sign Up" button located at the top right corner.</li> -->
                                <li>Fill in the required fields (Userame, Password or Passcode).</li>
                                <li>Click "Submit" to create your account.</li>
                                <li>Check your email for a confirmation link to activate your account.</li>
                            </ol>
                        </section>
                
                        <section id="login" class="section">
                            <h2>Logging In</h2>
                            <ol>
                                <li>Click on the "Login" button.</li>
                                <li>Enter your registered username and password or passcode.</li>
                                <li>Click "Login" to access your dashboard.</li>
                                <li>If you forget your password/passcode, click on "Forgot Password?" to request the administrator for resetting the password.</li>
                            </ol>
                        </section>
                
                        <section id="dashboard" class="section">
                            <h2>Navigating the Dashboard</h2>
                            <p>Overview of Features:</p>
                            <ul>
                                <li><strong>Quick Links:</strong> Access frequently used features directly from the dashboard.</li>
                                <li><strong>Notifications:</strong> Stay updated with alerts and messages.</li>
                                <li><strong>Search Bar:</strong> Use the search function to find specific resources quickly.</li>
                            </ul>
                        </section>

                            <h3>Need Help?</h3>
                            <p>Contact our support team via the <a href="#support">Support</a> section.</p>
                            <p>Email: <a href="mailto:support@greybox.com">support@yourportal.com</a></p>
                            <p>Phone: (123) 456-7890</p>
                        
                    </div>


<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>