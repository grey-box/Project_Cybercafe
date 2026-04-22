<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
#error_reporting(E_ALL);
require __DIR__ . '/../global.php';

function createAccount($username,$password,$passwordRetyped,$realname,$phone,$email)
{
	$db = global_createDatabaseObj();
	#check that passwords match
	if($password!=$passwordRetyped)
	{
		return 2;
	}
	$password=hashPassword($password);
	#check that username is unique
	$response = $db->query("SELECT 1 FROM users WHERE username='".$username."'");
	$responseArray = $response->fetchArray();
	if($responseArray)
	{
		return 3;
	}
	#create account
	$response = $db->query("SELECT MAX(user_id) FROM users");
	$responseArray = $response->fetchArray();
	$user_id = (int)$responseArray[0]+1;
	$db->exec("INSERT INTO users (
	user_id,
	name,
	email,
	phone,
	username,
	password,
	user_level,
	lane_id,
	status)
	VALUES(
	".$user_id.",
	'".$realname."',
	'".$email."',
	'".$phone."',
	'".$username."',
	'".$password."',
	1,
	0,
	'DISABLED'
	)");
	$db->close();
	return 1;
}

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
	'.$GLOBALS['defaultStyle'].'
	</style>
	<body>
		<a><img src="/assets/CyberCafe_logo.png" width="100" height="100"></a>
		'.$GLOBALS['defaultNavHTML'].'
		<h2>Create Account<h2>
		<form method="post"><p>
				<label for="realname">Name:</label><br>
				<input type="text" name="realname" id="realname" required><br>
				<label for="email">Email:</label><br>
				<input type="text" name="email" id="email" required><br>
				<label for="phone">Phone:</label><br>
				<input type="text" name="phone" id="phone" required><br>
				<label for="username">User Name:</label><br>
				<input type="text" name="username" id="username" required><br>
				<label for="password">Password:</label><br>
				<input type="password" name="password" id="password" required><br>
				<label for="password-retyped">Retype Password:</label><br>
				<input type="password" name="password-retyped" id="password-retyped" required><br>
				<button type="submit">Submit</button><br></p>
		</form>
	</body>
	</html>
	';
}

$userType = global_verifyUser($_COOKIE);
if($userType=='admin')
{
	header('Location: /home');
}
elseif($userType=='user')
{
	header('Location: /home');
}
elseif($userType=='default')
{
	if(isset($_POST['username'])&&
	isset($_POST['password'])&&
	isset($_POST['password-retyped'])&&
	isset($_POST['realname'])&&
	isset($_POST['phone'])&&
	isset($_POST['email']))
	{
		$returnVal=createAccount($_POST['username'],$_POST['password'],$_POST['password-retyped'],$_POST['realname'],$_POST['phone'],$_POST['email']);
		if($returnVal==1)
		{
			header('Location: ../login');
		}
		if($returnVal==2)
		{
			displayPage();
			echo "Passwords don't match.";
		}
		if($returnVal==3)
		{
			displayPage();
			echo "Username is taken.";
		}
		else
		{
			displayPage();
			echo "Error creating account";
		}
	}
	else
	{
		displayPage();
	}
}
else
{}
?>
