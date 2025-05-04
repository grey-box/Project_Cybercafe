<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
require __DIR__ . '/../globalfunctions.php';

function adminHomePage()
{
	$db = global_createDatabaseObj();
	$response = $db->query("SELECT * FROM internet_sessions WHERE session_id='".$_COOKIE['session_id']."'");
	$responseArray = $response->fetchArray();
	$user_id=$responseArray['user_id'];
	
	$response2 = $db->query("SELECT MAX(session_number) FROM user_data_usage WHERE user_id=".$user_id);
	$responseArray2 = $response2->fetchArray();
	$sessionNumber=(int)$responseArray2[0];
	
	#Get data usage information
	#Session TX
	$sessionTX=$responseArray['session_tx'];
	#Session RX
	$sessionRX=$responseArray['session_rx'];
	#Today
	$response2 = $db->query("SELECT SUM(interval_bytes_tx),SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=".$user_id." AND entry_datetime>=datetime(datetime(),'localtime','-1 days')");
	$responseArray2 = $response2->fetchArray(SQLITE3_NUM);
	$todayTotalTX = (int)$responseArray2[0];
	$todayTotalRX = (int)$responseArray2[1];
	$todayTotal = $todayTotalTX+$todayTotalRX;
	#Week
	$response2 = $db->query("SELECT SUM(interval_bytes_tx),SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=".$user_id." AND entry_datetime>=datetime(datetime(),'localtime','-7 days')");
	$responseArray2 = $response2->fetchArray(SQLITE3_NUM);
	$weekTotalTX = (int)$responseArray2[0];
	$weekTotalRX = (int)$responseArray2[1];
	$weekTotal = $weekTotalTX+$weekTotalRX;
	#Month
	$response2 = $db->query("SELECT SUM(interval_bytes_tx),SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=".$user_id." AND entry_datetime>=datetime(datetime(),'localtime','-30 days')");
	$responseArray2 = $response2->fetchArray(SQLITE3_NUM);
	$monthTotalTX = (int)$responseArray2[0];
	$monthTotalRX = (int)$responseArray2[1];
	$monthTotal = $monthTotalTX+$monthTotalRX;
	
	#Status
	$response2 = $db->query("SELECT status,lane_id FROM users WHERE user_id=".$user_id);
	$responseArray2 = $response2->fetchArray();
	$status=$responseArray2['status'];
	$lane_id=$responseArray2['lane_id'];
	if($status=='ACTIVE')
	{
		$status='ADMIN';
	}
	#Convert to KB
	$todayTotal=round($todayTotal/1000);
	$weekTotal=round($weekTotal/1000);
	$monthTotal=round($monthTotal/1000);
	$sessionTX=round($sessionTX/1000);
	$sessionRX=round($sessionRX/1000);
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
		<h2>Admin Page<h2>
		<form method="post">
			<input type="submit" value="Refresh">
		</form>
		<table>
			<tr>
				<th>Session #</th>
				<td>'.$sessionNumber.'</td>
			</tr>
			<tr>
				<th>Data Lane #</th>
				<td>'.$lane_id.'</td>
			</tr>
			<tr>
				<th>Session Status</th>
				<td>'.$status.'</td>
			</tr>
			<tr>
				<th>Session TX</th>
				<td>'.$sessionTX.' KB</td>
			</tr>
			<tr>
				<th>Session RX</th>
				<td>'.$sessionRX.' KB</td>
			</tr>
			<tr>
				<th>Today\'s Byte Usage</th>
				<td>'.$todayTotal.' KB</td>
			</tr>
			<tr>
				<th>Lane Daily Limit</th>
				<td>ADMIN</td>
			</tr>
			<tr>
				<th>Weeks\'s Byte Usage</th>
				<td>'.$weekTotal.' KB</td>
			</tr>
			<tr>
				<th>Lane Weekly Limit</th>
				<td>ADMIN</td>
			</tr>
			<tr>
				<th>Month\'s Byte Usage</th>
				<td>'.$monthTotal.' KB</td>
			</tr>
			<tr>
				<th>Lane Monthly Limit</th>
				<td>ADMIN</td>
			</tr>
	</body>
	</html>
	';
}
function userHomePage()
{
	$db = global_createDatabaseObj();
	$response = $db->query("SELECT * FROM internet_sessions WHERE session_id='".$_COOKIE['session_id']."'");
	$responseArray = $response->fetchArray();
	$user_id=$responseArray['user_id'];
	
	$response2 = $db->query("SELECT MAX(session_number) FROM user_data_usage WHERE user_id=".$user_id);
	$responseArray2 = $response2->fetchArray();
	$sessionNumber=(int)$responseArray2[0];
	
	#Get data usage information
	#Session TX
	$sessionTX=$responseArray['session_tx'];
	#Session RX
	$sessionRX=$responseArray['session_rx'];
	#Today
	$response2 = $db->query("SELECT SUM(interval_bytes_tx),SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=".$user_id." AND entry_datetime>=datetime(datetime(),'localtime','-1 days')");
	$responseArray2 = $response2->fetchArray(SQLITE3_NUM);
	$todayTotalTX = (int)$responseArray2[0];
	$todayTotalRX = (int)$responseArray2[1];
	$todayTotal = $todayTotalTX+$todayTotalRX;
	#Week
	$response2 = $db->query("SELECT SUM(interval_bytes_tx),SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=".$user_id." AND entry_datetime>=datetime(datetime(),'localtime','-7 days')");
	$responseArray2 = $response2->fetchArray(SQLITE3_NUM);
	$weekTotalTX = (int)$responseArray2[0];
	$weekTotalRX = (int)$responseArray2[1];
	$weekTotal = $weekTotalTX+$weekTotalRX;
	#Month
	$response2 = $db->query("SELECT SUM(interval_bytes_tx),SUM(interval_bytes_rx) FROM user_data_usage WHERE user_id=".$user_id." AND entry_datetime>=datetime(datetime(),'localtime','-30 days')");
	$responseArray2 = $response2->fetchArray(SQLITE3_NUM);
	$monthTotalTX = (int)$responseArray2[0];
	$monthTotalRX = (int)$responseArray2[1];
	$monthTotal = $monthTotalTX+$monthTotalRX;
	
	#Status
	$response2 = $db->query("SELECT status,lane_id FROM users WHERE user_id=".$user_id);
	$responseArray2 = $response2->fetchArray();
	$status=$responseArray2['status'];
	$lane_id=$responseArray2['lane_id'];
	if($status=='ACTIVE')
	{
		#Calculate if overlimits
		#LANE_DAILY_LIMIT=$(($(sqlite3 "${DATABASE_PATH}" "SELECT bytelimit_daily FROM data_lanes WHERE lane_id=${LANE_ID}"))) > /dev/null 2>> error.log
		#LANE_WEEKLY_LIMIT=$(($(sqlite3 "${DATABASE_PATH}" "SELECT bytelimit_weekly FROM data_lanes WHERE lane_id=${LANE_ID}"))) > /dev/null 2>> error.log
		#LANE_MONTHLY_LIMIT=$(($(sqlite3 "${DATABASE_PATH}" "SELECT bytelimit_monthly FROM data_lanes WHERE lane_id=${LANE_ID}"))) > /dev/null 2>> error.log
		$response2 = $db->query("SELECT bytelimit_daily,bytelimit_weekly,bytelimit_monthly FROM data_lanes WHERE lane_id=".$lane_id);
		$responseArray2 = $response2->fetchArray();
		$laneDailyLimit=(int)$responseArray2[0];
		$laneWeeklyLimit=(int)$responseArray2[1];
		$laneMonthlyLimit=(int)$responseArray2[2];
		if($todayTotal>$laneDailyLimit or $weekTotal>$laneWeeklyLimit or $monthTotal>$laneMonthlyLimit)
		{
			$status='Over Limits';
		}
	}
	#Convert to KB
	$laneDailyLimit=round($laneDailyLimit/1000);
	$laneWeeklyLimit=round($laneWeeklyLimit/1000);
	$laneMonthlyLimit=round($laneMonthlyLimit/1000);
	$todayTotal=round($todayTotal/1000);
	$weekTotal=round($weekTotal/1000);
	$monthTotal=round($monthTotal/1000);
	$sessionTX=round($sessionTX/1000);
	$sessionRX=round($sessionRX/1000);
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
		'.$GLOBALS['userNavHTML'].'
		<h2>User Page<h2>
		<form method="post">
			<input type="submit" value="Refresh">
		</form>
		<table>
			<tr>
				<th>Session #</th>
				<td>'.$sessionNumber.'</td>
			</tr>
			<tr>
				<th>Data Lane #</th>
				<td>'.$lane_id.'</td>
			</tr>
			<tr>
				<th>Session Status</th>
				<td>'.$status.'</td>
			</tr>
			<tr>
				<th>Session TX</th>
				<td>'.$sessionTX.' KB</td>
			</tr>
			<tr>
				<th>Session RX</th>
				<td>'.$sessionRX.' KB</td>
			</tr>
			<tr>
				<th>Today\'s Byte Usage</th>
				<td>'.$todayTotal.' KB</td>
			</tr>
			<tr>
				<th>Lane Daily Limit</th>
				<td>'.$laneDailyLimit.' KB</td>
			</tr>
			<tr>
				<th>Weeks\'s Byte Usage</th>
				<td>'.$weekTotal.' KB</td>
			</tr>
			<tr>
				<th>Lane Weekly Limit</th>
				<td>'.$laneWeeklyLimit.' KB</td>
			</tr>
			<tr>
				<th>Month\'s Byte Usage</th>
				<td>'.$monthTotal.' KB</td>
			</tr>
			<tr>
				<th>Lane Monthly Limit</th>
				<td>'.$laneMonthlyLimit.' KB</td>
			</tr>
	</body>
	</html>
	';
}
function defaultHomePage()
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
		<h2>Greybox - Cybercafe<h2>
	</body>
	</html>
	';
}

$userType = global_verifyUser($_COOKIE);
if($userType=='admin')
{
	adminHomePage();
}
elseif($userType=='user')
{
	userHomePage();
}
elseif($userType=='default')
{
	defaultHomePage();
}
else
{}
?>