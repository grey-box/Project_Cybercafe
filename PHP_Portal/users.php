<?php
// Simulate dynamic data
$status = "Active";
$macAddress = "76-93:c4:80:79:8c";
$ipAddress = "192.168.0.26";
$sessionDownload = 2048; // in MB
$sessionDownloadLimit = 4096; // in MB
$sessionUpload = 1024; // in MB
$sessionUploadLimit = 2048; // in MB
$fastCredits = 0; // in MB
$mediumCredits = 2048; // in MB
$slowCredits = 2048; // in MB

// Calculate percentages
$downloadPercentage = ($sessionDownload / $sessionDownloadLimit) * 100;
$uploadPercentage = ($sessionUpload / $sessionUploadLimit) * 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberCafe Dashboard</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <div class="container">
        <header>
            <h1>CyberCafe</h1>
        </header>
        <div class="device-info">
            <h2>KimsS23Ultra</h2>
            <div class="info">
                <strong>Status:</strong> <span><?php echo $status; ?></span>
            </div>
            <div class="info">
                <strong>MAC Address:</strong> <span><?php echo $macAddress; ?></span>
            </div>
            <div class="info">
                <strong>IP Address:</strong> <span><?php echo $ipAddress; ?></span>
            </div>
            <div class="session-data">
                <div class="info">
                    <strong>Session ↓:</strong> <span><?php echo $sessionDownload; ?>MB</span>
                </div>
                <div class="info">
                    <strong>Session ↓ Limit:</strong> <span><?php echo $sessionDownloadLimit; ?>MB</span>
                </div>
                <div class="info">
                    <strong>Session ↓ %:</strong> <span><?php echo round($downloadPercentage, 2); ?>%</span>
                </div>
                <div class="info">
                    <strong>Session ↑:</strong> <span><?php echo $sessionUpload; ?>MB</span>
                </div>
                <div class="info">
                    <strong>Session ↑ Limit:</strong> <span><?php echo $sessionUploadLimit; ?>MB</span>
                </div>
                <div class="info">
                    <strong>Session ↑ %:</strong> <span><?php echo round($uploadPercentage, 2); ?>%</span>
                </div>
            </div>
            <div class="credits">
                <h3>Credits</h3>
                <div class="info">
                    <strong>Fast Credits:</strong> <span><?php echo $fastCredits; ?>Mb</span>
                </div>
                <div class="info">
                    <strong>Medium Credits:</strong> <span><?php echo $mediumCredits; ?>MB</span>
                </div>
                <div class="info">
                    <strong>Slow Credits:</strong> <span><?php echo $slowCredits; ?>MB</span>
                </div>
            </div>
            <div class="actions">
            <a href="settings.php" class="button">
                <button onclick="addCredits()">Add Credits</button>
</a>
                
                <div class="actions">
                <a href="index.php" class="button">
                <button onclick="endSession()">End Session</button>
</a>
                <button onclick="blockMac()">Block MAC</button>
                <button onclick="enforceURLBlocklist()">Enforce URL Blocklist</button>
            </div>
        </div>
    </div>
</body>
</html>
