<?php


require '../cybercafe_manage.php';
require './execute_sql_file.php';

// Reset the test database.
executeSQLFile('./reset_tables.sql', './CyberCafeTest.db');

// Make sure that the update_session method reduces bytes remainaing by the right amount
$cc_db = new SQLite3('./CyberCafeTest.db');

// Get the smallest session_id (entry we use for testing)
$session_id_query = $cc_db->prepare("
    SELECT session_id FROM session_details
    ORDER BY session_start ASC LIMIT 1");

$session_id = $session_id_query->execute()->fetchArray(SQLITE3_ASSOC)['session_id'];

$_COOKIE['session_id'] = $session_id; 

$check_current_balance_query = $cc_db->prepare("
    SELECT bytes_remaining
    FROM session_details
    WHERE session_id = :session_id");
$check_current_balance_query->bindValue(":session_id", $session_id);

// Make sure that the session is updated and the result is true.
$original_bytes_remaining = $check_current_balance_query->execute()->fetchArray(SQLITE3_ASSOC)['bytes_remaining'];

$session_can_continue = update_session(1000);

$bytes_remaining = $check_current_balance_query->execute()->fetchArray(SQLITE3_ASSOC)['bytes_remaining'];

if ($bytes_remaining == $original_bytes_remaining - 1000) {
    if ($session_can_continue) {
        echo 'Success\n';    
    }
    else {
        echo "Method did not return true";
    }
}
else {
    echo "bytes after update: " . $bytes_remaining . "\n";
    echo "bytes before update: " . $original_bytes_remaining . "\n";
}

$cc_db->close();

?>