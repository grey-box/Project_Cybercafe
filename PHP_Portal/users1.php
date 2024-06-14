<?php
// Simulated dynamic data
$status = "Active";
$currentClients = 4;
$downloadSession = 2048; // in MB
$downloadLimit = 4096; // in MB
$uploadSession = 1024; // in MB
$uploadLimit = 2048; // in MB
$downloadPercentage = ($downloadSession / $downloadLimit) * 100;
$uploadPercentage = ($uploadSession / $uploadLimit) * 100;

// Speed tiers
$fastTier = '15Mb/s';
$mediumTier = '10Mb/s';
$slowTier = '5Mb/s';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberCafe Session</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>CyberCafe</h1>
        </header>
        <div class="section">
            <h2>Session</h2>
            <p><strong>CyberCafe Service:</strong> <?php echo $status; ?></p>
            <p><strong>Current Clients:</strong> <?php echo $currentClients; ?></p>
            <p><strong>Session ↓:</strong> <?php echo $downloadSession . 'MB'; ?></p>
            <p><strong>Session ↓ Limit:</strong> <?php echo $downloadLimit . 'MB'; ?></p>
            <p><strong>Session ↓ %:</strong> <?php echo round($downloadPercentage) . '%'; ?></p>
            <p><strong>Session ↑:</strong> <?php echo $uploadSession . 'MB'; ?></p>
            <p><strong>Session ↑ Limit:</strong> <?php echo $uploadLimit . 'MB'; ?></p>
            <p><strong>Session ↑ %:</strong> <?php echo round($uploadPercentage) . '%'; ?></p>
            <p><strong>Fast Tier:</strong> <?php echo $fastTier; ?></p>
            <p><strong>Medium Tier:</strong> <?php echo $mediumTier; ?></p>
            <p><strong>Slow Tier:</strong> <?php echo $slowTier; ?></p>
        </div>
        <a href="limits.php" class="button">
          <button onclick="changeLimits()">Change Session Limits</button> 
</a>

<a href="settings.php" class="button">
<button onclick="changeLimits()">Add credits</button> 

<a href="blocklist.php" class="button">
<button onclick="changeLimits()">Blocklist</button> 

<a href="users.php" class="button">
<button onclick="changeLimits()">Check users</button> 

<a href="index.php" class="button">
<button onclick="changeLimits()">Back to main</button> 
        <div class="actions">
             
             
            </div>
        </div>
    </div>
</body>
</html>
