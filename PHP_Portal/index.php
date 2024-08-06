<?php
// Simulate dynamic data
$status = "Active";
$currentClients = 4;
$sessionDownload = 2048; // in MB
$sessionDownloadLimit = 4096; // in MB
$sessionUpload = 1024; // in MB
$sessionUploadLimit = 2048; // in MB

// Calculate percentages
$downloadPercentage = ($sessionDownload / $sessionDownloadLimit) * 100;
$uploadPercentage = ($sessionUpload / $sessionUploadLimit) * 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberCafe Overview</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>CyberCafe</h1>
        </header>
        <div id="main" class="overview">
            <div class="item">
                <strong>Status</strong>
                <div><?php echo $status; ?></div>
            </div>
            <div class="item">
                <strong>Current Clients</strong>
                <div><?php echo $currentClients; ?></div>
            </div>
            <div class="item">
                <strong>Session ↓</strong>
                <div><?php echo $sessionDownload . "MB"; ?></div>
            </div>
            <div class="item">
                <strong>Session ↓ Limit</strong>
                <div><?php echo $sessionDownloadLimit . "MB"; ?></div>
            </div>
            <div class="item">
                <strong>Session ↓ %</strong>
                <div><?php echo round($downloadPercentage, 2) . "%"; ?></div>
            </div>
            <div class="item">
                <strong>Session ↑</strong>
                <div><?php echo $sessionUpload . "MB"; ?></div>
            </div>
            <div class="item">
                <strong>Session ↑ Limit</strong>
                <div><?php echo $sessionUploadLimit . "MB"; ?></div>
            </div>
            <div class="item">
                <strong>Session ↑ %</strong>
                <div><?php echo round($uploadPercentage, 2) . "%"; ?></div>
            </div>
            <div class="actions">
            </div>
            <a href="settings.php" class="button">
          <button onclick="changeLimits()">Add credits</button> 
</a>
<a href="limits.php" class="button">
          <button onclick="changeLimits()">Change Session Limits</button> 
</a>

<a href="blocklist.php" class="button">
          <button onclick="changeLimits()">Blocklist</button> 
</a>
                
<a href="users.php" class="button">
          <button onclick="changeLimits()">Check users</button> 
</a>

<a href="users1.php" class="button">
          <button onclick="changeLimits()">Session</button> 
</a>
            </div>
        </div>
    </div>
</body>
</html>
