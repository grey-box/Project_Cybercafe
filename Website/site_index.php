<?php
// Site navigator for the Website folder
// Usage: run the PHP dev server with project root as DOCUMENT_ROOT and open /Website/site_index.php

declare(strict_types=1);

$root = __DIR__;
$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : '';

function gatherPhpFiles(string $dir): array {
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $f) {
        if ($f->isFile() && strtolower($f->getExtension()) === 'php') {
            $files[] = $f->getPathname();
        }
    }
    sort($files, SORT_STRING);
    return $files;
}

$files = [];
// include top-level pages and php_views
if (is_dir($root . '/php_views')) {
    $files = gatherPhpFiles($root . '/php_views');
}

// also include top-level php files in Website (like this file and potential pages)
foreach (glob($root . '/*.php') as $p) {
    if (!in_array($p, $files, true)) $files[] = $p;
}

// helper to build URL from absolute path
function urlForPath(string $absPath, string $docRoot, string $websiteRoot): string {
    if ($docRoot !== '' && strpos($absPath, $docRoot) === 0) {
        $url = substr($absPath, strlen($docRoot));
    } else {
        // fallback: make path relative to website root
        $url = '/' . trim(str_replace($websiteRoot, '', $absPath), '/');
    }
    if ($url === '') $url = '/';
    return $url;
}

?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Website — Site index</title>
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial;margin:24px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:8px;border-bottom:1px solid #eee;text-align:left}
    a{color:#0366d6}
    .small{color:#666;font-size:0.9em}
  </style>
</head>
<body>
  <h1>Website — Navigator</h1>
  <p class="small">Links below open pages as served from your PHP dev server. Start the server from project root and open <code>/Website/site_index.php</code>.</p>

  <table>
    <thead>
      <tr><th>Path</th><th>Type</th></tr>
    </thead>
    <tbody>
<?php
foreach ($files as $f) {
    // skip paths we don't want to expose
    if (strpos($f, '/.git/') !== false) continue;
    // create URL
    $href = urlForPath($f, $docRoot, $root);
    // Prefer linking via Website prefix if the url doesn't already contain it
    if (strpos($href, '/Website') !== 0 && strpos($href, '/') === 0) {
        // ensure user-friendly link: prefix /Website if file under website root
        $href = '/Website' . $href;
    }
    $type = strpos($f, $root . '/php_views') === 0 ? 'view' : 'page';
    echo '<tr><td><a href="' . htmlspecialchars($href, ENT_QUOTES) . '">' . htmlspecialchars($href) . '</a></td><td>' . $type . '</td></tr>\n';
}
?>
    </tbody>
  </table>

  <p class="small">Notes: some pages require database or authentication to display properly. If a page fails to load with include/DB errors, make sure you started the dev server with project root as DOCUMENT_ROOT (see README in this message).</p>
</body>
</html>
