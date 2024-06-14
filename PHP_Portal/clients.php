<?php
// Simulate dynamic data for clients
$clients = [
    ['name' => 'JimmyiPhone12', 'download' => '25%', 'upload' => '12%'],
    ['name' => 'KimsS23Ultra', 'download' => '75%', 'upload' => '65%'],
    ['name' => 'JennysAndroid13', 'download' => '16%', 'upload' => '6%'],
    ['name' => 'iPhone3G', 'download' => '4%', 'upload' => '2%']
];

// Simulated data for blocklist
$blockedMacs = [
    ['mac' => '93:80:F:4:82:78:8c']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberCafe Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>CyberCafe</h1>
        </header>
        <div class="section clients">
            <h2>Clients</h2>
            <table>
                <thead>
                    <tr>
                        <th>HostName</th>
                        <th>Session Usage</th>
                        <th>Limit %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo $client['name']; ?></td>
                        <td><?php echo $client['download']; ?></td>
                        <td><?php echo $client['upload']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="section blocklist">
            <h2>Blocklist</h2>
            <?php foreach ($blockedMacs as $mac): ?>
            <div class="mac-address">
                <span><?php echo $mac['mac']; ?></span>
                <button>Unblock</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
