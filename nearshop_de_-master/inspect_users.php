<?php
include '_db_connect.php';

echo "Table: users\n";
$result = mysqli_query($conn, "DESCRIBE users");
while ($row = mysqli_fetch_assoc($result)) {
    print_r($row);
}

echo "\nSample Users:\n";
$result = mysqli_query($conn, "SELECT * FROM users LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    print_r($row);
}
?>
