<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
#error_reporting(E_ALL);

#Test Script
function runScript()
{
	echo "Your button works!";
	#$message=shell_exec('/var/www/scripts/ToggleScript.sh');
	#echo $message;
}
function adminPage()
{
	echo '<!DOCTYPE html>
	<html>
	<head>
			<title>Cybercafe Demo - Home</title>
	</head>
	<body>
	</body>
	</html>
	<h2>Admin Page</h2>
	<form method="post">
			<input type="submit" name="test" id="test" value="test button">
	</form>
	</body>
	</html>
	';
}
function userPage()
{
	echo '<!DOCTYPE html>
	<html>
	<head>
			<title>Cybercafe Demo - Home</title>
	</head>
	<body>
	</body>
	</html>
	<h2>User Page</h2>
	</form>
	</body>
	</html>
	';
}
if(isset($_COOKIE['session_id']))
{
	$db = new SQLite3('../../database/CyberCafe_Database.db');
	$response = $db->query("SELECT user_id FROM internet_sessions WHERE session_id='".$_COOKIE['session_id']."'");
	$responseArray = $response->fetchArray();
	#if there is an internet session found matching the query then load respective page
	if($responseArray)
	{
		$user_id = (int)$responseArray['user_id'];
		$response2 = $db->query("SELECT * FROM users WHERE user_id=".$user_id."");
		$responseArray2 = $response2->fetchArray();
		if($responseArray2['user_level']==0)
		{
			adminPage();
			if(array_key_exists('test',$_POST))
			{
				runScript();
			}
		}
		else if($responseArray2['user_level']==1)
		{
			userPage();
		}
	}
	#if there is no internet session matching the cookie then return to login
	else
	{
		setcookie('session_id', '', time()-3600, '/');
		header('Location: /login');
	}
	$db->close();
}
#if session_id is not set users should be redirected to login
else
{
        header('Location: /login');
}
?>
