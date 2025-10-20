<?php
declare(strict_types=1);

if (!defined('VIEWS_ROOT')) {
    define('VIEWS_ROOT', dirname(__DIR__) . '/php_views');  // /Website/php_views
}
if (!defined('SITE_FS_ROOT')) {
    define('SITE_FS_ROOT', dirname(VIEWS_ROOT));            // /Website
}
if (!defined('WEB_BASE')) {
    $webBase = str_replace($_SERVER['DOCUMENT_ROOT'], '', SITE_FS_ROOT);
    $webBase = $webBase === '' ? '/' : $webBase;
    define('WEB_BASE', rtrim($webBase, '/'));
}
?>
