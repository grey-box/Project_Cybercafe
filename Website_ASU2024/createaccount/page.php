<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
#error_reporting(E_ALL);
$GLOBALS['database_path']='/data/data/com.termux/files/usr/var/www/database/CyberCafe_Database.db';

function displayPage()
{
	echo '
	<!DOCTYPE html>
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
		<ul>
			<li><a href="/home">Home</a></li>
			<li><a href="/login/">Login</a></li>
			<li><a href="/createaccount">Create Account</a></li>
			<li><a href="/about/">About</a></li>
		</ul>
		<h2>Create Account<h2>
	</body>
	</html>
	';
}
if(isset($_COOKIE['session_id']))
{
	$db = new SQLite3($GLOBALS['database_path']);
	$response = $db->query("SELECT user_id FROM internet_sessions WHERE session_id='".$_COOKIE['session_id']."'");
	$responseArray = $response->fetchArray();
	#if there is an internet session found matching the query then load respective page
	if($responseArray)
	{
		header('Location: /home');
	}
	#if there is no internet session matching the cookie then return to login
	else
	{
		setcookie('session_id', '', time()-3600, '/');
		header('Location: /login');
	}
	$db->close();
}
else
{
        displayPage();
}
?>
