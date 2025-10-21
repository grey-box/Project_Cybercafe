<?php
require_once dirname(__DIR__, 2) . '/config/paths.php';

$assetsBase = rtrim(WEB_BASE, '/') . '/assets';
?>
<!-- Core JS Files -->
<script src="<?php echo $assetsBase; ?>/js/core/jquery-3.7.1.min.js"></script>
<script src="<?php echo $assetsBase; ?>/js/core/popper.min.js"></script>
<script src="<?php echo $assetsBase; ?>/js/core/bootstrap.min.js"></script>


<!-- jQuery Scrollbar -->
<script src="<?php echo $assetsBase; ?>/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

<!-- Datatables -->
<script src="<?php echo $assetsBase; ?>/js/plugin/datatables/datatables.min.js"></script>

<!-- Kaiadmin JS -->
<script src="<?php echo $assetsBase; ?>/js/kaiadmin.min.js"></script>

<!-- Kaiadmin DEMO methods, don't include it in your project! -->
<script src="<?php echo $assetsBase; ?>/js/setting-demo2.js"></script>

<!-- Owl Carousel -->
<script src="<?php echo $assetsBase; ?>/js/plugin/owl-carousel/owl.carousel.min.js"></script>

<!-- Magnific Popup -->
<script src="<?php echo $assetsBase; ?>/js/plugin/magnific-popup/jquery.magnific-popup.min.js"></script>

<!-- Moment.js -->
<script src="<?php echo $assetsBase; ?>/js/plugin/moment/moment.min.js"></script>

<!-- Bootstrap Notify -->
<script src="<?php echo $assetsBase; ?>/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

<!-- SweetAlert -->
<script src="<?php echo $assetsBase; ?>/js/plugin/sweetalert/sweetalert.min.js"></script>

<!-- Select2 -->
<script src="<?php echo $assetsBase; ?>/js/plugin/select2/select2.full.min.js"></script>

<!-- Dropzone -->
<script src="<?php echo $assetsBase; ?>/js/plugin/dropzone/dropzone.min.js"></script>

<!-- FullCalendar -->
<script src="<?php echo $assetsBase; ?>/js/plugin/fullcalendar/fullcalendar.min.js"></script>

<!-- Gmaps -->
<script src="<?php echo $assetsBase; ?>/js/plugin/gmaps/gmaps.js"></script>

<!-- jQuery Sparkline -->
<script src="<?php echo $assetsBase; ?>/js/plugin/jquery-sparkline/jquery.sparkline.min.js"></script>

<!-- Sortable -->
<script src="<?php echo $assetsBase; ?>/js/plugin/sortable/sortable.min.js"></script>

<!-- Sticky Sidebar -->
<script src="<?php echo $assetsBase; ?>/js/plugin/sticky-sidebar/sticky-sidebar.min.js"></script>

<!-- Circles -->
<script src="<?php echo $assetsBase; ?>/js/plugin/circles/circles.min.js"></script>

<!-- Chart.js -->
<script src="<?php echo $assetsBase; ?>/js/plugin/chartjs/chart.min.js"></script>

<!-- JS VectorMap -->
<script src="<?php echo $assetsBase; ?>/js/plugin/jsvectormap/jsvectormap.min.js"></script>
<script src="<?php echo $assetsBase; ?>/js/plugin/jsvectormap/world.js"></script>

<script src="<?php echo $assetsBase; ?>/js/owner/feature_toggle.js"></script>
<script src="<?php echo $assetsBase; ?>/js/owner/faq_table.js"></script>
<script src="<?php echo $assetsBase; ?>/js/owner/faq_add_form.js"></script>
<script src="<?php echo $assetsBase; ?>/js/owner/instructions_add_edit_form.js"></script>
<script src="<?php echo $assetsBase; ?>/js/owner/instructions.js"></script>
<script src="<?php echo $assetsBase; ?>/js/owner/report.js"></script>
<script src="<?php echo $assetsBase; ?>/js/owner/user_table.js"></script>
<script src="<?php echo $assetsBase; ?>/js/owner/view_user.js"></script>
<script src="<?php echo $assetsBase; ?>/js/owner/ocharts_values.js"></script>
<script src="<?php echo $assetsBase; ?>/js/admin/choose_layout.js"></script>
<script src="<?php echo $assetsBase; ?>/js/admin/charts.js"></script>
<script src="<?php echo $assetsBase; ?>/js/admin/main_page_with_charts_and_tables.js"></script>
<script src="<?php echo $assetsBase; ?>/js/admin/table_samples.js"></script>
