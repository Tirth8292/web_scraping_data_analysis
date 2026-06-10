<?php
include '_db_connect.php';

// Execute Schema Phase 3.1
$file_content = file_get_contents('schema_phase3_1.sql');
$queries = explode(';', $file_content);

foreach ($queries as $query) {
    if (trim($query)) {
        if (mysqli_query($conn, $query)) {
            echo "Query executed successfully: " . substr($query, 0, 50) . "...<br>";
        } else {
            // Ignore 'Duplicate column name' errors mostly
            echo "Error executing query: " . mysqli_error($conn) . " (Query: " . substr($query, 0, 50) . "...)<br>";
        }
    }
}
echo "Phase 3.1 DB Update Complete.";
?>
