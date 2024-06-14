<?php
require '../cybercafe_manage.php';
require './execute_sql_file.php';

executeSQLFile('./reset_tables.sql', './CyberCafeTest.db');

$cc_db = new SQLite3('./CyberCafeTest.db');

// Successfully create a new group
createWebsiteBlockingGroup('G4', 'Unique Group');

// Try to create a new group with a code that already exists
createWebsiteBlockingGroup('Already Exists', 'G1');

// Check website_blocking_group entries using sqlite3.
?>