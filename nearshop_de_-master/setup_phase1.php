<?php
include '_db_connect.php';

// 1. Rename table if old name exists and new name doesn't
$checkval = mysqli_query($conn, "SHOW TABLES LIKE 'register_user'");
if (mysqli_num_rows($checkval) > 0) {
    echo "Renaming register_user to users...<br>";
    mysqli_query($conn, "RENAME TABLE register_user TO users");
} else {
    echo "Table register_user not found (already renamed or missing). Checking for users...<br>";
}

// 2. Add columns if they don't exist
$check_cols = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
if (mysqli_num_rows($check_cols) == 0) {
    echo "Adding role column...<br>";
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN role ENUM('customer','super_admin','shop_owner','delivery_partner') DEFAULT 'customer'");
}

$check_cols2 = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'status'");
if (mysqli_num_rows($check_cols2) == 0) {
    echo "Adding status column...<br>";
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN status ENUM('active','pending','suspended') DEFAULT 'active'");
}

// 3. Migrate Admin
// Connect to admin DB to get data
$conn_admin = mysqli_connect($servername, $username, $password, "trial_admin");
if ($conn_admin) {
    echo "Connected to trial_admin...<br>";
    $res = mysqli_query($conn_admin, "SELECT * FROM admin_page_user");
    while ($row = mysqli_fetch_assoc($res)) {
        $admin_id = $row['admin_id'];
        // Check if exists in users
        $check_exists = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$admin_id'");
        if (mysqli_num_rows($check_exists) == 0) {
            echo "Migrating admin $admin_id...<br>";
            // We assume password is 'admin' or something since we couldn't see it, 
            // but for safety we will insert a record.
            // Note: Current admin system didn't check password. We will set a default 'admin123' if not found.
            // If the table has a password column, we use it. 
            // Let's try to see if 'admin_password' or similar exists in $row.
             
            $pass = isset($row['password']) ? $row['password'] : (isset($row['admin_password']) ? $row['admin_password'] : 'admin123');
            
            // Insert
            $sql_ins = "INSERT INTO users (f_name, l_name, user_id, email_id, user_address, user_password, role, status) 
                        VALUES ('Super', 'Admin', '$admin_id', '$admin_id@admin.com', 'Admin Office', '$pass', 'super_admin', 'active')";
            if (mysqli_query($conn, $sql_ins)) {
                echo "Admin migrated successfully.<br>";
            } else {
                echo "Error migrating admin: " . mysqli_error($conn) . "<br>";
            }
        } else {
            // Update role if exists
            mysqli_query($conn, "UPDATE users SET role='super_admin' WHERE user_id='$admin_id'");
            echo "Admin user already exists, updated role.<br>";
        }
    }
} else {
    echo "Could not connect to trial_admin DB.<br>";
}

echo "Phase 1 Setup Complete.";
?>
