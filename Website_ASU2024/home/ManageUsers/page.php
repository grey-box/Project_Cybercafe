<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
#error_reporting(E_ALL);
require __DIR__ . '/../../global.php';

function banUser($user_id)
{
	$db = global_createDatabaseObj();
	if($db->query("SELECT user_level FROM users WHERE user_id='".$user_id."'")->fetchArray()[0]!=0)
	{
		$db->exec("UPDATE users SET status='BANNED' WHERE user_id='".$user_id."'");
	}
	$db->close();
}
function unbanUser($user_id)
{
	$db = global_createDatabaseObj();
	$db->exec("UPDATE users SET status='ACTIVE' WHERE user_id='".$user_id."'");
	$db->close();
}
function disableUser($user_id)
{
	$db = global_createDatabaseObj();
	$db->exec("UPDATE users SET status='DISABLED' WHERE user_id='".$user_id."'");
	$db->close();
}
function enableUser($user_id)
{
	$db = global_createDatabaseObj();
	$db->exec("UPDATE users SET status='ACTIVE' WHERE user_id='".$user_id."'");
	$db->close();
}

function updateLaneID($user_id,$lane_id)
{
	$db = global_createDatabaseObj();
	$db->exec("UPDATE users SET lane_id=".$lane_id." WHERE user_id='".$user_id."'");
	$db->close();
}

function displayPage()
{
	$db = global_createDatabaseObj();
	$response = $db->query("SELECT * FROM users");
	$response2 = $db->query("SELECT COUNT(lane_id) FROM data_lanes");
	$responseArray2=$response2->fetchArray(SQLITE3_NUM);
	$numberOfDataLanes=(int)$responseArray2[0];
	$table_entries="";
	$i=0;
	while($responseArray=$response->fetchArray())
	{
		$user_id=$responseArray['user_id'];
		$dataLaneFormHTML="<form method='post'>
			<select name='laneID' id='landID'>
				<option value='".$responseArray['lane_id']."'>".$responseArray['lane_id']."</option>";
		for($x=0;$x<$numberOfDataLanes;$x++)
		{
			if($x==(int)$responseArray['lane_id'])
			{
				continue;
			}
			$dataLaneFormHTML=$dataLaneFormHTML."<option value='".$x."'>".$x."</option>";
		}
		$dataLaneFormHTML=$dataLaneFormHTML."</select><input type='hidden' name='action_userID' value='".$user_id."'><br><input type='submit' value='update'></form>";
		$response2=$db->query("SELECT session_tx,session_rx FROM internet_sessions WHERE user_id='".$user_id."'");
		if($responseArray2=$response2->fetchArray())
		{
			$session_rx=$responseArray2['session_rx'];
			$session_tx=$responseArray2['session_tx'];
		}
		else
		{
			$session_rx='0';
			$session_tx='0';
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
		$session_rx=round($session_rx/1000);
		$session_tx=round($session_tx/1000);
		$table_entries=$table_entries."<tr>
			<td>".$responseArray['user_id']."</td>
			<td>".$responseArray['username']."</td>
			<td>".$responseArray['name']."</td>
			<td>".$responseArray['email']."</td>
			<td>".$responseArray['phone']."</td>
			<td>".$responseArray['status']."</td>
			<td>".$session_rx." KB</td>
			<td>".$session_tx." KB</td>
			<td>".$dataLaneFormHTML."</td>
			<td>".$isAdmin."</td>";
		if($responseArray['status']=="BANNED")
		{
			$table_entries=$table_entries."
				<td></td>
				<td><form method='post'>
					<input type='submit' name='button' class='button' value='unban'>
					<input type='hidden' name='action_userID' value='".$user_id."'></form></td>
			</tr>";
		}
		else
		{
			if($responseArray['status']=="DISABLED")
			{
				if($isAdmin=='Yes')
				{
					$table_entries=$table_entries."
						<td><form method='post'>
							<input type='submit' name='button' class='button' value='enable'>
							<input type='hidden' name='action_userID' value='".$user_id."'></form></td>
						<td></td>
					</tr>";
				}
				else
				{
					$table_entries=$table_entries."
						<td><form method='post'>
							<input type='submit' name='button' class='button' value='enable'>
							<input type='hidden' name='action_userID' value='".$user_id."'></form></td>
						<td><form method='post'>
							<input type='submit' name='button' class='button' value='ban'>
							<input type='hidden' name='action_userID' value='".$user_id."'></form></td>
					</tr>";
				}
			}
			else
			{
				if($isAdmin=='Yes')
				{
					$table_entries=$table_entries."
						<td><form method='post'>
							<input type='submit' name='button' class='button' value='disable'>
							<input type='hidden' name='action_userID' value='".$user_id."'></form></td>
						<td></td>
					</tr>";
				}
				else
				{
					$table_entries=$table_entries."
						<td><form method='post'>
							<input type='submit' name='button' class='button' value='disable'>
							<input type='hidden' name='action_userID' value='".$user_id."'></form></td>
						<td><form method='post'>
							<input type='submit' name='button' class='button' value='ban'>
							<input type='hidden' name='action_userID' value='".$user_id."'></form></td>
					</tr>";
				}
			}
		}
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
	.button
	{
		text-align: center;
		display: inline-block;
		height: 30px;
		width: 80px;
		cursor: pointer;
	}
	</style>
	<body>
		<a><img src="/assets/CyberCafe_logo.png" width="100" height="100"></a>
		'.$GLOBALS['adminNavHTML'].'
		<h2>Manage Users<h2>
		<form method="post">
			<input type="submit" value="Refresh">
		</form>
		<table style="width:100%">
			<tr>
				<th style="width: 1%">User ID</th>
				<th style="width: 10%">Username</th>
				<th style="width: 11%">Name</th>
				<th style="width: 15%">Email</th>
				<th style="width: 11%">Phone</th>
				<th style="width: 7%">Status</th>
				<th style="width: 7%">Session RX</th>
				<th style="width: 7%">Session TX</th>
				<th style="width: 2%">Lane ID</th>
				<th style="width: 2%">Admin</th>
				<th style="width: 13%" colspan="2" style="border-style:none">Actions</th>
			</tr>
			'.$table_entries.'
		</table>
	</body>
	</html>
	';
	$db->close();
}

$userType = global_verifyUser($_COOKIE);
if($userType=='admin')
{
	if(array_key_exists('button', $_POST))
	{
		if($_POST['button']=='disable')
		{
			disableUser($_POST['action_userID']);
		}
		elseif($_POST['button']=='ban')
		{
			banUser($_POST['action_userID']);
		}
		elseif($_POST['button']=='enable')
		{
			enableUser($_POST['action_userID']);
		}
		elseif($_POST['button']=='unban')
		{
			unbanUser($_POST['action_userID']);
		}
	}
	if(array_key_exists('laneID', $_POST))
	{
		updateLaneID($_POST['action_userID'],(int)$_POST['laneID']);
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