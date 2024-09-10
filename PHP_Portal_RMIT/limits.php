<?php
// Simulated existing settings
$limits = [
    'download_limit' => '4096MB',
    'upload_limit' => '2048MB',
    'fast_tier' => '15Mb/s',
    'medium_tier' => '10Mb/s',
    'slow_tier' => '5Mb/s'
];

// This part would handle the POST request if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Processing form data
    $new_download_limit = isset($_POST['new_download_limit']) ? trim($_POST['new_download_limit']) : '';
    $new_upload_limit = isset($_POST['new_upload_limit']) ? trim($_POST['new_upload_limit']) : '';
    $fast_tier = isset($_POST['fast_tier']) ? trim($_POST['fast_tier']) : '';
    $medium_tier = isset($_POST['medium_tier']) ? trim($_POST['medium_tier']) : '';
    $slow_tier = isset($_POST['slow_tier']) ? trim($_POST['slow_tier']) : '';

    // Validate and sanitize inputs
    if (is_numeric($new_download_limit)) {
        $limits['download_limit'] = $new_download_limit . 'MB';
    }
    if (is_numeric($new_upload_limit)) {
        $limits['upload_limit'] = $new_upload_limit . 'MB';
    }
    if (is_numeric($fast_tier)) {
        $limits['fast_tier'] = $fast_tier . 'Mb/s';
    }
    if (is_numeric($medium_tier)) {
        $limits['medium_tier'] = $medium_tier . 'Mb/s';
    }
    if (is_numeric($slow_tier)) {
        $limits['slow_tier'] = $slow_tier . 'Mb/s';
    }

    // Optionally, you can save the updated limits to a database or file here

    // Redirect to the same page to see the new values or display a success message
    header('Location: limits.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Limits Configuration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Session Limits</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label>Session ↓ Limit</label>
                <div class="current-new">
                    <span><?php echo htmlspecialchars($limits['download_limit']); ?></span>
                    <input type="text" name="new_download_limit" placeholder="New Limit">
                </div>
            </div>
            <div class="form-group">
                <label>Session ↑ Limit</label>
                <div class="current-new">
                    <span><?php echo htmlspecialchars($limits['upload_limit']); ?></span>
                    <input type="text" name="new_upload_limit" placeholder="New Limit">
                </div>
            </div>
            <div class="form-group">
                <label>Fast Tier</label>
                <input type="text" name="fast_tier" placeholder="<?php echo htmlspecialchars($limits['fast_tier']); ?>">
            </div>
            <div class="form-group">
                <label>Medium Tier</label>
                <input type="text" name="medium_tier" placeholder="<?php echo htmlspecialchars($limits['medium_tier']); ?>">
            </div>
            <div class="form-group">
                <label>Slow Tier</label>
                <input type="text" name="slow_tier" placeholder="<?php echo htmlspecialchars($limits['slow_tier']); ?>">
            </div>
            <button type="submit">Save Session Limits</button>
            <div class="actions">
            <a href="index.php" class="button">Back to main</a>
        </form>
    </div>
</body>
</html>
