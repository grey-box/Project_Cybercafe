<?php
require '../cybercafe_manage.php';
require './execute_sql_file.php';

executeSQLFile('./reset_tables.sql', './CyberCafeTest.db');

$cc_db = new SQLite3('./CyberCafeTest.db');

// Successfully delete a group.
deleteWebsiteBlockingGroup('G1');
// Check website_blocking_group entries using sqlite3.
?>