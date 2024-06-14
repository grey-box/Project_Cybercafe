<?php
require '../cybercafe_manage.php';
require './execute_sql_file.php';

executeSQLFile('./reset_tables.sql', './CyberCafeTest.db');

$cc_db = new SQLite3('./CyberCafeTest.db');

// Make sure that the method correctly sets the end date.
// Get the smallest session_id (entry we use for testing)
// Put this into function.
$session_id_query = $cc_db->prepare("
    SELECT session_id FROM session_details
    ORDER BY session_start ASC LIMIT 1");

$session_id = $session_id_query->execute()->fetchArray(SQLITE3_ASSOC)['session_id'];

$_COOKIE['session_id'] = $session_id;

endSession();

$check_session_end = $cc_db->prepare("
    SELECT session_end
    FROM session_details
    WHERE session_id = :session_id");
$check_session_end->bindValue(":session_id", $session_id);

$session_end = $check_session_end->execute()->fetchArray(SQLITE3_ASSOC)['session_end'];

$cc_db->close();

echo $session_end;

?>