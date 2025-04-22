<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
#error_reporting(E_ALL);
require __DIR__ . '/../../globalfunctions.php';

function updateByteLimitDaily($lane_id,$bytelimit)
{
	$bytelimit=$bytelimit*(10**6);
	$db = global_createDatabaseObj();
	$db->exec("UPDATE data_lanes SET bytelimit_daily=".$bytelimit." WHERE lane_id=".$lane_id);
	$db->close();
}

function updateByteLimitWeekly($lane_id,$bytelimit)
{
	$bytelimit=$bytelimit*(10**6);
	$db = global_createDatabaseObj();
	$db->exec("UPDATE data_lanes SET bytelimit_weekly=".$bytelimit." WHERE lane_id=".$lane_id);
	$db->close();
}

function updateByteLimitMonthly($lane_id,$bytelimit)
{
	$bytelimit=$bytelimit*(10**6);
	$db = global_createDatabaseObj();
	$db->exec("UPDATE data_lanes SET bytelimit_monthly=".$bytelimit." WHERE lane_id=".$lane_id);
	$db->close();
}

function removeLane($lane_id)
{
	$db = global_createDatabaseObj();
	$db->exec("DELETE FROM data_lanes WHERE lane_id=".$lane_id);
	$db->close();
}

function newLane()
{
	$db = global_createDatabaseObj();
	$response=$db->query("SELECT MAX(lane_id) FROM data_lanes");
	$responseArray=$response->fetchArray();
	if($response)
	{
		$nextLaneID=$responseArray[0]+1;
	}
	else
	{
		$nextLaneID=0;
	}
	$db->exec("INSERT INTO data_lanes (lane_id,lane_name,bytelimit_daily,bytelimit_weekly,bytelimit_monthly) VALUES (".$nextLaneID.",'new lane',0,0,0)");
	$db->close();
}

function renameLane($lane_id,$newName)
{
	$db = global_createDatabaseObj();
	$db->exec("UPDATE data_lanes SET lane_name='".$newName."' WHERE lane_id=".$lane_id);
	$db->close();
}

function displayPage()
{
	$db =global_createDatabaseObj();
	$response = $db->query("SELECT * FROM data_lanes");
	$table_entries="";
	$i=0;
	while($responseArray=$response->fetchArray())
	{
		$lane_id=$responseArray['lane_id'];
		$lane_name=$responseArray['lane_name'];
		$bytelimit_daily=$responseArray['bytelimit_daily']/(10**6);
		$bytelimit_weekly=$responseArray['bytelimit_weekly']/(10**6);
		$bytelimit_monthly=$responseArray['bytelimit_monthly']/(10**6);
		$response2=$db->query("SELECT username FROM users WHERE lane_id=".$lane_id);
		$responseArray2=$response2->fetchArray(SQLITE3_NUM);
		if($responseArray2)
		{
			$usersInLane=sizeof($responseArray2);
		}
		else
		{
			$usersInLane=0;
		}
		$table_entries=$table_entries."<tr>
			<td>".$lane_id."</td>
			<td><form method='post'>
				<input type='text' class='textbox2' name='text_laneName' id='text_laneName' value='".$lane_name."'><br>
				<input type='submit' value='update' class='button2'><input type='hidden' name='action_laneID' value='".$lane_id."'></form></td>
			<td>".$usersInLane."</td>
			<td><form method='post'>
				<input type='text' class='textbox' name='bytelimit_daily' id='bytelimit_daily' value='".$bytelimit_daily."'><label for='bytelimit_daily' class='label'>MB<br>
				<input type='submit' value='update' class='button2'><input type='hidden' name='action_laneID' value='".$lane_id."'></form></td>
			<td><form method='post'>
				<input type='text' class='textbox' name='bytelimit_weekly' id='bytelimit_weekly' value='".$bytelimit_weekly."'><label for='bytelimit_daily' class='label'>MB</label><br>
				<input type='submit' value='update' class='button2'><input type='hidden' name='action_laneID' value='".$lane_id."'></form></td>
			<td><form method='post'>
				<input type='text' class='textbox' name='bytelimit_monthly' id='bytelimit_monthly' value='".$bytelimit_monthly."'><label for='bytelimit_daily' class='label'>MB</label><br>
				<input type='submit' value='update' class='button2'><input type='hidden' name='action_laneID' value='".$lane_id."'></form></td>
			<td><form method='post'>
				<input type='submit' name='laneButton_remove' id='laneButton_remove' class='button2' value='remove'><input type='hidden' name='action_laneID' value='".$lane_id."'></form></td>
		</tr>";
		$i=$i+1;
	}
	$db->close();
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
		font-size: 80%;
	}
	.button
	{
		text-align: center;
		display: inline-block;
		height: 50px;
		width: 100px;
		cursor: pointer;
	}
	.button2
	{
		text-align: center;
		display: inline-block;
		height: 30px;
		width: 80px;
		cursor: pointer;
	}
	.button3
	{
		text-align: center;
		display: inline-block;
		height: 50px;
		width: 100px;
		cursor: pointer;
	}
	.textbox
	{
		text-align: right;
		font-family: verdana;
		width:100px;
	}
	.textbox2
	{
		text-align: center;
		font-family: verdana;
		font-size:100%;
	}
	.label
	{
		display:inline;
		font-size:80%;
		padding-left:5px;
	}
	.form2
	{
		text-align: center;
		padding:10px;
	}
	</style>
	<body>
		<a><img src="/assets/CyberCafe_logo.png" width="100" height="100"></a>
		'.$GLOBALS['adminNavHTML'].'
		<h2>Manage Lanes<h2>
		<table style="width:100%">
			<tr>
				<th style="width: 4%">Lane ID</th>
				<th style="width: 15%">Lane Name</th>
				<th style="width: 4%">Users In Lane</th>
				<th style="width: 20%">Bytelimit Daily</th>
				<th style="width: 20%">Bytelimit Weekly</th>
				<th style="width: 20%">Bytelimit Monthly</th>
				<th style="width: 7%">Actions</th>
			</tr>
			'.$table_entries.'
		</table>
		<form method="post" class="form2">
			<input type="submit" name="laneButton_new" id="laneButton_new" value="New Lane" class="button3">
		</form>
	</body>
	</html>
	';
}

$userType = global_verifyUser($_COOKIE);
if($userType=='admin')
{
	if(array_key_exists('bytelimit_daily', $_POST))
	{
		updateByteLimitDaily($_POST['action_laneID'],$_POST['bytelimit_daily']);
	}
	if(array_key_exists('bytelimit_weekly', $_POST))
	{
		updateByteLimitWeekly($_POST['action_laneID'],$_POST['bytelimit_weekly']);
	}
	if(array_key_exists('bytelimit_monthly', $_POST))
	{
		updateByteLimitMonthly($_POST['action_laneID'],$_POST['bytelimit_monthly']);
	}
	if(array_key_exists('laneButton_remove', $_POST))
	{
		removeLane($_POST['action_laneID']);
	}
	if(array_key_exists('laneButton_new', $_POST))
	{
		newLane();
	}
	if(array_key_exists('text_laneName', $_POST))
	{
		renameLane($_POST['action_laneID'],$_POST['text_laneName']);
	}
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