<?php
require __DIR__ . "/../global.php";

if(isset($_COOKIE['session_id']))
{
	$db = global_createDatabaseObj();
	$response = $db->query("SELECT * FROM internet_sessions WHERE session_id='".$_COOKIE['session_id']."'");
	$responseArray = $response->fetchArray();
	$db->close();
	if($response)
	{
		global_removeInternetSession($responseArray['table_index']);
	}
	setcookie('session_id', '', time()-3600, '/');
	header('Refresh:0');
}
else
{
	header('Location: /');
}
?>