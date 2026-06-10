<?php
session_start();
// Empty file as placeholder for settings logic
include '_db_connect.php';

if (!isset($_SESSION["shop_userid"])) {
    header("Location: shop_login.php");
    exit();
}

$message = "";

// Handle Password Change
if (isset($_POST['change_pw'])) {
    $userid = $_SESSION["shop_userid"];
    $curr_pw = $_POST['curr_pw'];
    $new_pw = $_POST['new_pw'];
    $conf_pw = $_POST['conf_pw'];

    $res = mysqli_query($conn, "SELECT user_password FROM users WHERE user_id = '$userid'");
    $row = mysqli_fetch_assoc($res);

    if ($row['user_password'] === $curr_pw) {
        if ($new_pw === $conf_pw) {
            mysqli_query($conn, "UPDATE users SET user_password = '$new_pw' WHERE user_id = '$userid'");
            $message = "Password updated successfully.";
            $msg_type = "success";
        } else {
            $message = "New passwords do not match.";
            $msg_type = "error";
        }
    } else {
        $message = "Current password is incorrect.";
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop Settings - Shop Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <?php include 'sidebar_shop.php'; ?>

    <div class="main-content">
        <div class="header-title">
            <h1>Settings</h1>
        </div>

        <?php if($message): ?>
            <div style="padding: 1rem; background: <?php echo $msg_type == 'error' ? '#fee2e2' : '#dcfce7'; ?>; 
                        color: <?php echo $msg_type == 'error' ? '#b91c1c' : '#15803d'; ?>; 
                        border-radius: 6px; margin-bottom: 1rem;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 600px;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 1rem; margin-bottom: 1.5rem;">Change Password</h3>
            <form method="post">
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="curr_pw" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_pw" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="conf_pw" class="form-control" required>
                </div>
                <button type="submit" name="change_pw" class="btn-primary">Update Password</button>
            </form>
        </div>
        
        <div class="card" style="max-width: 600px; margin-top: 2rem;">
            <h3 style="margin-top: 0; margin-bottom: 1rem;">Shop Details</h3>
             <p style="color: #6b7280; margin-bottom: 1rem;">To update your shop address or details, please contact the Super Admin.</p>
             <a href="mailto:admin@support.com" style="color: var(--primary); text-decoration: none;">Contact Support</a>
        </div>
    </div>

</body>
</html>
