<?php
session_start();
include '_db_connect.php';

$user_id = $_SESSION['userid'];

// Handle Address Change from Checkout
if (isset($_POST['set_checkout_address'])) {
    $sel_id = $_POST['set_checkout_address'];
    // Reset defaults
    mysqli_query($conn, "UPDATE `user_addresses` SET `is_default` = 0 WHERE `user_id` = '$user_id'");
    // Set new default
    mysqli_query($conn, "UPDATE `user_addresses` SET `is_default` = 1 WHERE `address_id` = '$sel_id' AND `user_id` = '$user_id'");
    // Sync to register_user
    mysqli_query($conn, "UPDATE `users` SET `user_address` = (SELECT `user_address` FROM `user_addresses` WHERE `address_id` = '$sel_id'), `address_select` = 1 WHERE `user_id` = '$user_id'");
}

$sql12 = "SELECT * FROM `users` WHERE `user_id` = '$user_id'";
$result_user = mysqli_query($conn, $sql12);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="img/near.png" sizes="16x16 32x32">
    <style>
        /* Simple Modal for Address Selection */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .address-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-change {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
    </style>
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
                        <a href="user_cart.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-basket" viewBox="0 0 16 16">
                                <path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9H2zM1 7v1h14V7H1zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5z" />
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
                            }
                            ?>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <div class="checkout-det">
        <div id="order-products" class="ch-de">
            <h2>1. Products</h2>
            <div class="ch-pr1">
                <div class="ch-img"><h3></h3></div>
                <div class="ch-name1"><h3>Product name</h3></div>
                <div class="ch-pr-price1"><h3>Price</h3></div>
                <div class="ch-pr-price2"><h3>Quantity</h3></div>
                <div class="ch-pr-price1"><h3>Subtotal</h3></div>
            </div>
            <hr>
            <?php
            if (isset($_POST['buy_cart_item_now'])) {
                $user_cartid = $_POST['cart_id_buy'];
                $sql9 = "SELECT * FROM `cart_items` WHERE `cart_id` = '$user_cartid'";
                $result_cart_product = mysqli_query($conn, $sql9);
                if (mysqli_num_rows($result_cart_product) > 0) {
                    $count = 1;
                    $total = 0;
                    while ($pr_row = mysqli_fetch_assoc($result_cart_product)) {
                        $product_id = $pr_row['pr_id'];
                        $sql10 = "SELECT * FROM `product` WHERE `pr_id` = '$product_id'";
                        $result_product = mysqli_query($conn, $sql10);
                        while ($product_row = mysqli_fetch_assoc($result_product)) {
                            $subtotal = $pr_row['quantity'] * $product_row['pr_pr'];
                            echo '<div class="ch-pr">
                            <div class="ch-img">
                            <h3>' . $count . '</h3>
                            <img src="img/products_img/' . $product_row['pr_img_n'] . '" alt="">
                            </div>
                            <div class="ch-name1" >
                            <h3>' . $product_row['pr_name'] . '</h3>
                            </div>
                            <div class="ch-pr-price1">
                            <h3>₹' . $product_row['pr_pr'] . '/-</h3>
                            </div>
                            <div class="ch-pr-price5">
                            <div>
                                <input type="number" readonly value="' . $pr_row['quantity'] . '" id="us-ca-1" style="width: 50px;">
                            </div>
                            </div>
                            <div class="ch-pr-price1">
                            <h3>₹' . $subtotal . '/-</h3>
                            </div>
                            </div>
                            <hr>';
                            $total = $total + $subtotal;
                            $count++;
                        }
                    }
                }
            } else if (isset($_POST['cart_id_confirm'])) {
                 // Fallback if re-rendering from confirm
                 $total = 0; // Logic needed if we want to persist total across postbacks without cart logic repetition
                 // For now assumes initial flow
            }
            ?>
        </div>
        
        <?php
        // Fetch Current Default Address
        $current_address = "Please select an address.";
        $sql_def = "SELECT `user_address` FROM `user_addresses` WHERE `user_id` = '$user_id' AND `is_default` = 1 LIMIT 1";
        $res_def = mysqli_query($conn, $sql_def);
        if ($r_def = mysqli_fetch_assoc($res_def)) {
            $current_address = $r_def['user_address'];
        } else {
             // Fallback to register_user col if not in user_addresses
             $row = mysqli_fetch_assoc($result_user);
             if ($row) {
                 $current_address = (!empty($row['user_address'])) ? $row['user_address'] : $row['user2_address'];
                 if (empty($current_address)) $current_address = "No address found. Please add one.";
             }
        }
        $delivery_total = isset($total) ? $total + 0 : 0;
        ?>

        <div id="order-address" class="ch-de">
            <div class="ch-add">
                <h2>2. Delivery Address</h2>
            </div>
            <div class="ch-de-add">
                <div style="font-size: 1.1rem; margin-bottom: 10px;"><?php echo $current_address; ?></div>
                <button class="btn-change" onclick="document.getElementById('addressModal').style.display='block'">Change Address</button>
            </div>
        </div>

        <!-- Address Selection Modal -->
        <div id="addressModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('addressModal').style.display='none'">&times;</span>
                <h3>Select Delivery Address</h3>
                <hr>
                <?php
                $sql_all = "SELECT * FROM `user_addresses` WHERE `user_id` = '$user_id'";
                $res_all = mysqli_query($conn, $sql_all);
                while ($arow = mysqli_fetch_assoc($res_all)) {
                    echo '<form method="post">
                        <!-- Preserve cart info -->
                        <input type="hidden" name="buy_cart_item_now" value="1">
                        <input type="hidden" name="cart_id_buy" value="'. (isset($user_cartid) ? $user_cartid : '') .'">
                        
                        <div class="address-item">
                            <div>'. $arow['user_address'] .'</div>
                            <button name="set_checkout_address" value="'.$arow['address_id'].'" class="btn-change">Select</button>
                        </div>
                    </form>';
                }
                ?>
                <div style="margin-top: 20px;">
                    <a href="account_page_address.php" target="_blank" style="color: blue; text-decoration: underline;">+ Add New Address (Opens in new tab)</a>
                </div>
            </div>
        </div>

        <div id="order-payment-method" class="ch-de">
            <div class="ch-add">
                <h2>3. Payment Method</h2>
            </div>
            <div class="ch-de-add">
                Cash on Delivery / Pay on Delivery
            </div>
        </div>
        <div id="order-summery" class="ch-de">
            <div class="ch-add">
                <h2>4. Order Summary</h2>
            </div>
            <div class="ch-de-add">
                <ul class="order-su-bill">
                    <li>Items &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp: ₹<?php echo isset($total) ? $total : 0; ?>/- </li>
                    <li>Delivery &nbsp: ₹0/- (By Shop Owners)</li>
                    <li>Total &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp: ₹<?php echo $delivery_total; ?>/-</li>
                </ul>
                <ul id="order-total">
                    <li>Order Total : ₹<?php echo $delivery_total; ?>/-</li>
                </ul>
            </div>
        </div>

        <div id="conform-order" class="ch-de">
            <div class="ch-add">
                <h2>5. Confirm Order</h2>
            </div>
            <div class="ch-de-add">
                <form action="order_confirm.php" method="post">
                    <input type="hidden" name="cart_id_confirm" value="<?php echo isset($user_cartid) ? $user_cartid : ''; ?>">
                    <input type="hidden" name="order_total_pr" value="<?php echo $delivery_total; ?>">
                    <button class="btn-buy-cart" name="btn_confirm_buy" value="order_confirm">Confirm Order</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('addressModal')) {
                document.getElementById('addressModal').style.display = "none";
            }
        }
    </script>
</body>

</html>