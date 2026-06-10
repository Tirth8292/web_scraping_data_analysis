<?php

session_start();
include '_db_connect.php';
date_default_timezone_set("Asia/Kolkata");
if (isset($_POST['btn_confirm_buy'])) {
    $order_total = $_POST['order_total_pr'];
    $user_id = $_SESSION['userid'];
    $user_cartid = $_POST['cart_id_confirm'];

    // Determine Shop ID from cart items
    $shop_query = "SELECT p.shop_id FROM cart_items ci JOIN product p ON ci.pr_id = p.pr_id WHERE ci.cart_id = '$user_cartid' LIMIT 1";
    $shop_res = mysqli_query($conn, $shop_query);
    $order_shop_id = 0;
    if ($shop_row = mysqli_fetch_assoc($shop_res)) {
        $order_shop_id = $shop_row['shop_id'];
    }

    $time_s = date('Y-m-d H:i:s');
    // Using default for delivery_partner_id (NULL) and order_status ('Pending' - if DB default is set, otherwise specify)
    $sql13 = "INSERT INTO `order_table` (`order_id`, `user_id`, `shop_id`, `order_date`, `total_amount`, `order_status`) VALUES (NULL, '$user_id', '$order_shop_id', '$time_s', '$order_total', 'Pending')";
    $orderid_result = mysqli_query($conn, $sql13);

    $sql14 = "SELECT * FROM `order_table` WHERE `user_id` = '$user_id' AND `order_date` = '$time_s'";
    $or_result = mysqli_query($conn, $sql14);




    $order_confirmation = false;

    while ($row_pr = mysqli_fetch_assoc($or_result)) {
        $user_cartid = $_POST['cart_id_confirm'];
        $sql9 = "SELECT * FROM `cart_items` WHERE `cart_id` = '$user_cartid'";
        $result_cart_product = mysqli_query($conn, $sql9);
        if (mysqli_num_rows($result_cart_product) > 0) {
            //echo "count hello pr";
            while ($pr_row = mysqli_fetch_assoc($result_cart_product)) {
                $product_id = $pr_row['pr_id'];
                $sql10 = "SELECT * FROM `product` WHERE `pr_id` = '$product_id'";
                $result_product = mysqli_query($conn, $sql10);
                //echo "count hello";
                while ($product_row = mysqli_fetch_assoc($result_product)) {



                    $or_id = $row_pr['order_id'];
                    $pr_quantity = $pr_row['quantity'];
                    $pr_unit_pr = $product_row['pr_pr'];
                    $sql15 = "INSERT INTO `order_items` (`order_id`, `pr_id`, `quantity`, `unit_pr`) VALUES ('$or_id', '$product_id', '$pr_quantity', '$pr_unit_pr')";

                    $order_items = mysqli_query($conn, $sql15);

                    
                    $product_up_quantity = $product_row['pr_qu'] - $pr_quantity;
                    $sql21 = "UPDATE `product` SET `pr_qu` = '$product_up_quantity' WHERE `pr_id` = '$product_id'";
                    //echo $product_up_quantity . "<br>";
                    //echo $product_row['pr_qu'] . "<br>";
                    //echo $pr_quantity . "<br>";
                    $qu_up_result = mysqli_query($conn,$sql21);
                    if ($qu_up_result) {
                        //echo "hello";
                    }
                    
                    $order_confirmation = true;
                }
            }
        }
    }
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style3.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="img/near.png" sizes="16x16 32x32">
    <title>Confirmation</title>
    <style>
        body {
            background-image: url(img/login-re.png);
            /* background: cover ; */
            background-size: 100%;
            background-repeat: no-repeat;
            /* background-position: center; */
        }
    </style>
</head>

<body>
    <div class="container" id="reg2">

        <?php

        if ($order_confirmation) {
            echo '<div class="login-fo">
                        <h2>Thank you for your order</h2>
                        <div id="form-input-otp">
                            <form action="login.php" method="post" style="width:100%;">

                                <div class="content-form">
                                    <div class="lo-lab" id="otp-ti">
                                        <label style="
                                    font-size: 27px;
                                ">
                                            We are getting started on your order, Confirmation email has been send to you.
                                        </label>
                                    </div>
                                </div>

                                <div class="content-form" id="btn-l-r">
                                <a href="index.php" style="
                                text-decoration: underline;
                                color: black;
                                border: 2px solid white;
                                font-size: 30px;
                                padding: 5px 60px;
                            ">
                                    Home
                                </a>
                                </div>
                        </div>
                    </div>';

                    $sql16 = "SELECT * FROM `users` WHERE `user_id` = '$user_id'";
                    $email_re = mysqli_query($conn, $sql16);
                    while ($user_row = mysqli_fetch_assoc($email_re)) {
                        
                        $email_id = $user_row['email_id'];

                        $to = $email_id;
                        $sub = "Order Confirmation";
                        $message = "Your Order is confirm and delivered by tomorrow";
                        
                        $result = mail($to,$sub,$message);
                    }

                    $sql17 = "DELETE FROM `cart_items` WHERE `cart_id`='$user_cartid'";
                    $del_cart_items = mysqli_query($conn,$sql17);



        }
        else {
            echo '<div class="login-fo">
                        <h2>Your Order is not conform</h2>
                    </div>';
        }

        ?>

    </div>
</body>

</html>