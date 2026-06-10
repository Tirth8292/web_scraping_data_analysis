<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "trial";
    $conn = mysqli_connect($servername, $username, $password, $database);
    if (!$conn) {
        echo "not connect to database";
    }
?>