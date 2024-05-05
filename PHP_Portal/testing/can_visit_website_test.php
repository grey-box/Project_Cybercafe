<?php
require '../cybercafe_manage.php';
require './execute_sql_file.php';

executeSQLFile('./reset_tables.sql', './CyberCafeTest.db');

$cc_db = new SQLite3('./CyberCafeTest.db');

startSession('S001', 'mac');

$url = "http://example.com"; // should not be able to visit this site.

$can_visit_site = canSessionVisitSite($url);

if ($can_visit_site) {
    echo "Session can visit blocked site (fail) \n";
} else {
    echo "Session cannot visit blocked site (success) \n";
}

$url = "http://secure.com";
$can_visit_site = canSessionVisitSite($url);

if ($can_visit_site) {
    echo "Session can visit non-blocked site (success) \n";
} else {
    echo "Session cannot visit non-blocked site (fail) \n";
}
?>