<?php
// Lightweight router for the Website folder.
// If a guest homepage exists, include it. Otherwise try guest login.
// This avoids requiring the user to restructure the project to have a top-level index.

$guest_home = __DIR__ . '/php_views/guest_user/guest_homepage.php';
$guest_login = __DIR__ . '/php_views/guest_user/guest_login.php';

if (file_exists($guest_home)) {
    require $guest_home;
    exit;
}

if (file_exists($guest_login)) {
    require $guest_login;
    exit;
}

// Fallback: list available top-level php_views directories
http_response_code(404);
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Website — No entry page</title>
</head>
<body>
  <h1>No entry page found</h1>
  <p>I couldn't find a guest homepage or login page to include. Available folders under <code>php_views</code>:</p>
  <pre><?php
    $dirs = array_filter(scandir(__DIR__ . '/php_views'), function($d){ return $d !== '.' && $d !== '..'; });
    echo implode("\n", $dirs);
  ?></pre>
</body>
</html>
