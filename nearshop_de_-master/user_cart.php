<?php
session_start();
include '_db_connect.php';


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Now</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- <link rel="stylesheet" href="css/style2.css"> -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tilt+Prism&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="img/near.png" sizes="16x16 32x32">
    <!-- <style>
    <?php // include "css/style.css" 
    ?>
    </style> -->
    <?php

    if (isset($_POST['cart_item_update'])) {
        $c_id = $_POST['car_id'];
        $p_id = $_POST['pro_id'];
        $new_quantity = $_POST['pro_q'];
        
        if ($new_quantity > 0) {
            $sql_update = "UPDATE `cart_items` SET `quantity` = '$new_quantity' WHERE `cart_id` = '$c_id' AND `pr_id` = '$p_id'";
            mysqli_query($conn, $sql_update);
        }
    }

    if (isset($_POST['cart_item_delete'])) {
        $c_id = $_POST['car_id'];
        $p_id = $_POST['pro_id'];
        $pro_quantity = $_POST['pro_q'];
        //echo $c_id;
        //echo $p_id;
        $sql11 = "DELETE FROM `cart_items` WHERE `cart_id`= '$c_id' AND `pr_id` = '$p_id' LIMIT 1";
        $relsult_del_cart_item = mysqli_query($conn, $sql11);
        /*if ($relsult_del_cart_item) {
            # code...
            echo "hello";
        }*/
    }

    echo '<script>
        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }
        </script>';
    if ($_SESSION['userid'] == '') {
        echo '<script>
            window.location.replace("login.php");
            alert("Please login to view Cart");
            </script>';
    } else {

        $user_id_for_cart = $_SESSION["userid"];
        $sql8 = "SELECT * FROM `cart_table` WHERE `user_id` = '$user_id_for_cart'";
        $result_crid = mysqli_query($conn, $sql8);
        while ($row1 = mysqli_fetch_assoc($result_crid)) {
            $user_cartid = $row1['cart_id'];
        }
    }
    ?>

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
    <br>
    <br>

    <div class="checkout-det">
        <div id="order-products1" class="ch-de">
            <h2>User Cart</h2>
            <div class="ch-pr1">
                <div class="ch-img">
                    Items
                </div>
                <div class="ch-name1">
                    <h3>Product name</h3>
                </div>
                <div class="ch-pr-price1">
                    <h3>Price</h3>
                </div>
                <div class="ch-pr-price2">
                    <h3>
                        Quantity
                    </h3>
                </div>
                <div class="ch-pr-price1">
                    <h3>Subtotal</h3>
                </div>
            </div>
            <hr>
            <!-- <div class="ch-pr">
                <div class="ch-img">
                    <h3><button class="btn-cart-item-del">x</button></h3>
                    <img src="img/products_img/kitkat.jpg" alt="">
                </div>
                <div class="ch-name1">
                    <h3>Product name 12345</h3>
                </div>
                <div class="ch-pr-price1">
                    <h3>100/-</h3>
                </div>
                <div class="ch-pr-price5">
                    <div>
                        <form action="index.php" method="post">
                            <input type="number" name="pr_quantity" placeholder="Quantity" value="1" id="us-ca-1">
                            <input type="hidden" name="productid_add_to_cart" value="'.$row['pr_id'].'" placeholder="Quantity"> -->
            <!-- <div class="details-pr">5</div> -->
            <!-- </form>
                    </div>
                </div>
                <div class="ch-pr-price1">
                    <h3>100/-</h3>
                </div>
            </div>
            <hr> -->
            <?php

            $sql9 = "SELECT * FROM `cart_items` WHERE `cart_id` = '$user_cartid'";
            $result_cart_product = mysqli_query($conn, $sql9);
            if (mysqli_num_rows($result_cart_product) > 0) {
                while ($pr_row = mysqli_fetch_assoc($result_cart_product)) {
                    $product_id = $pr_row['pr_id'];
                    $sql10 = "SELECT * FROM `product` WHERE `pr_id` = '$product_id'";
                    $result_product = mysqli_query($conn, $sql10);
                    while ($product_row = mysqli_fetch_assoc($result_product)) {

                        $subtotal = $pr_row['quantity'] * $product_row['pr_pr'];
                        echo '<div class="ch-pr">
                        <div class="ch-img">
                        <h3>
                        <form action="user_cart.php" method="post">
                        <input type="hidden" name="pro_id" value = "' . $product_row['pr_id'] . '">
                        <input type="hidden" name="car_id" value = "' . $user_cartid . '">
                        <input type="hidden" name="pro_q" value = "' . $pr_row['quantity'] . '">
                        <button class="btn-cart-item-del"
                        name="cart_item_delete" value="del_pr_from_cart">x</button></form></h3>
                        <img src="img/products_img/' . $product_row['pr_img_n'] . '" alt="">
                        </div>
                        <div class="ch-name1" >
                        <h3>' . $product_row['pr_name'] . '</h3>
                        </div>
                        <div class="ch-pr-price1">
                        <h3>₹' . $product_row['pr_pr'] . '/-</h3>
                        </div>
                        <div class="ch-pr-price5">
                            <form action="user_cart.php" method="post" id="qty-form-' . $product_row['pr_id'] . '">
                                <input type="hidden" name="car_id" value="' . $user_cartid . '">
                                <input type="hidden" name="pro_id" value="' . $product_row['pr_id'] . '">
                                <input type="hidden" name="cart_item_update" value="1">
                                
                                <div class="qty-pill">
                                    <button type="button" class="qty-btn" onclick="updateQty(' . $product_row['pr_id'] . ', -1)">-</button>
                                    <input type="number" name="pro_q" value="' . $pr_row['quantity'] . '" min="1" class="qty-input" id="qty-input-' . $product_row['pr_id'] . '" readonly>
                                    <button type="button" class="qty-btn" onclick="updateQty(' . $product_row['pr_id'] . ', 1)">+</button>
                                </div>
                            </form>
                        </div>
                        <div class="ch-pr-price1">
                        <h3>₹' . $subtotal . '/-</h3>
                        </div>
                        </div>
                        <hr>';
                    }
                }
                echo '
                
                <form action="product_buy.php" method="post" style="display: flex;
                justify-content: center;">
                <input type="hidden" name="cart_id_buy" value="' . $user_cartid . '">
                <button name="buy_cart_item_now" value="1" class="btn-buy-cart" >Buy Now</button>
                </form>

                ';
            } else {
                echo '<h2 style = " text-align : center ; " >
                Cart is empty
                </h2>';
            }

            ?>
            <!-- <div class="ch-pr">
                <div class="ch-img">
                    <h3><button class="btn-cart-item-del">x</button></h3>
                    <img src="img/products_img/kitkat.jpg" alt="">
                </div>
                <div class="ch-name1">
                    <h3>Product name 12345</h3>
                </div>
                <div class="ch-pr-price1">
                    <h3>100/-</h3>
                </div>
                <div class="ch-pr-price5">
                    <div>
                        <form action="index.php" method="post">
                            <input type="number" name="pr_quantity" placeholder="Quantity" value="1" id="us-ca-1">
                            <input type="hidden" name="productid_add_to_cart" value="'.$row['pr_id'].'" placeholder="Quantity">
                            <div class="details-pr">5</div> -->
            <!-- </form>
                    </div>
                </div>
                <div class="ch-pr-price1">
                    <h3>100/-</h3>
                </div>
            </div>  -->
            <hr>
            
            <!-- Alternative Shops Section -->
            <div id="alt-shops-container" style="display:none; padding: 20px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; margin-top: 20px;">
                <h3 style="margin-top:0; color: #166534;"><i class="bi bi-shop"></i> Available at other shops</h3>
                <p style="color: #15803d; font-size: 0.9rem;">This entire order is available at the following nearby shops:</p>
                <div id="alt-shops-list" style="margin-top: 15px;"></div>
            </div>

        </div>
    </div>
    <?php
    include 'footer_all.php';
    ?>
    <script>
    function updateQty(id, change) {
        let input = document.getElementById('qty-input-' + id);
        let form = document.getElementById('qty-form-' + id);
        let val = parseInt(input.value);
        let newVal = val + change;
        
        if (newVal >= 1) {
            input.value = newVal;
            form.submit();
        }
    }

    // Check for alternatives on load
    document.addEventListener("DOMContentLoaded", function() {
        checkAlternatives();
    });

    function checkAlternatives() {
        fetch('api_check_alternatives.php')
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success' && data.shops.length > 0) {
                const container = document.getElementById('alt-shops-container');
                const list = document.getElementById('alt-shops-list');
                container.style.display = 'block';
                list.innerHTML = '';

                data.shops.forEach(shop => {
                    let costHtml = `₹${shop.total_cost}`;
                    let distHtml = shop.distance !== -1 ? `${shop.distance} km` : '';
                    
                    let div = document.createElement('div');
                    div.style.cssText = "display: flex; justify-content: space-between; align-items: center; background: white; padding: 10px; border-radius: 6px; margin-bottom: 8px; border: 1px solid #dcfce7;";
                    div.innerHTML = `
                        <div>
                            <div style="font-weight: 600; color: #111827;">${shop.shop_name}</div>
                            <div style="font-size: 0.85rem; color: #6b7280;">${shop.shop_address}</div>
                            <div style="font-size: 0.85rem; color: #4f46e5;">${distHtml}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: #059669; font-size: 1.1rem;">${costHtml}</div> <!-- Total Order Cost at this shop -->
                            <button onclick="switchShop(${shop.shop_id})" style="background: #22c55e; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85rem; margin-top: 5px;">Switch & Buy</button>
                        </div>
                    `;
                    list.appendChild(div);
                });
            }
        });
    }

    function switchShop(shopId) {
        if(confirm("Switch order to this shop? Prices may vary.")) {
            fetch('api_switch_cart_shop.php', {
                method: 'POST',
                body: JSON.stringify({shop_id: shopId})
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    }
    </script>
</body>
</html>