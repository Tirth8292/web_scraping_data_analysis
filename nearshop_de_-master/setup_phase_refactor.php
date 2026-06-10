<?php
include '_db_connect.php';

$sql = file_get_contents('schema_phase_refactor.sql');

// Split by semicolon to run multiple queries
$queries = explode(';', $sql);

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        if (mysqli_query($conn, $query)) {
            echo "Executed: " . substr($query, 0, 50) . "...<br>";
        } else {
            // Ignore "Duplicate column name" error
            if (mysqli_errno($conn) == 1060) {
                 echo "Column already exists: " . substr($query, 0, 50) . "...<br>";
            } else {
                echo "Error executing: " . substr($query, 0, 50) . "... - " . mysqli_error($conn) . "<br>";
            }
        }
    }
}

echo "Database refactor complete.";
?>
