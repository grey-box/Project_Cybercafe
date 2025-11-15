<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
require __DIR__ . '/../global.php';

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
		$laneDailyLimit='ADMIN';
		$laneWeeklyLimit='ADMIN';
		$laneMonthlyLimit='ADMIN';
	}
	else
	{
		$laneDailyLimit=0;
		$laneWeeklyLimit=0;
		$laneMonthlyLimit=0;
	}
	echo '<!DOCTYPE html>
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
		#Convert to KB
		$laneDailyLimit=round($laneDailyLimit/1000);
		$laneWeeklyLimit=round($laneWeeklyLimit/1000);
		$laneMonthlyLimit=round($laneMonthlyLimit/1000);
		$todayTotal=round($todayTotal/1000);
		$weekTotal=round($weekTotal/1000);
		$monthTotal=round($monthTotal/1000);
		$sessionTX=round($sessionTX/1000);
		$sessionRX=round($sessionRX/1000);
	}
	else
	{
		$laneDailyLimit=0;
		$laneWeeklyLimit=0;
		$laneMonthlyLimit=0;
	}
	echo '<!DOCTYPE html>
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
	'.$GLOBALS['defaultStyle'].'
	</style>
	<body>
		<a><img src="/assets/CyberCafe_logo.png" width="100" height="100"></a>
		'.$GLOBALS['defaultNavHTML'].'
		<h2>Greybox: Project Cybercafe<h2>
		<p>Project Cybercafe is software that runs on an Android device, enabling smart hotspot functionality for the sharing of internet connectivity 
		with other Wi-Fi devices. Recognizing costs and other restrictions that may exist in our targeted regions, several controls are in place to allow 
		a high level of control over how a user’s hotspot device is used for internet access.<br>
		<a href="https://www.grey-box.ca/project-cybercafe/" target="_blank">https://www.grey-box.ca/project-cybercafe/</a></p>
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