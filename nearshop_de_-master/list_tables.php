<?php
include '_db_connect.php';

$sql = "SHOW TABLES";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_row($result)) {
    echo $row[0] . "\n";
}
?>
