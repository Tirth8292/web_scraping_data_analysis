<?php
include '_db_connect.php';

// Disable foreign key checks temporarily just in case
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");

// 1. Drop if exists to ensure clean state
$sql_drop = "DROP TABLE IF EXISTS `user_addresses`";
mysqli_query($conn, $sql_drop);

// 2. Create user_addresses table WITHOUT Foreign Key
// using MyISAM if possible to match likely parent, or InnoDB without constraint
$sql_create = "CREATE TABLE `user_addresses` (
    `address_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `user_address` TEXT NOT NULL,
    `is_default` TINYINT DEFAULT 0
) ENGINE=InnoDB"; 

if (mysqli_query($conn, $sql_create)) {
    echo "Table 'user_addresses' created successfully.<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
    // Fallback?
}

// 3. data migration
$sql_fetch = "SELECT `user_id`, `user_address`, `user2_address`, `address_select` FROM `register_user`";
$result = mysqli_query($conn, $sql_fetch);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $u_id = $row['user_id'];
        $addr1 = $row['user_address'];
        $addr2 = $row['user2_address'];
        $selected = $row['address_select']; // 1 or 2

        // Insert Address 1
        if (!empty($addr1)) {
            $is_def = ($selected == 1) ? 1 : 0;
            $sql_ins1 = "INSERT INTO `user_addresses` (`user_id`, `user_address`, `is_default`) VALUES ('$u_id', '$addr1', '$is_def')";
            mysqli_query($conn, $sql_ins1);
        }

        // Insert Address 2
        if (!empty($addr2)) {
            $is_def = ($selected == 2) ? 1 : 0;
            // If only address 2 exists (rare), make it default if not already
            if (empty($addr1) && $is_def == 0) $is_def = 1;

            $sql_ins2 = "INSERT INTO `user_addresses` (`user_id`, `user_address`, `is_default`) VALUES ('$u_id', '$addr2', '$is_def')";
            mysqli_query($conn, $sql_ins2);
        }
    }
    echo "Data migration completed.<br>";
} else {
    echo "No users to migrate or error fetching.<br>";
}
?>
