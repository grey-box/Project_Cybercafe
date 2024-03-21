<?php
function prompt_for_validation() {
	require 'login_form_inc.html';
}

function check_for_validation() {
	if(isset($_COOKIE['user_id']) && isset($_COOKIE['wsess_id'])) {
		$cc_db = new SQLite3('../CyberCafe.db');
		$check_user_query = $cc_db->prepare("
			SELECT 1 AS wsess_exists FROM website_sessions
			WHERE	wsess_id = :value1 AND
				user_id = :value2 AND
				expr_datetime > datetime('now')");
		$check_user_query->bindValue(":value1", $_COOKIE['wsess_id']);
		$check_user_query->bindValue(":value2", $_COOKIE['user_id']);

		$check_user_result = $check_user_query->execute()->fetchArray();

		if($check_user_result['wsess_exists'] == 1) {
			return true;
		}
	}

	return false;
}

function check_session_allowed($sq_id = 1) {
	// What does a session consist of? Available bytes in a particular speed queue.
	// How does this DB tell us this? `balance_table`
	$cc_db = new SQLite3('../CyberCafe.db');
	$check_balance_query = $cc_db->prepare("
		SELECT bytes_remaining FROM balance_table WHERE
			user_id = :user_id AND sq_id = :sq_id");
	$check_balance_query->bindValue(":user_id", $_COOKIE['user_id']);
	$check_balance_query->bindValue(":sq_id", $sq_id);
	$check_balance_result = $check_balance_query->execute();

	if($check_balance_row = $check_balance_result->fetchArray()) {
		return $check_balance_row['bytes_remaining'];
	} else {
		return null;
	}

}
function get_user_status() {
	$cc_db = new SQLite3('../CyberCafe.db');
	$get_status_query = $cc_db->prepare("
		SELECT user_status FROM user_status WHERE user_id = :user_id");
	$get_status_query->bindValue(':user_id', $_COOKIE['user_id']);
	$get_status_result = $get_status_query->execute()->fetchArray();

	return $get_status_result['user_status'];
}

function add_internet_session($sq_id = 1) {
	$cc_db = new SQLite3('../CyberCafe.db');
	for($i = 0; $i < 3; $i++) {
		$isess_id = rand(100000, 9999999);
		$create_isession_query = $cc_db->prepare("
			INSERT OR IGNORE INTO internet_sessions
				(isess_id, user_id, sq_id, isess_datetime, isess_length)
			VALUES (
				:isess_id, :user_id, :sq_id, datetime('now'), '-1')");
		$create_isession_query->bindValue(':isess_id', $isess_id);
		$create_isession_query->bindValue(':user_id', $_COOKIE['user_id']);
		$create_isession_query->bindValue(':sq_id', $sq_id);
		$create_isession_query->execute();
		if($cc_db->changes() > 0) {
			// Success
			return $isess_id;
		}
	}

	return null;
}

function add_iptables_rules($comment_id, $mac_address) {
	$iptmon_rx_add_cmd = sprintf('iptables -t mangle -A iptmon_rx -d %s -m mac --mac-source %s -j RETURN -m comment --comment %s',
					$_SERVER['REMOTE_ADDR'], $mac_address, $comment_id);

	$iptmon_tx_add_cmd = sprintf('iptables -t mangle -A iptmon_tx -s %s -m mac --mac-source %s -j RETURN -m comment --comment %s',
					$_SERVER['REMOTE_ADDR'], $mac_address, $comment_id);

	shell_exec($iptmon_rx_add_cmd);
	shell_exec($iptmon_tx_add_cmd);

	$block_bypass_cmd = sprintf('iptables -t nat -I PREROUTING 2 -s %s -m mac --mac-source %s -j ACCEPT -m comment --comment %s', $_SERVER['REMOTE_ADDR'], $mac_address, $comment_id);

	shell_exec($block_bypass_cmd);
}

function fetch_ip_mac() {
	$ip_mac_cmd = sprintf("grep %s /proc/net/arp | awk '{print $4}'", $_SERVER['REMOTE_ADDR']);

	return rtrim(shell_exec($ip_mac_cmd));
}

function add_tc_filter($handle_id, $mac_address) {
	// sudo tc filter add dev <interface> parent <parent class> protocol ip u32 \
	// match ip dst 192.168.1.100 \
	// match u16 0x0800 0xffff at -4 \ (Matches IP protocol at an offest of -4 bytes from the end of the packet)
	// match u32 0x00112233 0xffffffff at -14 \ (Matches the source MAC address 
	// match u16 0x4455 0xffff at -18 \
	// flowid <target class>
	//
	// 'u32' is a filter that matches packets based on sepcific fields or patterns within the packet.
	// 	Stands for "unsigned 32-bit"
	//
	// 'u16' allows for matching and filtering on 16-bit values within packets. It is an option
	// 	available within the u32 filter

//	$tc_filter_add = sprintf("tc filter add dev wlan0 parent 1:0 protocol ip prio 1 u32 match ip dst %s match u16 0x0800 0xffff at -4 match u32flowid 1:20", $_SERVER['REMOTE_ADDR']);
//		shell_exec($tc_filter_add);
}

// This function coordinates all the actions required to allow internet access
function allow_internet_access($sq_id) {
	// First we check:
	//  1. User is logged in? `check_for_validation()`
	//  2. User status allows a session? `get_user_status()`
	//  3. User has a balance remaining in `balance_table` for this $sq_id? `get_byte_balance()`
	//
	// Then we add:
	//  1. Add a session to `internet_sessions` `add_internet_session()`
	//  2. Add iptables rules `add_iptables_rules()`
	//  3. Add tc filter rule `add_tc_filter_rule()`
	//
	// Finally, return the assigned internet session id

	// Check for validation
	if(!check_for_validation()) {
		throw new Exception("User not logged in.");
	}

	// Check user status allows for an internet session.
	if(get_user_status() != "ACTIVE" ||
			(get_user_status() == "BULK" && $sq_id != 1)) {
		throw new Exception("Your current account status does not allow this type of internet access.");
	}

	// Does the user have a byte balance for the desired speed queue?
	if(!check_session_allowed($sq_id)) {
		throw new Exception("No available data for the requested speed queue.");
	}


	/* They passed all the checks */
	// Add an entry to the `internet_sessions` database
	$isess_id = add_internet_session($sq_id);
	if(!$isess_id) {
		throw new Exception("Failed to add new session to 'internet_sessions' table.");
	} else {
		setcookie("isess_id", $isess_id, time() + 3600);
	}

	// Add required iptables rules
	add_iptables_rules($_COOKIE['user_id'] . ':' . $isess_id, fetch_ip_mac());

	// Add required tc filter rule
//	add_tc_filter($_COOKIE['user_id'] . ':' . $isess_id, fetch_ip_mac());
}

function verify_internet_access() {
	if(isset($_COOKIE['isess_id'])) {
		$cc_db = new SQLite3('../CyberCafe.db');
		$get_status_query = $cc_db->prepare("
			SELECT 1 AS session_exists FROM internet_sessions WHERE
				isess_id = :isess_id AND
				user_id = :user_id AND
				isess_length = -1");
		$get_status_query->bindValue(':isess_id', $_COOKIE['isess_id']);
		$get_status_query->bindValue(':user_id', $_COOKIE['user_id']);
		$get_status_result = $get_status_query->execute()->fetchArray();

		if($get_status_result['session_exists'] == 1) {
			return true;
		} else {
			print("FALSE!");
			return false;
		}
	}

	return false;

}
//setcookie("user_id", $_COOKIE['user_id'], time() - 3600);
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	$cc_db = new SQLite3('../CyberCafe.db');

	$check_userid_query = $cc_db->prepare("SELECT 1 AS user_exists FROM user_data WHERE user_id = :input");

	$check_userid_query->bindValue(':input', $_POST['user_id']);

	$check_userid_result = $check_userid_query->execute();

	$check_userid_row = $check_userid_result->fetchArray();

	if($check_userid_row['user_exists'] == 1) {
		$wsess_id = uniqid();
		$create_wsession_query = $cc_db->prepare("
			INSERT INTO website_sessions (wsess_id, user_id, wsess_datetime, expr_datetime) VALUES (
				:wsess_id, :user_id, datetime('now'), datetime('now', '+1 hour'))");
		$create_wsession_query->bindValue(":wsess_id", $wsess_id);
		$create_wsession_query->bindValue(":user_id", $_POST['user_id']);

		if($create_wsession_query->execute() && $cc_db->changes() > 0) {
			setcookie("user_id", $_POST['user_id'], time() + 3600);
			setcookie("wsess_id", $wsess_id, time() + 3600);
			print("Oh, hi " . $_POST['user_id']);
		} else {
			print("There was a problem logging you in!");
			prompt_for_validation();
		}
	} else {
		print("Sorry, couldn't find that user id.");
		prompt_for_validation();
	}

	$cc_db->close();
} elseif(check_for_validation()) {
	// Confirmed they have the correct cookies and entry in website_sessions
	print("Welcome back.<br />");

	// Do they have an entry in 'internet_sessions'?
	if(verify_internet_access()) {
		print("You HAVE internet!");
	} else {
		print("You DO NOT have internet!<br />");
		try {
			allow_internet_access(1);
		} catch(Exception $e) {
			print("Whoops! You cannot have internet either!<br />");
			echo $e->getMessage();
		}
	}
	//add_iptables_rules($_COOKIE['user_id'] . ':' . $_COOKIE['isess_id'], fetch_ip_mac());

} else {
	print("You're going to have to log in");
	prompt_for_validation();
}
?>
