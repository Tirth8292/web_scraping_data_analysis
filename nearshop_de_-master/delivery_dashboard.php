<?php
session_start();
if (!isset($_SESSION["delivery_partner_id"])) {
    header("Location: delivery_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Dashboard</title>
    <link rel="stylesheet" href="css/style3.css">
</head>
<body>
    <div style="text-align: center; margin-top: 50px; font-family: 'Inter', sans-serif;">
        <h1>Welcome, Delivery Partner <?php echo $_SESSION["delivery_partner_name"]; ?>!</h1>
        <p>This is your dashboard. (Implementation in Phase 6)</p>
        <a href="delivery_login.php" style="color: blue;">Logout</a>
    </div>
</body>
</html>
