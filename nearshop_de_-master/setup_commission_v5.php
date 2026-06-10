<?php
include '_db_connect.php';

function columnExists($conn, $table, $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return mysqli_num_rows($result) > 0;
}

echo "Starting Database Updates for Commission System...\n";

// Update order_table
$order_updates = [
    "commission_percentage" => "DECIMAL(5,2) DEFAULT 0",
    "commission_amount" => "DECIMAL(10,2) DEFAULT 0",
    "shop_earning" => "DECIMAL(10,2) DEFAULT 0"
];

foreach ($order_updates as $col => $def) {
    if (!columnExists($conn, 'order_table', $col)) {
        $sql = "ALTER TABLE `order_table` ADD COLUMN `$col` $def";
        if (mysqli_query($conn, $sql)) {
            echo "Added $col to order_table\n";
        } else {
            echo "Error adding $col to order_table: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "$col already exists in order_table\n";
    }
}

// Update shops table
$shop_updates = [
    "total_sales" => "DECIMAL(15,2) DEFAULT 0",
    "total_commission_paid" => "DECIMAL(15,2) DEFAULT 0",
    "total_earnings" => "DECIMAL(15,2) DEFAULT 0"
];

foreach ($shop_updates as $col => $def) {
    if (!columnExists($conn, 'shops', $col)) {
        $sql = "ALTER TABLE `shops` ADD COLUMN `$col` $def";
        if (mysqli_query($conn, $sql)) {
            echo "Added $col to shops\n";
        } else {
            echo "Error adding $col to shops: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "$col already exists in shops\n";
    }
}

echo "Database Schema Update Complete.\n";
?>
