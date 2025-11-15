<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
#error_reporting(E_ALL);
require __DIR__ . '/../global.php';
$GLOBALS['aboutPageContent']='
		<p>Project Cybercafe is software that runs on an Android device, enabling smart hotspot functionality for the sharing of internet connectivity 
		with other Wi-Fi devices. Recognizing costs and other restrictions that may exist in our targeted regions, several controls are in place to allow 
		a high level of control over how a user’s hotspot device is used for internet access.<br>
		<a href="https://www.grey-box.ca/project-cybercafe/" target="_blank">https://www.grey-box.ca/project-cybercafe/</a></p>
		';

function aboutPageAdmin()
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
	'.$GLOBALS['defaultStyle'].'
	</style>
	<body>
		<a><img src="/assets/CyberCafe_logo.png" width="100" height="100"></a>
		'.$GLOBALS['adminNavHTML'].'
		<h2>About<h2>
		'.$GLOBALS['aboutPageContent'].'
	</body>
	</html>
	';
}
function aboutPageUser()
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
	'.$GLOBALS['defaultStyle'].'
	</style>
	<body>
		<a><img src="/assets/CyberCafe_logo.png" width="100" height="100"></a>
		'.$GLOBALS['userNavHTML'].'
		<h2>About<h2>
		'.$GLOBALS['aboutPageContent'].'
	</body>
	</html>
	';
}
function aboutPageDefault()
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
	'.$GLOBALS['defaultStyle'].'
	</style>
	<body>
		<a><img src="/assets/CyberCafe_logo.png" width="100" height="100"></a>
		'.$GLOBALS['defaultNavHTML'].'
		<h2>About<h2>
		'.$GLOBALS['aboutPageContent'].'
	</body>
	</html>
	';
}

$userType = global_verifyUser($_COOKIE);
if($userType=='admin')
{
	aboutPageAdmin();
}
elseif($userType=='user')
{
	aboutPageUser();
}
elseif($userType=='default')
{
	aboutPageDefault();
}
else
{}
?>