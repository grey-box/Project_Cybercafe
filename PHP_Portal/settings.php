<?php
// Simulated existing settings (These would typically come from a database or file)
$limits = [
    'download_limit' => '4096MB',
    'upload_limit' => '2048MB',
    'fast_tier' => '15Mb/s',
    'medium_tier' => '10Mb/s',
    'slow_tier' => '5Mb/s',
    'blocklist' => 'Work'
];

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $new_download_limit = isset($_POST['new_download_limit']) ? htmlspecialchars(trim($_POST['new_download_limit'])) : '';
    $new_upload_limit = isset($_POST['new_upload_limit']) ? htmlspecialchars(trim($_POST['new_upload_limit'])) : '';
    $fast_tier = isset($_POST['fast_tier']) ? htmlspecialchars(trim($_POST['fast_tier'])) : '';
    $medium_tier = isset($_POST['medium_tier']) ? htmlspecialchars(trim($_POST['medium_tier'])) : '';
    $slow_tier = isset($_POST['slow_tier']) ? htmlspecialchars(trim($_POST['slow_tier'])) : '';
    $blocklist = isset($_POST['blocklist']) ? htmlspecialchars(trim($_POST['blocklist'])) : '';

    // Update the settings array
    $limits['download_limit'] = is_numeric($new_download_limit) ? $new_download_limit . 'MB' : $limits['download_limit'];
    $limits['upload_limit'] = is_numeric($new_upload_limit) ? $new_upload_limit . 'MB' : $limits['upload_limit'];
    $limits['fast_tier'] = is_numeric($fast_tier) ? $fast_tier . 'Mb/s' : $limits['fast_tier'];
    $limits['medium_tier'] = is_numeric($medium_tier) ? $medium_tier . 'Mb/s' : $limits['medium_tier'];
    $limits['slow_tier'] = is_numeric($slow_tier) ? $slow_tier . 'Mb/s' : $limits['slow_tier'];
    $limits['blocklist'] = $blocklist;

    // Save the settings to a file
    file_put_contents('settings.json', json_encode($limits));

    // Optionally, display a success message
    echo '<p>Settings updated successfully!</p>';
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
        <h1>Default Settings</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label>Default ↓ Limit</label>
                <input type="text" name="new_download_limit" value="<?php echo htmlspecialchars($limits['download_limit']); ?>">
            </div>
            <div class="form-group">
                <label>Default ↑ Limit</label>
                <input type="text" name="new_upload_limit" value="<?php echo htmlspecialchars($limits['upload_limit']); ?>">
            </div>
            <div class="form-group">
                <label>Default Fast Tier</label>
                <input type="text" name="fast_tier" value="<?php echo htmlspecialchars($limits['fast_tier']); ?>">
            </div>
            <div class="form-group">
                <label>Default Med. Tier</label>
                <input type="text" name="medium_tier" value="<?php echo htmlspecialchars($limits['medium_tier']); ?>">
            </div>
            <div class="form-group">
                <label>Default Slow Tier</label>
                <input type="text" name="slow_tier" value="<?php echo htmlspecialchars($limits['slow_tier']); ?>">
            </div>
            <div class="form-group">
                <label>Default Blocklist</label>
                <select name="blocklist">
                    <option value="Work" <?php echo $limits['blocklist'] == 'Work' ? 'selected' : ''; ?>>Work</option>
                    <option value="Home" <?php echo $limits['blocklist'] == 'Home' ? 'selected' : ''; ?>>Home</option>
                </select>
            </div>
            <button type="submit">Save New Preferences</button>

            <a href="index.php" class="button">Back to main</a>
            <div class="actions">
        
        </form>
    </div>
</body>
</html>
