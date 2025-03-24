<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function logIn($username,$password,$session_id)
{
	$password = hashPassword($password);
	$db = new SQLite3('../../database/CyberCafe_Database.db');
	$response = $db->query("SELECT * FROM users WHERE username='".$username."'");
	$responseArray = $response->fetchArray();
	if($responseArray['password']==$password)
	{
		$user_level = (int)$responseArray['user_level'];
		$user_id = (int)$responseArray['user_id'];
		$lane_id = (int)$responseArray['lane_id'];
		if($user_level=='0')
		{
			$sessionAccessBit = 1;
		}
		else if($user_level=='1')
		{
			$sessionAccessBit = 0;
		}
		#Create New Internet Session to write to database
		$response = $db->query("SELECT MAX(table_index) FROM internet_sessions");
		$responseArray = $response->fetchArray();
		if($responseArray)
		{
			$internetSessionIndex = (int)$responseArray['table_index']+1;
		}
		else
		{
			$internetSessionIndex = 0;
		}
		#check to see if there are any sessions already active with this user_id
		$response = $db->query("SELECT * FROM internet_sessions WHERE user_id='".$user_id."'");
		$responseArray = $response->fetchArray();
		#remove the old session associated with this user_id if it exists.
		if($responseArray)
		{
			shell_exec("bash ../../backend/Cybercafe_internetSessionFunctions.sh remove_session ".$responseArray['table_index']);
		}
		#TODO: create method to check if this new session will already be out of range of their data limits
		#get datetime info
		$datetimeObj = new DateTime();
		$datetime = $datetimeObj->format('Y-m-d H:i:s');
		$db->exec("INSERT INTO internet_sessions (
		table_index,
		user_id,
		session_id,
		ip,
		session_tx,
		session_rx,
		session_access,
		datetime_created,
		datetime_sinceLastRequest)
		VALUES(
		".$internetSessionIndex.",
		".$user_id.",
		'".$session_id."',
		'".$_SERVER['REMOTE_ADDR']."',
		0,0,
		".$sessionAccessBit.",
		'".$datetime."',
		'".$datetime."')");
		##DEBUG##
		echo "<p>\$db->exec(\"INSERT INTO internet_sessions (<br>table_index,<br>user_id,<br>session_id,<br>ip,<br>session_tx,<br>session_rx,<br>session_access,<br>datetime_created,<br>datetime_sinceLastRequest)<br>VALUES(<br>".$internetSessionIndex.",<br>".$user_id.",<br>'".$session_id."',<br>'".$_SERVER['REMOTE_ADDR']."',<br>0,0,<br>".$sessionAccessBit.",<br>'".$datetime."',<br>'".$datetime."')\");<br></p>";
		#
		$db->close();
		return true;
	}
	$db->close();
	return false;
}
function hashPassword($passwordString)
{
	return hash('sha256', $passwordString);
}
function validSessionID($session_id)
{
	$db = new SQLite3('../../database/CyberCafe_Database.db');
	$response = $db->query("SELECT 1 FROM internet_sessions WHERE session_id='".$session_id."'");
	$result = $response->fetchArray();
	
	if($result)
	{
		$db->close();
		return true;
	}
	else
	{
		$db->close();
		return false;
	}
}

if(isset($_COOKIE['session_id'])&&validSessionID($_COOKIE['session_id']))
{
	header('Location: ../home');
	die();
}
else
{
	if(isset($_POST['username'])&&isset($_POST['password']))
	{
		$session_id = session_create_id();
		if(logIn($_POST['username'],$_POST['password'],$session_id))
		{
			setcookie("session_id",$session_id,time()+(3600),"/");
			header('Location: ../home');
			die();
		}
		else
		{
			echo "Login Failed!";
		}
	}
	else
	{
		echo '
		<!DOCTYPE html>
		<html>
		<head>
				<title>Cybercafe Demo - Log-In</title>
		</head>
		<body>

				<h2>Log-In</h2>
				<form method="post">
						<label for="username">User Name:</label><br>
						<input type="text" name="username" id="username" required><br>
						<label for="password">Password:</label><br>
						<input type="password" name="password" id="password" required><br>
						<button type="submit">Submit</button><br>
				</form>
		</body>
		</html>';
	}
}
?>
