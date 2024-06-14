<?php

function executeSQLFile($pathToFile, $dbPath) {

    $db = new SQLite3($dbPath);

    try {
        // Read the entire SQL file
        $sql = file_get_contents($pathToFile);
        if ($sql === false) {
            throw new Exception("Unable to read the SQL file.");
        }

        // Execute the queries in the SQL file
        $db->exec('BEGIN;');
        $db->exec($sql);
        $db->exec('COMMIT;');
        echo $pathToFile . " ran successfully.\n";
    } catch (Exception $e) {
        $db->exec('ROLLBACK;');
        echo "Error running file " . $pathToFile . ": " . $e->getMessage() . "\n";
    } finally {
        $db->close();
    }
}


?>
