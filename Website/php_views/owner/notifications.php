<?php


// Include header file
include $_SERVER['DOCUMENT_ROOT'] . '/php_views/asset_for_pages/owner_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Notifications - Kaiadmin Bootstrap 5 Admin Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="<?php echo $base_url; ?>/assets/img/kaiadmin/favicon.ico" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src="<?php echo $base_url; ?>/assets/js/plugin/webfont/webfont.min.js"></script>
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
                urls: ["<?php echo $base_url; ?>/assets/css/fonts.min.css"],
            },
            active: function () {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/plugins.min.css" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/kaiadmin.min.css" />
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/demo.css" />
</head>
<body>
    <div class="wrapper">

            <div class="container">
                <div class="page-inner">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Bootstrap Notify</div>
                                    <div class="card-category">
                                        Turn standard bootstrap alerts into "growl" like notifications from
                                        <a class="link" href="http://bootstrap-notify.remabledesigns.com/">Bootstrap Notify</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form">
                                        <div class="form-group form-show-notify row">
                                            <div class="col-lg-3 col-md-3 col-sm-4 text-end">
                                                <label>Placement :</label>
                                            </div>
                                            <div class="col-lg-4 col-md-9 col-sm-8">
                                                <select class="form-select input-fixed" id="notify_placement_from">
                                                    <option value="top">Top</option>
                                                    <option value="bottom">Bottom</option>
                                                </select>
                                                <select class="form-select input-fixed" id="notify_placement_align">
                                                    <option value="left">Left</option>
                                                    <option value="right" selected="">Right</option>
                                                    <option value="center">Center</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group form-show-notify row">
                                            <div class="col-lg-3 col-md-3 col-sm-4 text-end">
                                                <label>State :</label>
                                            </div>
                                            <div class="col-lg-4 col-md-9 col-sm-8">
                                                <select class="form-select input-fixed" id="notify_state">
                                                    <option value="default">Default</option>
                                                    <option value="primary">Primary</option>
                                                    <option value="secondary">Secondary</option>
                                                    <option value="info">Info</option>
                                                    <option value="success">Success</option>
                                                    <option value="warning">Warning</option>
                                                    <option value="danger">Danger</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group form-show-notify row">
                                            <div class="col-lg-3 col-md-3 col-sm-4 text-end">
                                                <label>Style :</label>
                                            </div>
                                            <div class="col-lg-4 col-md-9 col-sm-8">
                                                <select class="form-select input-fixed" id="notify_style">
                                                    <option value="plain">Plain</option>
                                                    <option value="withicon">With Icon</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="form">
                                        <div class="form-group from-show-notify row">
                                            <div class="col-lg-3 col-md-3 col-sm-12"></div>
                                            <div class="col-lg-4 col-md-9 col-sm-12">
                                                <button id="displayNotif" class="btn btn-success">Display</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include('../asset_for_pages/footer.php'); ?>
        </div>

        <!-- Custom template | don't include it in your project! -->
        <?php include('../asset_for_pages/custom_template.php'); ?>
        <!-- End Custom template -->
    </div>

    <!-- Core JS Files -->
    <script src="<?php echo $base_url; ?>/assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/core/popper.min.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/core/bootstrap.min.js"></script>
    <!-- Bootstrap Notify -->
    <script src="<?php echo $base_url; ?>/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
    <!-- jQuery Scrollbar -->
    <script src="<?php echo $base_url; ?>/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <!-- Kaiadmin JS -->
    <script src="<?php echo $base_url; ?>/assets/js/kaiadmin.min.js"></script>
    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src="<?php echo $base_url; ?>/assets/js/setting-demo2.js"></script>
    <script>
        $("#displayNotif").on("click", function () {
            var placementFrom = $("#notify_placement_from option:selected").val();
            var placementAlign = $("#notify_placement_align option:selected").val();
            var state = $("#notify_state option:selected").val();
            var style = $("#notify_style option:selected").val();
            var content = {};

            content.message = 'Turning standard Bootstrap alerts into "notify" like notifications';
            content.title = "Bootstrap notify";
            if (style == "withicon") {
                content.icon = "fa fa-bell";
            } else {
                content.icon = "none";
            }
            content.url = "index.html";
            content.target = "_blank";

            $.notify(content, {
                type: state,
                placement: {
                    from: placementFrom,
                    align: placementAlign,
                },
                time: 1000,
                delay: 0,
            });
        });
    </script>
</body>
</html>


<?php
// Include the footer
include('../asset_for_pages/footer.php');
?>