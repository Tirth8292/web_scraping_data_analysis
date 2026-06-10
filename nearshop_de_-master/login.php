<?php
session_start();
//echo $_SESSION['login_user_fname'];
include '_db_connect.php';

if (isset($_POST['otp-verify'])) {
    $enter_otp = $_POST['otp'];
    $real_otp = $_POST['otp-send'];
    if ($enter_otp == $real_otp) {
        $first_n = $_POST['f_n'];
        $last_n = $_POST['l_n'];
        $email_id = $_POST['eml'];
        $address = $_POST['addre'];
        $user_id = $_POST['usid'];
        $pass = $_POST['passwo'];
        $sql="INSERT INTO `users` (`f_name`, `l_name`, `user_id`, `email_id`, `user_address`, `user_password`, `time`, `role`, `status`) VALUES ('$first_n', '$last_n', '$user_id', '$email_id', '$address', '$pass', current_timestamp(), 'customer', 'active')";
        $res_re=mysqli_query($conn,$sql);
        $sql2="INSERT INTO `cart_table` (`user_id`, `cart_id`, `creat_at`) VALUES ('$user_id', NULL, current_timestamp())";
        $res_cart=mysqli_query($conn,$sql2);
        
         echo '<script>alert("Registration Successful! Please Login.");</script>';
    } else {
         echo '<script>alert("Invalid OTP! Registration Failed.");</script>';
    }
}
$user_id_home = null;
//var_dump($_POST);
if (isset($_POST['home_log'])) {
    $user_id_home = $_POST['user_id_home_page'];
    //echo $user_id_home;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Near Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="img/near.png" sizes="16x16 32x32">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6; /* Fallback */
            background-image: linear-gradient(135deg, #f3f4f6 0%, #eef2ff 100%);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .card {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-header h1 {
            margin: 0;
            color: #111827;
            font-size: 1.75rem;
            font-weight: 700;
        }
        .brand-header p {
            color: #6b7280;
            margin-top: 0.5rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
            font-size: 0.95rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            box-sizing: border-box;
            transition: all 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .btn-primary {
            width: 100%;
            padding: 0.875rem;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 0.5rem;
        }
        .btn-primary:hover {
            background: #4338ca;
        }
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #6b7280;
        }
        .register-link a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .divider {
            margin: 1.5rem 0;
            border-top: 1px solid #e5e7eb;
            position: relative;
            text-align: center;
        }
        .divider span {
            background: white;
            padding: 0 10px;
            color: #9ca3af;
            font-size: 0.875rem;
            position: relative;
            top: -10px;
        }
        .partner-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            margin-bottom: 0 !important;
            color: #6b7280;
            font-size: 0.9rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .partner-link:hover {
            color: #111827;
        }
    </style>
     <?php
    if(isset($_POST['logout'])){
        //session_destroy();
        unset($_SESSION["login_user_fname"]);
        unset($_SESSION["login_user_lname"]);
        unset($_SESSION["userid"]);
         echo '<script>
        alert("Logout Successful");
        </script>';
    }
    ?>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="brand-header">
                <h1>Welcome Back</h1>
                <p>Please enter your details to sign in</p>
            </div>
            
            <form action="index.php" method="post">
                <div class="form-group">
                    <label class="form-label">User ID</label>
                    <input type="text" name="login_id" class="form-control" placeholder="Enter your User ID" value="<?php echo isset($user_id_home) ? htmlspecialchars($user_id_home) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="login_password" class="form-control" placeholder="••••••••">
                </div>
                
                <button class="btn-primary" name="login_con" value="1">Sign In</button>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Register now</a>
            </div>
            
             <div class="divider">
                <span>OR</span>
            </div>
            
            <a href="partner_portal.php" class="partner-link">
                Are you a Shop Owner or Delivery Partner?
            </a>
        </div>
    </div>
</body>
</html>