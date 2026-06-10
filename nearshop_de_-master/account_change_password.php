<?php
session_start();
date_default_timezone_set("Asia/Kolkata");
include '_db_connect.php';
$user_id = $_SESSION["userid"];
$user_fname = $_SESSION["login_user_fname"];
$user_lname = $_SESSION["login_user_lname"];


echo '<script>
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }
            </script>';


if (isset($_POST['update_password']) && isset($_POST['new_password']) && isset($_POST['confirm_new_password'])) {
    if ($_POST['new_password'] == $_POST['confirm_new_password']) {
        $new_pass = $_POST['new_password'];
        $sql63 = "UPDATE `users` SET `user_password`='$new_pass' WHERE `user_id` = '$user_id'";
        $rel63 = mysqli_query($conn,$sql63);
        if ($rel63) {

            $sql64 = "SELECT  `email_id` FROM `users` WHERE `user_id` = '$user_id'";
            $rel64 = mysqli_query($conn,$sql64);
            $email_row = mysqli_fetch_assoc($rel64);
            $email_id = $email_row['email_id'];
            $to = $email_id;
            $sub = "Password Change Confirmation";
            $message = "This is to confirm that the password for your account has been successfully changed.";
            
            $result = mail($to,$sub,$message);
        }
        
    }
}





?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="css/account_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="img/near.png" sizes="16x16 32x32">
</head>

<body>
    <header id="header-sho">
        <div class="header-container">
            <div id="left-side">
                <a href="index.php">
                    <img src="img/near.png" alt="Logo">
                </a>
            </div>
            <div id="mid-side">
                <form action="index.php" method="post">
                    <input type="search" placeholder="Search for product..." name="search">
                    <button name="ser" value="1">Search</button>
                </form>
            </div>
            <div id="right-side">
                <nav>
                    <div class="nav-items">
                        <a href="index.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-door-fill" viewBox="0 0 16 16">
                                <path d="M6.5 14.5v-3.505c0-.245.25-.495.5-.495h2c.25 0 .5.25.5.495v3.505a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5Z"/>
                            </svg>
                            Home
                        </a>
                    </div>
                    <div class="nav-items">
                        <a href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-square-fill" viewBox="0 0 16 16">
                                <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm8.93 4.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM8 5.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" />
                            </svg>
                            About
                        </a>
                    </div>
                    <div class="nav-items">
                        <a href="user_cart.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-basket" viewBox="0 0 16 16">
                                <path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9H2zM1 7v1h14V7H1zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 5.5z" />
                            </svg>
                            Cart
                        </a>
                    </div>
                    <div class="nav-items">
                        <?php
                        if (isset($_SESSION['login_user_fname'])) {
                            echo '<a href="account_page.php">';
                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                            </svg>';
                            echo $_SESSION['login_user_fname'] . " " . $_SESSION['login_user_lname'];
                            echo '</a>';
                        } else {
                            echo '<a href="login.php">';
                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                            </svg>';
                            echo "Login";
                            echo '</a>';
                        }
                        ?>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <div class="side-1">
        <div class="content-box">
            <h3>Account</h3>
            <hr>
            <div class="content-items-oper"><a href="account_page.php">
                    <i class="bi bi-window-stack"></i> My details </a>
            </div>
            <hr>
            <div class="content-items-oper"><a href="account_page_address.php">
                    <i class="bi bi-shop"></i> My Address </a>
            </div>
            <hr>
            <div class="content-items-oper"><a href="account_order.php">
                    <i class="bi bi-pencil-square"></i> Orders </a>
            </div>
            <hr>
            <div class="content-items-oper"><a href="account_order_history.php">
                    <i class="bi bi-card-checklist"></i> Order History </a>
            </div>
            <hr>
            <div class="content-items-oper"><a href="account_change_password.php">
                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20" viewBox="0 0 50 50">
                        <path d="M 25 2 C 17.832484 2 12 7.8324839 12 15 L 12 21 L 8 21 C 6.3550302 21 5 22.35503 5 24 L 5 47 C 5 48.64497 6.3550302 50 8 50 L 42 50 C 43.64497 50 45 48.64497 45 47 L 45 24 C 45 22.35503 43.64497 21 42 21 L 38 21 L 38 15 C 38 7.8324839 32.167516 2 25 2 z M 25 4 C 31.086484 4 36 8.9135161 36 15 L 36 21 L 14 21 L 14 15 C 14 8.9135161 18.913516 4 25 4 z M 8 23 L 42 23 C 42.56503 23 43 23.43497 43 24 L 43 47 C 43 47.56503 42.56503 48 42 48 L 8 48 C 7.4349698 48 7 47.56503 7 47 L 7 24 C 7 23.43497 7.4349698 23 8 23 z M 13 34 A 2 2 0 0 0 11 36 A 2 2 0 0 0 13 38 A 2 2 0 0 0 15 36 A 2 2 0 0 0 13 34 z M 21 34 A 2 2 0 0 0 19 36 A 2 2 0 0 0 21 38 A 2 2 0 0 0 23 36 A 2 2 0 0 0 21 34 z M 29 34 A 2 2 0 0 0 27 36 A 2 2 0 0 0 29 38 A 2 2 0 0 0 31 36 A 2 2 0 0 0 29 34 z M 37 34 A 2 2 0 0 0 35 36 A 2 2 0 0 0 37 38 A 2 2 0 0 0 39 36 A 2 2 0 0 0 37 34 z"></path>
                    </svg> Change Password </a>
            </div>
            <hr>
            <div class="content-items-oper">
                <a href="#">
                    <i class="bi bi-box-arrow-left"></i>
                    <form action="login.php" method="post" style="display: contents;">
                        <button name="logout" value="1" style="background: transparent; font-size: 1rem; border: transparent; color: inherit; cursor: pointer;">
                            Logout
                        </button>
                    </form>
                </a>
            </div>
            <hr>
        </div>
    </div>
    <div class="side-2">
        <h2 class="head2" style="font-family: 'Kanit', sans-serif;">
            My Details
        </h2>
        <hr>
        <form action="account_change_password.php" method="post">
            <div class="ad-m">
                <div class="form-grid">
                    <div class="in-f full-width">
                        <label class="la-in-f">Enter New Password</label>
                        <div class="in">
                            <input type="password" name="new_password" placeholder="Enter New Password" required>
                        </div>
                    </div>

                    <div class="in-f full-width">
                        <label class="la-in-f">Confirm New Password</label>
                        <div class="in">
                            <input type="password" name="confirm_new_password" placeholder="Confirm New Password" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button class="btn" name="update_password" value="1">Update Password</button>
                </div>
            </div>
        </form>

    </div>

</body>

</html>