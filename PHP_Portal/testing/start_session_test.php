<?php
require '../cybercafe_manage.php';
require './execute_sql_file.php';

executeSQLFile('./reset_tables.sql', './CyberCafeTest.db');

$cc_db = new SQLite3('./CyberCafeTest.db');

// Start a session with a valid code and make sure that it inserts properly and the cookie is set properly.
$session_code = 'S001';
$mac_address = 'mac';

startSession($session_code, $mac_address);

$confirm_start_query = $cc_db->prepare('
    SELECT * FROM session_details WHERE session_id = :session_id');

$confirm_start_query->bindValue(":session_id", $_COOKIE['session_id']);

$result = $confirm_start_query->execute()->fetchArray(SQLITE3_ASSOC);

echo 'session id: ' . $result['session_id'] . "\n" . 
    'bytes remaining: ' . $result['bytes_remaining'] . "\n" .
    'mac address: ' . $result['mac_address'] . "\n" .
    'session start: ' . $result['session_start'] ."\n" .
    'group id: ' . $result['group_id'] . "\n"; 

// Try to start a session with an invalid code and make sure it fails.
$session_code = 'S2';
startSession($session_code, $mac_address);
?>