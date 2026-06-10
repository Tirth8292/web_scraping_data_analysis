<?php
session_start();
include '_db_connect.php';

if(isset($_POST['log_out_admin'])){
    // echo '<script>alert("Confirm to logout");</script>'; // Removed annoying alert, just logout
    unset($_SESSION["admin_lg_id"]);
    unset($_SESSION["admin_type"]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SuperAdmin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #111827; /* Dark background for Admin */
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        .card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .header {
            text-align: center;
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
            font-size: 0.9rem;
        }
        .logo-icon {
            font-size: 2.5rem;
            color: #4f46e5;
            margin-bottom: 1rem;
            display: inline-block;
        }
        .form-group {
            margin-bottom: 1.25rem;
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
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.95rem;
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
            padding: 0.75rem;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 1rem;
        }
        .btn-primary:hover {
            background: #4338ca;
        }
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back-link a:hover {
            color: #111827;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="header">
                <div class="logo-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h2>Admin Login</h2>
                <p>Secure access for platform administrators</p>
            </div>
            
            <form action="admin_home.php" method="post">
                <div class="form-group">
                    <label class="form-label">Admin ID</label>
                    <input type="text" name="admin_login_id" class="form-control" placeholder="Enter Admin ID" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="admin_login_password" class="form-control" placeholder="••••••••" required>
                </div>
                
                <button class="btn-primary" name="admin_login_con" value="1">Login</button>
            </form>

            <!-- <div class="back-link">
                <a href="login.php">Back to Main Site</a>
            </div> -->
        </div>
    </div>
</body>
</html>