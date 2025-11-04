<?php
// Send proper 404 HTTP status code
http_response_code(404);

// Set the page title
$pageTitle = "Page Not Found - 404";


include $_SERVER['DOCUMENT_ROOT'] . '/php_views/asset_for_pages/owner_header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2 text-center">

            <!-- 404 Number -->
            <h1 class="display-1 fw-bold text-danger" style="font-size: 12rem;">
                404
            </h1>

           
            <h2 style="font-size: 3.5rem; font-weight: 700;">
                Oops! Order Not On Menu
            </h2>
            <img src="https://notion-emojis.s3-us-west-2.amazonaws.com/prod/svg-twitter/2615.svg" 
                 alt="Hot Coffee" 
                 style="width: 120px; height: 120px; margin-bottom: 1.5rem;"
                 class="img-fluid">

            <!-- Description -->
            <p class="lead" style="font-size: 1.75rem;">
                The page you're looking for is not on our menu or you're in the wrong cafe
            </p>

            <!-- Return Button -->
            <a href="odashboard.php" class="btn btn-primary btn-lg mt-4" 
               style="font-size: 1.5rem; padding: .75rem 2rem;">
                Return Home
            </a>

        </div>
    </div>
</div>

<?php
// Include the footer
include $_SERVER['DOCUMENT_ROOT'] . '/php_views/asset_for_pages/footer.php';
?>