        <?php
        session_start();
        date_default_timezone_set("Asia/Kolkata");
        ?>

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Krishi Market — Farming Supplies</title>
            <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Tilt+Prism&display=swap" rel="stylesheet">
            <link rel="icon" type="image/x-icon" href="img/near.png" sizes="16x16 32x32">
            <?php

            include '_db_connect.php';

            echo '<script>
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }
            </script>';

            $login_state = false;
            if (isset($_POST['login_con'])) {
                $session_state = false;

                $login_us_id = $_POST['login_id'];
                $login_pass = $_POST['login_password'];
                $sql4 = "SELECT * FROM `users`";
                $result_reg = mysqli_query($conn, $sql4);
                $er = true;
                while ($row = mysqli_fetch_assoc($result_reg)) {

                    if ($row['user_id'] == $login_us_id && $row['user_password'] == $login_pass && ($row['role'] == 'customer' || !isset($row['role']))) {


                        $login_state = true;
                        $user_name = $row['f_name'];
                        $user_l_name = $row['l_name'];
                        $userid = $row['user_id'];
                        
                        // Clear potential stale shop owner session data
                        unset($_SESSION["user_role"]);
                        unset($_SESSION["shop_owner_id"]);
                        
                        $_SESSION["login_user_fname"] = $user_name;
                        $_SESSION["login_user_lname"] = $user_l_name;
                        $_SESSION["userid"] = $userid;
                        $session_state = true;
                        $er = false;
                    }
                }
                if ($er) {
                    echo '<script>
                    window.location.replace("login.php");
                    alert("Enter a valid  User Id or Password");
                    </script>';
                }
            }

            // --- Phase 1: Location Logic ---
            // We now rely on $_SESSION['user_lat'] and $_SESSION['user_lng']
            // --- End Phase 1 ---

            if (isset($_POST['clear_shop_selection'])) {
                unset($_SESSION['selected_shop_id']);
                unset($_SESSION['selected_shop_name']);
            }

            if (isset($_POST['add_to_cart_btn'])) {

                if (isset($_SESSION['userid'])) {


                    $userid_login = $_SESSION["userid"];
                    
                    // Verify if user actually exists in the database
                    $check_user_sql = "SELECT * FROM `users` WHERE `user_id` = '$userid_login'";
                    $check_user_res = mysqli_query($conn, $check_user_sql);
                    
                    if (mysqli_num_rows($check_user_res) == 0) {
                        // User does not exist (stale session), force logout
                        session_unset();
                        session_destroy();
                        echo '<script>
                        alert("Session invalid or user not found. Please login again.");
                        window.location.replace("login.php");
                        </script>';
                        exit();
                    }

                    $sql6 = "SELECT * FROM `cart_table` WHERE `user_id` = '$userid_login'";
                    $rel_cart = mysqli_query($conn, $sql6);
                    
                    $cartid_of_user = null;

                    if (mysqli_num_rows($rel_cart) > 0) {
                        while ($cart_user = mysqli_fetch_assoc($rel_cart)) {
                            $cartid_of_user = $cart_user['cart_id'];
                        }
                    } else {
                        // Create a new cart for the user if one doesn't exist
                        $sql_create_cart = "INSERT INTO `cart_table` (`user_id`) VALUES ('$userid_login')";
                        if (mysqli_query($conn, $sql_create_cart)) {
                            $cartid_of_user = mysqli_insert_id($conn);
                        } else {
                            echo '<script>alert("Error creating cart: ' . mysqli_error($conn) . '");</script>';
                        }
                    }

                    if ($cartid_of_user) {
                        $productid = $_POST['productid_add_to_cart'];
                        $pro_qu = $_POST['pr_quantity'];
                        $sql5 = "INSERT INTO `cart_items` (`cart_id`, `pr_id`, `quantity`) VALUES ('$cartid_of_user', '$productid', '$pro_qu')";
                        $rel_cart_insert = mysqli_query($conn, $sql5);
                        if ($rel_cart_insert) {
                            // echo '<script>alert("Item added to your cart");</script>';*/
                             echo '<script>
                                 document.addEventListener("DOMContentLoaded", function() {
                                     showToast("Item added to your cart successfully!", "success");
                                 });
                             </script>';
                        } else {
                             echo '<script>alert("Error adding item to cart: ' . mysqli_error($conn) . '");</script>';
                        }
                    }
                } else {
                    echo '<script>
                    window.location.replace("login.php");
                    alert("Please login to add items to the cart");
                    </script>';
                }
            }

            $date_visit = date('Y-m-d');
            //echo $date_visit;


            $sql42 = "SELECT * FROM `vs_chart` WHERE `date` = '$date_visit'";
            $ch_sel_vi = mysqli_query($conn, $sql42);

            if (mysqli_num_rows($ch_sel_vi)>0) {
                
                $sql41 = "UPDATE `vs_chart` SET `count`=`count`+1 WHERE `date` = '$date_visit'";
                $ch_upchart_vi = mysqli_query($conn, $sql41);
            }
            else {

                $sql43 = "INSERT INTO `vs_chart`(`visit_id`, `date`, `count`) VALUES (NULL,'$date_visit','1')";
                $ch_inchart_vi = mysqli_query($conn, $sql43);
    
            }


            ?>
        </head>
        <style>
            .toast-notification {
                visibility: hidden;
                min-width: 250px;
                background-color: #333;
                color: #fff;
                text-align: center;
                border-radius: 8px;
                padding: 16px;
                position: fixed;
                z-index: 1000;
                left: 50%;
                bottom: 30px;
                transform: translateX(-50%);
                font-size: 17px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                opacity: 0;
                transition: opacity 0.3s, bottom 0.3s;
            }

            .toast-notification.show {
                visibility: visible;
                opacity: 1;
                bottom: 50px;
            }
            .toast-success { background-color: #10b981; }
            .toast-error { background-color: #ef4444; }

            /* Shop Banner Styles */
            .shop-banner-alert {
                background: linear-gradient(to right, #e0e7ff, #eff6ff);
                color: #3730a3;
                padding: 12px 24px;
                border-bottom: 1px solid #c7d2fe;
                display: flex;
                justify-content: space-between;
                align-items: center;
                animation: slideDown 0.3s ease-out;
            }
            .banner-text {
                display: flex;
                align-items: center;
                gap: 10px;
                font-size: 1rem;
            }
            .banner-text i {
                font-size: 1.2rem;
            }
            .btn-clear-choice {
                background: rgba(255, 255, 255, 0.5);
                border: 1px solid rgba(55, 48, 163, 0.2);
                color: #3730a3;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
                width: fit-content;
            }
            .btn-clear-choice:hover {
                background: white;
                color: #ef4444;
                border-color: #ef4444;
                transform: scale(1.1);
            }
            @keyframes slideDown {
                from { transform: translateY(-10px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            .shop-banner-alert {
                background: white;
                border: 1px solid #e0e7ff;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                color: #3730a3;
                padding: 8px 20px;
                border-radius: 50px; /* Pill shape */
                display: inline-flex;
                justify-content: space-between;
                gap: 15px;
                align-items: center;
                animation: slideDown 0.3s ease-out;
                
                /* Sticky Positioning */
                position: sticky;
                top: 85px; /* Height of sticky category bar (approx) */
                z-index: 98;
                margin-left: 2rem; /* Left alignment */
                margin-top: 1rem;
                margin-bottom: 1rem;
                width: fit-content;
                max-width: 90%;
            }
            .banner-text {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.95rem;
                font-weight: 500;
            }
            .banner-text i {
                color: #4f46e5;
                font-size: 1.1rem;
            }
            .btn-clear-choice {
                background: #fee2e2;
                border: none;
                color: #b91c1c;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 0.8rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .btn-clear-choice:hover {
                background: #fecaca;
            }

            /* Active Category Style */
            .fil-ca-items button.active-cat {
                background-color: #2e7d32; /* Primary */
                color: white;
                border-color: #2e7d32;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }

        </style>
        <body>
            <div id="toast" class="toast-notification"></div>
            <script>
                function showToast(message, type = 'success') {
                    var x = document.getElementById("toast");
                    x.className = "toast-notification show toast-" + type;
                    x.innerText = message;
                    setTimeout(function(){ 
                        x.className = x.className.replace("show", ""); 
                    }, 3000);
                }

                function addToCart(productId, quantity) {
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    formData.append('quantity', quantity);

                    fetch('ajax_add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success') {
                            showToast(data.message, 'success');
                        } else {
                            showToast(data.message, 'error');
                            if(data.redirect) {
                                setTimeout(() => window.location.href = data.redirect, 1500);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast("An error occurred", 'error');
                    });
                }
            </script>
            <?php
            // Re-inject PHP logic for toast triggering
            // This relies on the PHP block at the top setting a variable or outputting script directly if it wasn't an AJAX request.
            // Since the previous implementation outputted <script>alert(...)</script> directly in the PHP if block which is IN the <head> area (Wait, looking at file structure...)
            // Ah, the PHP block is inside the <head>.
            // So we need to ensure the JS function is defined BEFORE we call it, or we simply output the call and let it run on load.
            // Actually, the previous code outputted <script>alert()</script> inside the <head> logic. 
            // Better strategy: The <head> PHP block runs before body. 
            // We can set a PHP variable `$toast_message` and then check it at the end of body.
            // OR, since this is a simple replace, we can output the script that waits for DOMContentLoaded.
            ?>
            <?php


            ?>
            
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
                            <div class="nav-items" style="border: 1px solid #2e7d32; border-radius: 4px; padding: 2px 8px; margin-right: 5px;">
                                <a href="shop_selection.php" style="color: #2e7d32; display: flex; align-items: center; gap: 5px;">
                                    <i class="bi bi-geo-alt"></i>
                                    Nearby Stores (5km)
                                </a>
                            </div>
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

                            
                            <?php if (isset($_SESSION['shop_userid'])): ?>
                            
                            <?php endif; ?>

                            <div class="nav-items">
                            <?php if (isset($_SESSION['login_user_fname'])) {
                                /*echo '<form action="login.php" method="post" style="display: contents;">';
                                echo '<button name="logout" value="1" style="background: transparent;
                                font-size: 27px;
                                border: transparent;
                                ">';
                                echo $_SESSION['login_user_fname'] . " " . $_SESSION['login_user_lname'];
                                echo '</button>';
                                echo '</form>';*/
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
            </div>
            <hr>
            </header>
            
            <!-- Category Filter Section (Now Below Navbar) -->
            <div class="filter-cat">
                <form action="index.php" method="post">
                    <span class="cat-label">Category:</span>
                <div class="cat-items">
                        <?php 
                        $active_cat = isset($_POST['category1']) ? $_POST['category1'] : '';
                        $cats = [
                            1 => 'Pesticides', 2 => 'Fertilizers', 3 => 'Seeds', 4 => 'Farm Equipment',
                            5 => 'Irrigation', 6 => 'Animal Feed', 7 => 'Organic Products', 8 => 'Tools'
                        ];
                        foreach($cats as $id => $name) {
                            $isActive = ($active_cat == $id);
                            $activeClass = $isActive ? 'active-cat' : '';
                            // If active, setting value to empty string allows deselecting (toggling off) on next click
                            $val = $isActive ? '' : $id;
                            echo '<div class="fil-ca-items"><button name="category1" value="'.$val.'" class="'.$activeClass.'">'.$name.'</button></div>';
                        }
                        ?>
                    </div>
                </form>
            </div>
            <hr>

            <div class="slide">
                <div class="slider-bg-container">
                    <div class="slider-bg-item active" style="background-image: url('img/slider_1.png');"></div>
                    <div class="slider-bg-item" style="background-image: url('img/slider-2-min.png');"></div>
                </div>
                <div class="lines">
                    Everything Your Farm Needs
                </div>
                <!-- <div class="em-lo">
                    <?php
                    if (isset($_SESSION['login_user_fname'])) {
                        echo '<h2>';
                        echo "Welcome " .  $_SESSION['login_user_fname'] . " " . $_SESSION['login_user_lname'];
                        echo '</h2>';
                    } else {
                        echo '<form action="login.php" method="post">
                        <input type="text" name="user_id_home_page" placeholder="Enter your email User Id...">
                        <button name="home_log" value="1">Login</button>
                    </form>';
                    }
                    ?>
                </div> -->
            </div>
            <hr>

            <?php
            $selected_shop_id = isset($_SESSION['selected_shop_id']) ? $_SESSION['selected_shop_id'] : null;
            $selected_shop_name = isset($_SESSION['selected_shop_name']) ? $_SESSION['selected_shop_name'] : 'Selected Shop';

            if ($selected_shop_id) {
                // Show Banner Here
                echo '<div class="shop-banner-alert">
                        <div class="banner-text">
                            <i class="bi bi-shop-window"></i>
                            <span>Items from <strong>' . htmlspecialchars($selected_shop_name) . '</strong></span>
                        </div>
                        <form action="index.php" method="post" style="margin:0;">
                            <button name="clear_shop_selection" value="1" class="btn-clear-choice" title="Show All Nearby Shops">
                                <i class="bi bi-x"></i> Remove
                            </button>
                        </form>
                      </div>';
            }
            ?>
            <div class="products">
                <?php


                $user_lat = isset($_SESSION['user_lat']) ? $_SESSION['user_lat'] : null;
                $user_lng = isset($_SESSION['user_lng']) ? $_SESSION['user_lng'] : null;

                $relq2 = false; // Default to no results
                $search_condition = "";

                if (isset($_POST['ser']) && !empty($_POST['search'])) {
                    $search_term = mysqli_real_escape_string($conn, $_POST['search']);
                    $search_condition = " AND p.pr_name LIKE '%$search_term%' ";
                    $_POST['ser'] = null;
                } elseif (isset($_POST['category1']) && !empty($_POST['category1'])) {
                    $cat_term = mysqli_real_escape_string($conn, $_POST['category1']);
                    $search_condition = " AND p.pr_cat = '$cat_term' ";
                    $_POST['category1'] = null;
                }

                // Removed duplicate session check


                if ($selected_shop_id) {
                    // Show Banner


                    $sql_products = "SELECT p.*, s.shop_name ";
                    
                    if ($user_lat && $user_lng) {
                         $sql_products .= ", ( 6371 * acos( cos( radians($user_lat) ) * cos( radians( s.latitude ) ) * cos( radians( s.longitude ) - radians($user_lng) ) + sin( radians($user_lat) ) * sin( radians( s.latitude ) ) ) ) AS distance ";
                    } else {
                         $sql_products .= ", -1 AS distance ";
                    }

                    $sql_products .= "FROM product p 
                                     JOIN shops s ON p.shop_id = s.shop_id 
                                     WHERE p.shop_id = '$selected_shop_id' $search_condition 
                                     ORDER BY p.pr_name ASC";
                    
                    $relq2 = mysqli_query($conn, $sql_products);

                } elseif ($user_lat && $user_lng) {
                    // Haversine Formula to find shops within 5km
                    // We join product with shops
                    // 6371 is Earth radius in km
                    $sql_products = "SELECT p.*, s.shop_name, 
                                     ( 6371 * acos( cos( radians($user_lat) ) * cos( radians( s.latitude ) ) * cos( radians( s.longitude ) - radians($user_lng) ) + sin( radians($user_lat) ) * sin( radians( s.latitude ) ) ) ) AS distance 
                                     FROM product p 
                                     JOIN shops s ON p.shop_id = s.shop_id 
                                     WHERE 1=1 $search_condition
                                     HAVING distance <= 5 
                                     ORDER BY distance ASC";
                    
                    $relq2 = mysqli_query($conn, $sql_products);
                }

                if ($relq2 && mysqli_num_rows($relq2) > 0) {
                    $co = 1;
                    while ($each_pr = mysqli_fetch_assoc($relq2)) {

                        // Optional: Round distance for display
                        $dist_disp = round($each_pr['distance'], 1) . " km";
                        $shop_nm = $each_pr['shop_name'];

                        echo '<div class="pr">
                                <div class="pro-img">
                                    <a href="item_page.php?product_id=' . $each_pr['pr_id'] . '">
                                        <img src="img/products_img/' . $each_pr['pr_img_n'] . '" alt="Product Image" style="width:100%; height:100%; object-fit:contain;">
                                    </a>
                                </div>
                                <div class="pro-det">
                                    <h3>' . $each_pr['pr_name'] . '</h3>
                                    <div style="font-size:0.8rem; color:#2e7d32; font-weight:600; margin-bottom:5px;">
                                        <i class="bi bi-shop"></i> ' . $shop_nm . ' (' . $dist_disp . ')
                                    </div>
                                    <div class="det">
                                    ' . $each_pr['pr_de'] . '
                                    </div>
                                </div>
                                <div class="pro-pri">
                                    <div class="pri">
                                        ₹' . $each_pr['pr_pr'] . '
                                    </div>
                                    <div class="oper-pro">
                                    <button class="btn-buy-cart" onclick="addToCart(' . $each_pr['pr_id'] . ', 1)">add to Cart</button>
                                    </div>
                                </div>
                            </div>';
                        $co++;
                    }
                } else {
                    if (!$user_lat || !$user_lng) {
                        echo '<div style="width:100%; text-align:center; padding: 50px;">
                                <h2>Locating nearby shops...</h2>
                                <p>Please allow location access to see products near you (within 5km).</p>
                                <button onclick="requestLocation()" style="padding:10px 20px; margin-top:10px; cursor:pointer;">Retry Location</button>
                              </div>';
                    } else {
                        echo '<div style="width:100%; text-align:center; padding: 50px;">
                                <h2>No products found nearby.</h2>
                                <p>We could not find any shops within 5km of your location.</p>
                                <button onclick="requestLocation()" style="padding:10px 20px; margin-top:10px; cursor:pointer; background:#eee; border:1px solid #ccc;">Refresh Location</button>
                                <a href="shop_selection.php" style="display:block; margin-top:10px; color:blue;">View All Shops Map</a>
                              </div>';
                    }
                }
                ?>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        <?php if(!$user_lat): ?>
                        requestLocation();
                        <?php endif; ?>
                    });

                    function requestLocation() {
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(position => {
                                fetch("api_set_location.php", {
                                    method: "POST",
                                    headers: {'Content-Type': 'application/json'},
                                    body: JSON.stringify({lat: position.coords.latitude, lng: position.coords.longitude})
                                }).then(res => res.json())
                                  .then(data => {
                                      if(data.status === 'success') {
                                          window.location.reload();
                                      }
                                  });
                            }, error => {
                                alert("Unable to retrieve your location. Please enable location services to see nearby products.");
                            });
                        } else {
                            alert("Geolocation is not supported by this browser.");
                        }
                    }
                </script>



                <!-- <div class="pr">
            <div class="pro-img">
                    <form action="item_page.php" method="post">
                    <input type="hidden" name="product_id" value="">
                    <input type="image" src="img/products_img/pr_img1.jpg" name="item_sub" value="item">
                    </form>
                </div>
                <div class="pro-det">
                    <h3>Snickers</h3>
                    <div class="det">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Recusandae,
                    </div>
                </div>
                <div class="pro-pri">
                    <div class="pri">
                        ₹10
                    </div>
                    <div class="oper-pro">
                        <form action="#">
                            <button class="btn-buy-cart">Add to Cart</button>
                            <button class="btn-buy-cart">Buy</button>
                        </form>
                    </div>
                </div>
            
        </div> -->
                <!-- 
        <div class="pr">
            <div class="pro-img">
                <img src="img/products_img/pr_img1.jpg" alt="">
            </div>
            <div class="pro-det">
                <h3>Snickers</h3>
                <div class="det">
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Recusandae,
                </div>
            </div>
            <div class="pro-pri">
                <div class="pri">
                    ₹10
                </div>
                <div class="oper-pro">
                    <form action="">
                        <button class="btn-buy-cart">Add to Cart</button>
                        <button class="btn-buy-cart">Buy</button>
                    </form>
                </div>
            </div>
        </div> -->

            </div>

            <?php
            include 'footer_all.php';
            ?>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.3/gsap.min.js" integrity="sha512-7Au1ULjlT8PP1Ygs6mDZh9NuQD0A5prSrAUiPHMXpU6g3UMd8qesVnhug5X4RoDr35x5upNpx0A6Sisz1LSTXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
            <script src="js\index.js?v=<?php echo time(); ?>"></script>
        </body>

        </html>