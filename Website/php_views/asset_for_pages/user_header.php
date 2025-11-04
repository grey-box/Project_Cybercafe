<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/paths.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/Website/config/auth.php';
require_roles(['user']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title><?php echo isset($pageTitle) ? $pageTitle : 'Default Title'; ?></title>
  <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
  <link rel="icon" href="<?= WEB_BASE ?>/assets/greybox-logo.png" type="image/x-icon" />

  <!-- Fonts and icons -->
  <script src="<?= WEB_BASE ?>/assets/js/plugin/webfont/webfont.min.js"></script>
  <script>
    WebFont.load({
      google: { families: ["Public Sans:300,400,500,600,700"] },
      custom: {
        families: [
          "Font Awesome 5 Solid",
          "Font Awesome 5 Regular",
          "Font Awesome 5 Brands",
          "simple-line-icons",
        ],
        urls: ["<?= WEB_BASE ?>/assets/css/fonts.min.css"],
      },
      active: function () {
        sessionStorage.fonts = true;
      },
    });
  </script>

  <!-- CSS Files -->
  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/bootstrap.min.css" />
  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/plugins.min.css" />
  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/kaiadmin.min.css" />

  <!-- CSS Just for demo purpose, don't include it in your project -->
  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/demo.css" />
  <link rel="stylesheet" href="<?= WEB_BASE ?>/assets/css/owner/feature_toggle.css" />
</head>

<body>
  <div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar" data-background-color="dark">
      <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
          <a href="https://www.grey-box.ca/project-cybercafe/" class="logo">
            <img src="<?= WEB_BASE ?>/assets/greybox-logo.png" alt="navbar brand"
              class="navbar-brand" height="50" />
          </a>
          <div class="nav-toggle">
            <button class="btn btn-toggle toggle-sidebar">
              <i class="gg-menu-right"></i>
            </button>
            <button class="btn btn-toggle sidenav-toggler">
              <i class="gg-menu-left"></i>
            </button>
          </div>
          <button class="topbar-toggler more">
            <i class="gg-more-vertical-alt"></i>
          </button>
        </div>
        <!-- End Logo Header -->
      </div>
      <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
          <ul class="nav nav-secondary">
            <li class="nav-item">
              <a href="<?= WEB_BASE ?>/php_views/user/user_profile.php" class="collapsed" aria-expanded="false">
                <i class="fas fa-home"></i>
                <p>Home Page</p>
              </a>
            </li>
            <li class="nav-section">
              <span class="sidebar-mini-icon">
                <i class="fa fa-ellipsis-h"></i>
              </span>
            </li>

            <li class="nav-item">
              <a href="<?= WEB_BASE ?>/php_views/user/faqpage.php">
                <i class="fas fa-table"></i>
                <p>FAQs</p>
              </a>
            </li>

            <li class="nav-item">
              <a href="<?= WEB_BASE ?>/php_views/user/instructionspage.php">
                <i class="far fa-chart-bar"></i>
                <p>Instructions</p>
              </a>
            </li>

            <li class="nav-item">
                <a href="<?= WEB_BASE ?>/php_views/user/user_support.php">
                  <i class="far fa-question-circle"></i>
                  <p>Support</p>
                </a>
            </li>

            <!-- <li class="nav-item">
              <a href="">
                <i class="fas fa-desktop"></i>
                <p>Devices Connected</p>
                <span class="badge badge-success">4</span>
              </a>
            </li>

            <li class="nav-item">
              <a href="">
                <i class="fas fa-file"></i>
                <p>Any Documentation</p>
                <span class="badge badge-secondary">1</span>
              </a>
            </li>

            <li class="nav-item">
              <a data-bs-toggle="collapse" href="#submenu">
                <i class="fas fa-bars"></i>
                <p>Extra if needed</p>
                <span class="caret"></span>
              </a>
              <div class="collapse" id="submenu">
                <ul class="nav nav-collapse">
                  <li>
                    <a data-bs-toggle="collapse" href="#subnav1">
                      <span class="sub-item">Level 1</span>
                      <span class="caret"></span>
                    </a>
                    <div class="collapse" id="subnav1">
                      <ul class="nav nav-collapse subnav">
                        <li>
                          <a href="#">
                            <span class="sub-item">Level 2</span>
                          </a>
                        </li>
                        <li>
                          <a href="#">
                            <span class="sub-item">Level 2</span>
                          </a>
                        </li>
                      </ul>
                    </div>
                  </li>
                  <li>
                    <a data-bs-toggle="collapse" href="#subnav2">
                      <span class="sub-item">Level 1</span>
                      <span class="caret"></span>
                    </a>
                    <div class="collapse" id="subnav2">
                      <ul class="nav nav-collapse subnav">
                        <li>
                          <a href="#">
                            <span class="sub-item">Level 2</span>
                          </a>
                        </li>
                      </ul>
                    </div>
                  </li>
                  <li>
                    <a href="#">
                      <span class="sub-item">Level 1</span>
                    </a>
                  </li>
                </ul>
              </div>
            </li> -->

          </ul>
        </div>
      </div>
    </div>
    <!-- End Sidebar -->

    <div class="main-panel">
      <div class="main-header">
        <div class="main-header-logo">
          <!-- Logo Header -->
          <div class="logo-header" data-background-color="dark">
            <a href="https://www.grey-box.ca/project-cybercafe/" class="logo">
              <img src="<?= WEB_BASE ?>/assets/img/kaiadmin/logo_light.svg" alt="navbar brand" class="navbar-brand" height="20" />
            </a>
            <div class="nav-toggle">
              <button class="btn btn-toggle toggle-sidebar">
                <i class="gg-menu-right"></i>
              </button>
              <button class="btn btn-toggle sidenav-toggler">
                <i class="gg-menu-left"></i>
              </button>
            </div>
            <button class="topbar-toggler more">
              <i class="gg-more-vertical-alt"></i>
            </button>
          </div>
          <!-- End Logo Header -->
        </div>
        <!-- Navbar Header -->
        <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
          <div class="container-fluid">
            <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
              <form class="input-group w-auto" action="<?= WEB_BASE ?>/php_views/user/search_results.php" method="get">
                <div class="input-group-prepend">
                  <button type="submit" class="btn btn-search pe-1">
                    <i class="fa fa-search search-icon"></i>
                  </button>
                </div>
                <input
                  type="text"
                  name="q"
                  placeholder="Search payments, invoices, sessions…"
                  class="form-control"
                  value="<?= isset($_GET['q']) ? htmlspecialchars((string)$_GET['q']) : '' ?>"
                  aria-label="Search"
                />
              </form>
          </nav>


            <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
              <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                  aria-expanded="false" aria-haspopup="true">
                  <i class="fa fa-search"></i>
                </a>
                <ul class="dropdown-menu dropdown-search animated fadeIn">
                  <form class="navbar-left navbar-form nav-search" action="<?= WEB_BASE ?>/php_views/user/search_results.php" method="get">
                    <div class="input-group">
                      <input type="text" name="q" placeholder="Search ..." class="form-control" />
                    </div>
                  </form>
                </ul>
              </li>
              <li class="nav-item topbar-icon dropdown hidden-caret">
                <a class="nav-link dropdown-toggle" href="#" id="messageDropdown" role="button"
                  data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-envelope"></i>
                </a>
                <ul class="dropdown-menu messages-notif-box animated fadeIn" aria-labelledby="messageDropdown">
                  <li>
                    <div class="dropdown-title d-flex justify-content-between align-items-center">
                      Messages
                      <a href="#" class="small">Mark all as read</a>
                    </div>
                  </li>
                  <li>
                    <div class="message-notif-scroll scrollbar-outer">
                      <div class="notif-center">
                        <a href="#">
                          <div class="notif-img">
                            <img src="../../assets/img/jm_denis.jpg" alt="Img Profile" />
                          </div>
                          <div class="notif-content">
                            <span class="subject">Louis Rai</span>
                            <span class="block"> What's the update ? </span>
                            <span class="time">5 minutes ago</span>
                          </div>
                        </a>
                        <!-- <a href="#">
                          <div class="notif-img">
                            <img src="../../assets/img/chadengle.jpg" alt="Img Profile" />
                          </div>
                          <div class="notif-content">
                            <span class="subject">Chad</span>
                            <span class="block"> Ok, Thanks ! </span>
                            <span class="time">12 minutes ago</span>
                          </div>
                        </a>
                        <a href="#">
                          <div class="notif-img">
                            <img src="../../assets/img/mlane.jpg" alt="Img Profile" />
                          </div>
                          <div class="notif-content">
                            <span class="subject">Jhon Doe</span>
                            <span class="block">
                              Ready for the meeting today...
                            </span>
                            <span class="time">12 minutes ago</span>
                          </div>
                        </a>
                        <a href="#">
                          <div class="notif-img">
                            <img src="../../assets/img/talha.jpg" alt="Img Profile" />
                          </div>
                          <div class="notif-content">
                            <span class="subject">Talha</span>
                            <span class="block"> Hi, Apa Kabar ? </span>
                            <span class="time">17 minutes ago</span>
                          </div>
                        </a> -->
                      </div>
                    </div>
                  </li>
                  <li>
                    <a class="see-all" href="javascript:void(0);">See all messages<i class="fa fa-angle-right"></i>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item topbar-icon dropdown hidden-caret">
                <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-bell"></i>
                  <span class="notification">3</span>
                </a>
                <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
                  <li>
                    <div class="dropdown-title">
                      You have 3 new notification
                    </div>
                  </li>
                  <li>
                    <div class="notif-scroll scrollbar-outer">
                      <div class="notif-center">
                        <a href="<?= WEB_BASE ?>/php_views/user/user_notification.php">
                          <div class="notif-icon notif-primary">
                            <i class="fa fa-user-plus"></i>
                          </div>
                          <div class="notif-content">
                            <span class="block"> New user registered </span>
                            <span class="time">5 minutes ago</span>
                          </div>
                        </a>
                        <a href="<?= WEB_BASE ?>/php_views/user/user_notification.php">
                          <div class="notif-icon notif-success">
                            <i class="fa fa-comment"></i>
                          </div>
                          <div class="notif-content">
                            <span class="block">
                              Chinmay posted a broadcast message on User.
                            </span>
                            <span class="time">12 minutes ago</span>
                          </div>
                        </a>
                        <a href="<?= WEB_BASE ?>/php_views/user/user_notification.php">
                          <div class="notif-icon notif-success">
                            <i class="fa fa-comment"></i>
                          </div>
                          <div class="notif-content">
                            <span class="block">
                              Aniket posted a broadcast message on User.
                            </span>
                            <span class="time">12 minutes ago</span>
                          </div>
                        </a>
                        <!-- <a href="#">
                          <div class="notif-icon notif-danger">
                            <i class="fa fa-heart"></i>
                          </div>
                          <div class="notif-content">
                            <span class="block"> Farrah liked Admin </span>
                            <span class="time">17 minutes ago</span>
                          </div>
                        </a> -->
                      </div>
                    </div>
                  </li>
                  <li>
                    <a class="see-all" href="<?= WEB_BASE ?>/php_views/user/user_notification.php">See all notifications<i class="fa fa-angle-right"></i>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item topbar-icon dropdown hidden-caret">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                  <i class="fas fa-layer-group"></i>
                </a>
                <div class="dropdown-menu quick-actions animated fadeIn">
                  <div class="quick-actions-header">
                    <span class="title mb-1">Quick Actions</span>
                    <span class="subtitle op-7">Shortcuts</span>
                  </div>
                  <div class="quick-actions-scroll scrollbar-outer">
                    <div class="quick-actions-items">
                      <div class="row m-0">
                        <a class="col-6 col-md-4 p-0" href="<?= WEB_BASE ?>/php_views/user/user_calendar.php">
                          <div class="quick-actions-item">
                            <div class="avatar-item bg-danger rounded-circle">
                              <i class="far fa-calendar-alt"></i>
                            </div>
                            <span class="text">Calendar</span>
                          </div>
                        </a>
                        <a class="col-6 col-md-4 p-0" href="#">
                          <div class="quick-actions-item">
                            <div class="avatar-item bg-warning rounded-circle">
                              <i class="fas fa-map"></i>
                            </div>
                            <span class="text">Maps</span>
                          </div>
                        </a>
                        <a class="col-6 col-md-4 p-0" href="<?= WEB_BASE ?>/php_views/user/user_reports.php">
                          <div class="quick-actions-item">
                            <div class="avatar-item bg-info rounded-circle">
                              <i class="fas fa-file-excel"></i>
                            </div>
                            <span class="text">Reports</span>
                          </div>
                        </a>
                        <a class="col-6 col-md-4 p-0" href="#">
                          <div class="quick-actions-item">
                            <div class="avatar-item bg-success rounded-circle">
                              <i class="fas fa-envelope"></i>
                            </div>
                            <span class="text">Emails</span>
                          </div>
                        </a>
                        <a class="col-6 col-md-4 p-0" href="<?= WEB_BASE ?>/php_views/user/user_invoices.php">
                          <div class="quick-actions-item">
                            <div class="avatar-item bg-primary rounded-circle">
                              <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <span class="text">Invoice</span>
                          </div>
                        </a>
                        <a class="col-6 col-md-4 p-0" href="<?= WEB_BASE ?>/php_views/user/user_payments.php">
                          <div class="quick-actions-item">
                            <div class="avatar-item bg-secondary rounded-circle">
                              <i class="fas fa-credit-card"></i>
                            </div>
                            <span class="text">Payments</span>
                          </div>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </li>

              <li class="nav-item topbar-user dropdown hidden-caret">
                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                  <div class="avatar-sm">
                    <img src="<?= WEB_BASE ?>/assets/img/profile.jpg" alt="..." class="avatar-img rounded-circle" />
                  </div>
                  <span class="profile-username">
                    <span class="op-7">Hi,</span>
                    <span class="fw-bold">Aniket</span>
                  </span>
                </a>
                <ul class="dropdown-menu dropdown-user animated fadeIn">
                  <div class="dropdown-user-scroll scrollbar-outer">
                    <li>
                      <div class="user-box">
                        <div class="avatar-lg">
                          <img src="<?= WEB_BASE ?>/assets/img/profile.jpg" alt="image profile" class="avatar-img rounded" />
                        </div>
                        <div class="u-text">
                          <h4>Aniket</h4>
                          <p class="text-muted">Aniket@yahoo.com</p>
                          <a href="<?= WEB_BASE ?>/php_views/user/user_profile.php" class="btn btn-xs btn-secondary btn-sm">View Profile</a>
                        </div>
                      </div>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="<?= WEB_BASE ?>/php_views/user/user_profile.php">My Profile</a>
                      <!-- <a class="dropdown-item" href="#">My Balance</a> -->
                      <a class="dropdown-item" href="#">Inbox</a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="<?= WEB_BASE ?>/php_views/user/account_settings.php">Account Setting</a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="<?= WEB_BASE ?>/php_views/auth/logout.php">Logout</a>
                    </li>
                  </div>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
        <!-- End Navbar -->
      </div>

      <!-- Start of the Container -->

      <div class="container">
          <div class="page-inner">


<script>
  document.addEventListener("DOMContentLoaded", function () {
      let currentUrl = window.location.href; // Get the full current URL

      // Select all navigation links inside the sidebar
      let navLinks = document.querySelectorAll(".sidebar .nav-item a");

      navLinks.forEach(link => {
          if (currentUrl.includes(link.href)) { 
              link.classList.add("active"); // Add active class
              link.parentElement.classList.add("active"); // Optional: highlight parent li
          } else {
              link.classList.remove("active"); // Remove from others
              link.parentElement.classList.remove("active");
          }
      });
  });
</script>

<style>
  /* Active link styling */
  .nav-item a.active {
      font-weight: bold;
      color: #fff !important; /* White text */
      background-color: #007bff; /* Bootstrap primary color */
      border-left: 5px solid #0056b3; /* Left highlight */
  }

  .nav-item.active {
      background-color: #0056b3 !important; /* Highlight parent item */
  }
</style>
