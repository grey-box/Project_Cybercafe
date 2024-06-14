<?php
/*
Notes:
- Will need the daemon to get the mac address to start the session.
- At the moment the start session does not try to validate that the mac address is not blocked.
- Admin status is just a COOKIE value.
- At the moment the functions just return true or false (might need to change this).
- There is no parameter validation.
- There are not good error-throwing practices implemented.
- Should probably implement the testing using php unit tests rather than the current set up.
- Should make sure that the group name is unique for website blocking groups.
*/


$db_path = './CyberCafeTest.db';

// Start the session 
// Tested
function startSession($session_code, $mac_address) {
	global $db_path;
	$cc_db = new SQLite3($db_path);

	$cc_db->exec('BEGIN TRANSACTION');

	try {
		// Get details from session_types
		$session_type_query = $cc_db->prepare("
			SELECT * FROM session_types
			WHERE session_code = :session_code");
		$session_type_query->bindValue(":session_code",$session_code);
		$session_type = $session_type_query->execute()->fetchArray(SQLITE3_ASSOC);

		if (empty($session_type)) {
			throw new Exception("Session code not found: $session_code \n");
		}

		// Insert a session with session_start...
		// 		from session_types: group_id and bytes_remaining
		$start_session_query = $cc_db->prepare("
			INSERT INTO session_details
				(session_start, group_id, mac_address, bytes_remaining)
			VALUES 
				(DATETIME('now'), :group_id, :mac_address, :bytes_remaining)");

		$start_session_query->bindValue(":group_id",$session_type['group_id']);
		$start_session_query->bindValue(':mac_address',$mac_address);
		$start_session_query->bindValue(':bytes_remaining',$session_type['bytes_limit']);

		$start_session_query->execute();

		// Set the session_id cookie
		$get_session_id_query = $cc_db->prepare('
			SELECT MAX(session_id) as session_id FROM session_details');

		$session_id = $get_session_id_query->execute()->fetchArray(SQLITE3_ASSOC)['session_id'];
		$_COOKIE['session_id'] = $session_id;

		$cc_db->exec("COMMIT");
	} catch (Exception $e) {
		$cc_db->exec("ROLLBACK");
		echo "Error: " . $e->getMessage();
	}
}

// Updates session limit and returns wether the session can keep going
// Tested
function updateSession($bytes) {
	global $db_path;
    $session_id = $_COOKIE['session_id'];
    $cc_db = new SQLite3($db_path);
    
    // Begin a transaction
    $cc_db->exec('BEGIN TRANSACTION');

    try {
        $update_balance_query = $cc_db->prepare("
            UPDATE session_details
            SET bytes_remaining = bytes_remaining - :bytes
            WHERE session_id = :session_id");
        
        $update_balance_query->bindValue(":bytes", $bytes);
        $update_balance_query->bindValue(":session_id", $session_id);

        $update_balance_query->execute();

		$check_balance_query = $cc_db->prepare("
			SELECT bytes_remaining
			FROM session_details
			WHERE session_id = :session_id");

		$check_balance_query->bindValue(":session_id", $session_id);

		$bytes_remaining = $check_balance_query->execute()->fetchArray(SQLITE3_ASSOC)['bytes_remaining'];

        // Commit the transaction if all queries succeed
        $cc_db->exec('COMMIT');

		if ($bytes_remaining > 0) {
			return true;
		}
		
		return false;
    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        $cc_db->exec('ROLLBACK');
        
        return false; // Update failed
    } finally {
        // Close the database connection
        $cc_db->close();
    }
}

// Tested
function endSession() {
	global $db_path;
    $session_id = $_COOKIE['session_id'];
    $cc_db = new SQLite3($db_path);
    
    try {
		$end_session_query = $cc_db->prepare("
		UPDATE session_details
		SET session_end = datetime('now')
		WHERE session_id = :session_id");

		$end_session_query->bindValue(":session_id", $session_id);

		$end_session_query->execute();

		return true;

	} catch (Exception $e) {
		return false; // Update failed.
	} finally {
		$cc_db->close();
	}
}

// Check if the session can visit the current site (not in the blocklist for group id.)
// Tested 
function canSessionVisitSite($url) {
	global $db_path;
	$cc_db = new SQLite3($db_path);

	try {
		$is_blocked_query = $cc_db->prepare("
		SELECT s.group_id, wbu.website_url FROM 
		session_details s 
		JOIN website_blocking_groups_url wbu ON s.group_id = wbu.group_id
		WHERE session_id = :session_id
		AND wbu.website_url = :url");

		$is_blocked_query->bindValue("session_id", $_COOKIE["session_id"]);
		$is_blocked_query->bindValue("url", $url);

		$is_blocked = $is_blocked_query->execute()->fetchArray(SQLITE3_ASSOC);

		if ($is_blocked) {
			return false;
		} else {
			return true;
		}

	} catch (Exception $e) {
		return false;
	} finally {
		$cc_db->close();
	}
}

// Tested
function createWebsiteBlockingGroup($group_name, $group_id) {
	global $db_path;
	$cc_db = new SQLite3($db_path);

	try {
		$create_group_query = $cc_db->prepare("
			INSERT INTO website_blocking_groups
				(group_id, group_name)
			VALUES 
				(:group_id, :group_name)");
		$create_group_query->bindValue(":group_name", $group_name);
		$create_group_query->bindValue(":group_id", $group_id);

		$create_group_query->execute();

	} catch (Exception $e) {
		return "Error: " . $e->getMessage();
	} finally {
		$cc_db->close();
	}
}

// Tested
function deleteWebsiteBlockingGroup($group_id) {
	global $db_path;
	$cc_db = new SQLite3($db_path);

	try {
		$delete_group_query = $cc_db->prepare("
			DELETE FROM website_blocking_groups
			WHERE group_id = :group_id");
		$delete_group_query->bindValue(":group_id", $group_id);
		$result = $delete_group_query->execute();

		if ($result === false) {
			throw new Exception("Failed to delete group.");
		} else {
			$affected_rows = $cc_db->changes();
			if ($affected_rows > 0) {
				return "Group deleted successfully.";
			} else {
				return "Group not found.";
			}
		}

	} catch (Exception $e) {
		return "Error: " . $e->getMessage();
	} finally {
		$cc_db->close();
	}
}


?>
