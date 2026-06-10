<?php
include '_db_connect.php';

if (isset($_POST['reg_user'])) {
    
    $first_n = $_POST['f_name'];
    $last_n = $_POST['l_name'];
    $email_id = $_POST['email'];
    $address = $_POST['address'];
    $user_id = $_POST['userid'];
    $pass = $_POST['password'];
    $pass_con = $_POST['con_password'];


    $sql4 = "SELECT * FROM `users`";
    $result_reg = mysqli_query($conn, $sql4);
    while ($row = mysqli_fetch_assoc($result_reg)){
        
        if ($row['user_id'] == $user_id) {
            echo '<script>
            window.location.replace("register.php");
            alert("Username already taken. Please choose another.");
            </script>';
            exit();
        }
    } 
    if ($pass == $pass_con) {
        
        include 'otp_gen.php';
        
    } else {
         echo '<script>
            window.location.replace("register.php");
            alert("Passwords do not match.");
            </script>';
            exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Near Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="img/near.png" sizes="16x16 32x32">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-image: linear-gradient(135deg, #f3f4f6 0%, #eef2ff 100%);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .otp-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        .card {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .icon {
            font-size: 3rem;
            color: #4f46e5;
            margin-bottom: 1rem;
        }
        .header {
            margin-bottom: 2rem;
        }
        .header h2 {
            margin: 0;
            color: #111827;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .header p {
            color: #6b7280;
            margin-top: 0.5rem;
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
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
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1.25rem;
            box-sizing: border-box;
            transition: all 0.2s;
            text-align: center;
            letter-spacing: 2px;
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
        }
        .btn-primary:hover {
            background: #4338ca;
        }
        .email-highlight {
            font-weight: 600;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <div class="card">
            <div class="icon">
                <i class="bi bi-envelope-check"></i>
            </div>
            <div class="header">
                <h2>OTP Verification</h2>
                <p>We've sent a code to <br><span class="email-highlight"><?php echo isset($email_id) ? htmlspecialchars($email_id) : 'your email'; ?></span></p>
                <small style="color: red;">(OTP: <?php echo isset($otp) ? $otp : ''; ?>)</small>
            </div>
            
            <form action="login.php" method="post">
                <div class="form-group">
                    <label class="form-label" style="text-align:center;">Enter 4-digit Code</label>
                    <input type="text" name="otp" class="form-control" maxlength="4" placeholder="••••" required autofocus>
                </div>

                <!-- Hidden Inputs to pass data -->
                <input type="hidden" name="f_n" value="<?php echo htmlspecialchars($first_n); ?>">
                <input type="hidden" name="l_n" value="<?php echo htmlspecialchars($last_n); ?>">
                <input type="hidden" name="eml" value="<?php echo htmlspecialchars($email_id); ?>">
                <input type="hidden" name="addre" value="<?php echo htmlspecialchars($address); ?>">
                <input type="hidden" name="usid" value="<?php echo htmlspecialchars($user_id); ?>">
                <input type="hidden" name="passwo" value="<?php echo htmlspecialchars($pass); ?>">
                <input type="hidden" name="otp-send" value="<?php echo htmlspecialchars($otp); ?>">

                <button class="btn-primary" name="otp-verify" value="1">Verify & Register</button>
            </form>
        </div>
    </div>
</body>
</html>