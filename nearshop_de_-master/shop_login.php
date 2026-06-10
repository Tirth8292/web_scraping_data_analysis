<?php
session_start();
include '_db_connect.php';

$error_msg = "";

if (isset($_POST['shop_logout'])) {
    unset($_SESSION['shop_userid']);
    unset($_SESSION['shop_header_name']);
    unset($_SESSION['shop_role']);
    header("Location: shop_login.php");
    exit();
}

if (isset($_POST['shop_login_btn'])) {
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];

    // Check credentials and role
    $stmt = mysqli_prepare($conn, "SELECT * FROM `users` WHERE `user_id` = ? AND `role` = 'shop_owner'");
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verify Password (Plaintext for legacy project consistency, upgrade later if user asks)
        if ($password === $row['user_password']) {
            // Check Status
            if ($row['status'] === 'active') {
                // Use distinct session keys for Shop Owner to allow dual login
                $_SESSION["shop_userid"] = $user_id; 
                $_SESSION["shop_header_name"] = $row['f_name'];
                $_SESSION["shop_role"] = 'shop_owner';
                
                // Remove generic updates to prevent overwriting Customer session
                // $_SESSION["userid"] = $user_id; 
                
                // Direct redirect
                header("Location: shop_dashboard.php");
                exit();
            } else if ($row['status'] === 'pending') {
                $error_msg = "Your account is pending approval by the Admin.";
            } else {
                $error_msg = "Your account has been suspended.";
            }
        } else {
            $error_msg = "Invalid Password.";
        }
    } else {
        $error_msg = "User not found or not a Shop Owner.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Owner Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
        .login-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .login-card h2 {
            margin-top: 0;
            margin-bottom: 0.5rem;
            color: #111827;
            font-size: 1.5rem;
            text-align: center;
        }
        .login-card p {
            text-align: center;
            color: #6b7280;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
            font-size: 0.9rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-family: inherit;
            box-sizing: border-box;
            font-size: 1rem;
        }
        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 6px;
            background: #4f46e5;
            color: white;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #4338ca;
        }
        .btn-outline {
            background: white;
            color: #4f46e5;
            border: 1px solid #4f46e5;
            margin-top: 10px;
        }
        .btn-outline:hover {
            background: #eef2ff;
        }
        .error-msg {
            background: #fee2e2;
            color: #b91c1c;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <h2>Shop Partner Login</h2>
            <p>Manage your store, products, and orders</p>
            
            <?php if(!empty($error_msg)): ?>
                <div class="error-msg"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form action="shop_login.php" method="post">
                <div class="form-group">
                    <label class="form-label">User ID (or Phone)</label>
                    <input type="text" name="user_id" class="form-control" required placeholder="Enter your User ID">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>

                <button class="btn" name="shop_login_btn" value="1">Login to Dashboard</button>
            </form>
            
            <form action="shop_register.php" method="get">
                <button class="btn btn-outline">Register New Shop</button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="partner_portal.php" style="color: #6b7280; text-decoration: none; font-size: 0.9rem;">Back to Portal</a>
            </div>
        </div>
    </div>
</body>
</html>
