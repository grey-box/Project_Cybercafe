<?php
$timezone=date_default_timezone_get();
date_default_timezone_set($timezone);
$GLOBALS['database_path']='/data/data/com.termux/files/usr/var/www/database/CyberCafe_Database.db';
$GLOBALS['internetSessionFunctionsShellScript_path']='/data/data/com.termux/files/usr/var/www/backend/Cybercafe_internetSessionFunctions.sh';
$GLOBALS['adminNavHTML']='
		<ul>
			<li><a href="/home">Home</a></li>
			<li><a href="/home/Stats">Stats</a></li>
			<li><a href="/home/ManageUsers">Manage Users</a></li>
			<li><a href="/home/ManageLanes">Manage Lanes</a></li>
			<li><a href="/about/">About</a></li>
			<li><a href="/logout">Logout</a></li>
		</ul>';
$GLOBALS['userNavHTML']='
		<ul>
			<li><a href="/home">Home</a></li>
			<li><a href="/about/">About</a></li>
			<li><a href="/logout">Logout</a></li>
		</ul>';
$GLOBALS['defaultNavHTML']='
		<ul>
			<li><a href="/home">Home</a></li>
			<li><a href="/login/">Login</a></li>
			<li><a href="/createaccount">Create Account</a></li>
			<li><a href="/about/">About</a></li>
		</ul>';

function global_createDatabaseObj()
{
	return $db = new SQLite3($GLOBALS['database_path']);
}

function global_removeInternetSession($table_index)
{
	$db = global_createDatabaseObj();
	$db->exec("UPDATE internet_sessions SET pending_deletion=1 WHERE table_index=".$table_index);
	$db->close();
}

function global_verifyUser($cookies)
{
	if(isset($cookies['session_id']))
	{
		$db = global_createDatabaseObj();
		$response = $db->query("SELECT user_id FROM internet_sessions WHERE session_id='".$cookies['session_id']."'");
		$responseArray = $response->fetchArray();
		if($responseArray)
		{
			$user_id = (int)$responseArray['user_id'];
			$response2 = $db->query("SELECT * FROM users WHERE user_id=".$user_id."");
			$responseArray2 = $response2->fetchArray();
			$db->close();
			if($responseArray2['user_level']==0)
			{
				return 'admin';
			}
			elseif($responseArray2['user_level']==1 && $responseArray2['status']!='BANNED')
			{
				return 'user';
			}
			else
			{
				setcookie('session_id', '', time()-3600, '/');
				header('Location: /login');
				return -1;
			}
		}
		#if there is no internet session matching the cookie then return to login
		else
		{
			$db->close();
			setcookie('session_id', '', time()-3600, '/');
			header('Location: /login');
			return -1;
		}
		$db->close();
	}
	else
	{
		return 'default';
	}
}
?>