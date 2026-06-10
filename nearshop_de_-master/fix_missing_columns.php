<?php
include '_db_connect.php';

echo "Attempting to fix database schema...<br>";

// 1. Add shop_id to order_table
$sql = "ALTER TABLE `order_table` ADD COLUMN `shop_id` INT(11) NOT NULL DEFAULT 0 AFTER `user_id`";
if (mysqli_query($conn, $sql)) {
    echo "Added shop_id column.<br>";
} else {
    echo "shop_id column might already exist or error: " . mysqli_error($conn) . "<br>";
}

// 2. Add delivery_partner_id
$sql = "ALTER TABLE `order_table` ADD COLUMN `delivery_partner_id` INT(11) DEFAULT NULL AFTER `shop_id`";
if (mysqli_query($conn, $sql)) {
    echo "Added delivery_partner_id column.<br>";
} else {
    echo "delivery_partner_id column might already exist or error: " . mysqli_error($conn) . "<br>";
}

// 3. Add order_status
$sql = "ALTER TABLE `order_table` ADD COLUMN `order_status` VARCHAR(50) DEFAULT 'Pending' AFTER `total_amount`";
if (mysqli_query($conn, $sql)) {
    echo "Added order_status column.<br>";
} else {
    echo "order_status column might already exist or error: " . mysqli_error($conn) . "<br>";
}

echo "Database fix attempt finished.";
?>
