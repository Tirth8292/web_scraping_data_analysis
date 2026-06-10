<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "trial_admin";
    $conn1 = mysqli_connect($servername, $username, $password, $database);
    if (!$conn1) {
        echo "not connect to database";
    }
?>