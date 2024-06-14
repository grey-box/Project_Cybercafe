<?php
session_start();

// Simulated data for blocklists stored in session
if (!isset($_SESSION['blocklists'])) {
    $_SESSION['blocklists'] = [
        'School' => ['youtube.com', 'facebook.com'],
        'Work' => ['xxx.com', 'xrated.com']
    ];
}
$blocklists = &$_SESSION['blocklists'];

// Mark which blocklist is the default stored in session
if (!isset($_SESSION['defaultBlocklist'])) {
    $_SESSION['defaultBlocklist'] = 'School';
}
$defaultBlocklist = &$_SESSION['defaultBlocklist'];

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $newUrl = trim($_POST['new_url']);
        $blocklistName = $_POST['blocklist_name'];
        if (!empty($newUrl) && isset($blocklists[$blocklistName])) {
            // Add the new URL to the corresponding blocklist
            $blocklists[$blocklistName][] = $newUrl;
        }
    } elseif (isset($_POST['set_default'])) {
        $blocklistName = $_POST['blocklist_name'];
        if (isset($blocklists[$blocklistName])) {
            $defaultBlocklist = $blocklistName;
        }
    }

    // Redirect to avoid form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL BlockLists</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>URL BlockLists</h1>
        <?php foreach ($blocklists as $name => $urls): ?>
            <div class="blocklist">
                <h2><?php echo htmlspecialchars($name); ?> (<?php echo $name == $defaultBlocklist ? 'Default' : ''; ?>)</h2>
                <ul>
                    <?php foreach ($urls as $url): ?>
                        <li><?php echo htmlspecialchars($url); ?></li>
                    <?php endforeach; ?>
                </ul>
                <form method="POST" action="">
                    <input type="hidden" name="blocklist_name" value="<?php echo htmlspecialchars($name); ?>" />
                    <input type="text" name="new_url" placeholder="Add URL" />
                    <button type="submit" name="add">Add</button>
                    <?php if ($name !== $defaultBlocklist): ?>
                        <button type="submit" name="set_default">Set Default</button>
                    <?php endif; ?>
                </form>
                <div class="actions">
            <a href="index.php" class="button">Back to main</a>
            </div>
        <?php endforeach; ?>
    </div>
    
</body>
</html>
