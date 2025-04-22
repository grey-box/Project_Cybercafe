<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
#error_reporting(E_ALL);
require __DIR__ . '/../globalfunctions.php';

function logIn($username,$password,$session_id)
{
	$password = hashPassword($password);
	$db = global_createDatabaseObj();
	$response = $db->query("SELECT * FROM users WHERE username='".$username."'");
	$responseArray = $response->fetchArray();
	if($responseArray['status']=='BANNED' && $responseArray['user_level']!=0)
	{
		return false;
	}
	elseif($responseArray['password']==$password)
	{
		$user_level = (int)$responseArray['user_level'];
		$user_id = (int)$responseArray['user_id'];
		$lane_id = (int)$responseArray['lane_id'];
		#check to see if there are any sessions already active with this user_id
		$response2 = $db->query("SELECT * FROM internet_sessions WHERE user_id='".$user_id."'");
		$responseArray2 = $response2->fetchArray();
		if($responseArray2)
		{
			#Must close database temporarily so process write mutex lock for the database can be given to bash script
			$db->close();
			global_removeInternetSession($responseArray['table_index']);
			$db = global_createDatabaseObj();
		}
		if($user_level=='0')
		{
			$sessionAccessBit = 1;
		}
		else if($user_level=='1')
		{
			$sessionAccessBit = 0;
		}
		$response = $db->query("SELECT MAX(table_index) FROM internet_sessions");
		$responseArray = $response->fetchArray();
		$response2 = $db->query("SELECT datetime_created FROM internet_sessions");
		$responseArray2 = $response2->fetchArray();
		if($responseArray2['datetime_created']==NULL)
		{
			$internetSessionIndex=0;
		}
		else
		{
			$internetSessionIndex=((int)$responseArray[0])+1;
		}
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
	$db = global_createDatabaseObj();
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

function displayPage()
{
		echo '<!DOCTYPE html>
		<html>
		<head>
				<title>Cybercafe Demo</title>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
		</head>
		<style>
		ul {
			list-style-type: none;
			margin: 0;
			padding: 0;
			overflow: hidden;
			background-color: #e7e7e7;
		}
		li {
			float: left;
		}
		li a {
			display: block;
			color: black;
			text-align: center;
			padding: 14px 16px;
			text-decoration: none;
		}
		li a:hover {
			background-color: #bfbfbf;
		}
		</style>
		<body>
			<a><img src="/assets/CyberCafe_logo.png" width="100" height="100"></a>
			'.$GLOBALS['defaultNavHTML'].'
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
			setcookie("session_id",$session_id,time()+43200,"/");
			header('Location: ../home');
			die();
		}
		else
		{
			displayPage();
			echo "Login Failed!";
		}
	}
	else
	{
		displayPage();
	}
}
?>
