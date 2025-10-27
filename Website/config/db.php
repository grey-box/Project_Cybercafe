<?php
declare(strict_types=1);

// Basic PDO helper for the CyberCafe SQLite database.

$dbFile = dirname(__DIR__, 2) . '/Database/CyberCafeTest.db';

if (!file_exists($dbFile)) {
    throw new RuntimeException('Database file not found at ' . $dbFile);
}

$pdo = new PDO('sqlite:' . $dbFile, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->exec('PRAGMA foreign_keys = ON');

return $pdo;

