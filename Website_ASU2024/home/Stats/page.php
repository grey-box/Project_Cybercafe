<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
#error_reporting(E_ALL);
require __DIR__ . '/../../globalfunctions.php';

function dailyStats()
{
	$db = global_createDatabaseObj();
	#Daily Stats
	$response = $db->query("SELECT * FROM users");
	$HTML='
		<table style="width:100%">
			<tr>
				<th style="width: 3%">User ID</th>
				<th style="width: 15%">Username</th>
				<th style="width: 25%">Name</th>
				<th style="width: 10%">Today\'s RX</th>
				<th style="width: 10%">Today\'s TX</th>
				<th style="width: 10%">Today\'s Total</th>
				<th style="width: 3%">Lane ID</th>
				<th style="width: 5%">Admin</th>
			</tr>';
	$i=0;
	while($responseArray=$response->fetchArray())
	{
		$user_id=$responseArray['user_id'];
		$response2 = $db->query("SELECT SUM(interval_bytes_rx) FROM user_data_usage WHERE entry_datetime>=date(datetime(),'localtime','-1 days') AND user_id=".$user_id);
		$response3 = $db->query("SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE entry_datetime>=date(datetime(),'localtime','-1 days') AND user_id=".$user_id);
		if($response2)
		{
			$userRXToday=$response2->fetchArray(SQLITE3_NUM)[0];
		}
		if($response3)
		{
			$userTXToday=$response3->fetchArray(SQLITE3_NUM)[0];
		}
		if($responseArray['user_level']==0)
		{
			$isAdmin='Yes';
		}
		else
		{
			$isAdmin='No';
		}
		#Convert to KB
		$userTXToday=round($userTXToday/1000);
		$userRXToday=round($userRXToday/1000);
		if(($userRXToday!=0 && $userRXToday!='')||($userTXToday!=0 && $userTXToday!=''))
		{
			$userTotalToday=(int)$userRXToday+(int)$userTXToday;
			$HTML=$HTML."<tr>
				<td>".$responseArray['user_id']."</td>
				<td>".$responseArray['username']."</td>
				<td>".$responseArray['name']."</td>
				<td>".$userRXToday." KB</td>
				<td>".$userTXToday." KB</td>
				<td>".$userTotalToday." KB</td>
				<td>".$responseArray['lane_id']."</td>
				<td>".$isAdmin."</td>";
		}
	}
	$HTML=$HTML."</tr></table>";
	$db->close();
	return $HTML;
}

function weeklyStats()
{
	$db = global_createDatabaseObj();
	#Daily Stats
	$response = $db->query("SELECT * FROM users");
	$HTML='
		<table style="width:100%">
			<tr>
				<th style="width: 3%">User ID</th>
				<th style="width: 15%">Username</th>
				<th style="width: 25%">Name</th>
				<th style="width: 10%">Week\'s RX</th>
				<th style="width: 10%">Week\'s TX</th>
				<th style="width: 10%">Week\'s Total</th>
				<th style="width: 3%">Lane ID</th>
				<th style="width: 5%">Admin</th>
			</tr>';
	$i=0;
	while($responseArray=$response->fetchArray())
	{
		$user_id=$responseArray['user_id'];
		$response2 = $db->query("SELECT SUM(interval_bytes_rx) FROM user_data_usage WHERE entry_datetime>=date(datetime(),'localtime','-7 days') AND user_id=".$user_id);
		$response3 = $db->query("SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE entry_datetime>=date(datetime(),'localtime','-7 days') AND user_id=".$user_id);
		if($response2)
		{
			$userRXWeek=$response2->fetchArray(SQLITE3_NUM)[0];
		}
		if($response3)
		{
			$userTXWeek=$response3->fetchArray(SQLITE3_NUM)[0];
		}
		if($responseArray['user_level']==0)
		{
			$isAdmin='Yes';
		}
		else
		{
			$isAdmin='No';
		}
		#Convert to KB
		$userTXWeek=round($userTXWeek/1000);
		$userRXWeek=round($userRXWeek/1000);
		if(($userRXWeek!=0 && $userRXWeek!='')||($userTXWeek!=0 && $userTXWeek!=''))
		{
			$userTotalToday=(int)$userRXWeek+(int)$userTXWeek;
			$HTML=$HTML."<tr>
				<td>".$responseArray['user_id']."</td>
				<td>".$responseArray['username']."</td>
				<td>".$responseArray['name']."</td>
				<td>".$userRXWeek." KB</td>
				<td>".$userTXWeek." KB</td>
				<td>".$userTotalToday." KB</td>
				<td>".$responseArray['lane_id']."</td>
				<td>".$isAdmin."</td>";
		}
	}
	$HTML=$HTML."</tr></table>";
	$db->close();
	return $HTML;
}

function monthlyStats()
{
	$db = global_createDatabaseObj();
	#Daily Stats
	$response = $db->query("SELECT * FROM users");
	$HTML='
		<table style="width:100%">
			<tr>
				<th style="width: 3%">User ID</th>
				<th style="width: 15%">Username</th>
				<th style="width: 25%">Name</th>
				<th style="width: 10%">Month\'s RX</th>
				<th style="width: 10%">Month\'s TX</th>
				<th style="width: 10%">Month\'s Total</th>
				<th style="width: 3%">Lane ID</th>
				<th style="width: 5%">Admin</th>
			</tr>';
	$i=0;
	while($responseArray=$response->fetchArray())
	{
		$user_id=$responseArray['user_id'];
		$response2 = $db->query("SELECT SUM(interval_bytes_rx) FROM user_data_usage WHERE entry_datetime>=date(datetime(),'localtime','-30 days') AND user_id=".$user_id);
		$response3 = $db->query("SELECT SUM(interval_bytes_tx) FROM user_data_usage WHERE entry_datetime>=date(datetime(),'localtime','-30 days') AND user_id=".$user_id);
		if($response2)
		{
			$userRXMonth=$response2->fetchArray(SQLITE3_NUM)[0];
		}
		if($response3)
		{
			$userTXMonth=$response3->fetchArray(SQLITE3_NUM)[0];
		}
		if($responseArray['user_level']==0)
		{
			$isAdmin='Yes';
		}
		else
		{
			$isAdmin='No';
		}
		#Convert to KB
		$userTXMonth=round($userTXMonth/1000);
		$userRXMonth=round($userRXMonth/1000);
		if(($userRXMonth!=0 && $userRXMonth!='')||($userTXMonth!=0 && $userTXMonth!=''))
		{
			$userTotalToday=(int)$userRXMonth+(int)$userTXMonth;
			$HTML=$HTML."<tr>
				<td>".$responseArray['user_id']."</td>
				<td>".$responseArray['username']."</td>
				<td>".$responseArray['name']."</td>
				<td>".$userRXMonth." KB</td>
				<td>".$userTXMonth." KB</td>
				<td>".$userTotalToday." KB</td>
				<td>".$responseArray['lane_id']."</td>
				<td>".$isAdmin."</td>";
		}
	}
	$HTML=$HTML."</tr></table>";
	$db->close();
	return $HTML;
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
	table, th, td {
	}
	th, td
	{
		padding-top: 10px;
		padding-bottom: 10px;
		padding-left: 10px;
		padding-right: 10px;
		border-style: groove;
		text-align: center;
		font-size: 70%
	}
	td
	{
		font-weight:normal;
		font-size: 60%;
	}
	</style>
	<body>
		<a><img src="/assets/CyberCafe_logo.png" width="100" height="100"></a>
		'.$GLOBALS['adminNavHTML'].'
		<h2>Statistics<h2>
		<p>Daily</p>
		'.dailyStats().'
		<p>Weekly</p>
		'.weeklyStats().'
		<p>Monthly</p>
		'.monthlyStats().'
	</body>
	</html>
	';
}

$userType = global_verifyUser($_COOKIE);
if($userType=='admin')
{
	displayPage();
}
elseif($userType=='user')
{
	header('Location: /home');
}
elseif($userType=='default')
{
	header('Location: /login');
}
else
{}
?>