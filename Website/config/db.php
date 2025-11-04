<?php
declare(strict_types=1);

// Basic PDO helper for the CyberCafe SQLite database.

$dbFile = dirname(__DIR__, 2) . '/Database/CyberCafeTest.db';

if (!file_exists($dbFile)) {
    $pdo = new PDO('sqlite:' . $dbFile);
    $schema = file_get_contents(dirname(__DIR__, 2) . '/Database/schema.sql');
    $pdo->exec($schema);
    echo "Database created and schema loaded.\n";
}
else
{
    $pdo = new PDO('sqlite:' . $dbFile, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
}

$pdo->exec('PRAGMA foreign_keys = ON');

return $pdo;

