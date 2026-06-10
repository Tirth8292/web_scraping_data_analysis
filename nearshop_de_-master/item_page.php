<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Now</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <!-- <link rel="stylesheet" href="css/style2.css"> -->
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




    <?php

    if (isset($_REQUEST['product_id'])) {
        $pro_id = $_REQUEST['product_id'];
        $sql2 = "SELECT * FROM `product` WHERE `pr_id`='$pro_id'";
        $relq2 = mysqli_query($conn, $sql2);
        if (mysqli_num_rows($relq2) > 0) {
            while ($row = mysqli_fetch_assoc($relq2)) {
                echo '<div class="pro-details">
                 <div class="main-con">
                     <div class="head-ti">
                         ' . $row['pr_name'] . '
                     </div>
                     <div class="img-con">
                         <div class="pro-item-1" id="pro-de-im1">
                             <img src="img/products_img/' . $row['pr_img_n'] . '" alt="">
                         </div>
                         <div class="pro-item-1" id="pro-de-im2">
                             <div class="pro-info">
                                 <div class="details-pr" id="d-p-1">
                                     <u>₹' . $row['pr_pr'] . '</u>
                                 </div>
                                 <div class="details-pr" id="d-p-2">
                                     ' . $row['pr_de'] . '
         
                                 </div>';
                                 if ($row['pr_qu'] <=5) {
                                    echo '<div class="details-pr" id="d-p-3">
                                    <u>' . $row['pr_qu'] . ' in stock</u>
                                </div>';
                                 } else {
                                    echo '<div class="details-pr" id="d-p-3">
                                    <u></u>
                                </div>';
                                 }
                                 
                                 echo '<div class="details-pr" id="d-p-4">
                                     <form action="index.php" method="post">
                                         <input type="number" name="pr_quantity" placeholder="Quantity" value="1">
                                         <input type="hidden" name="productid_add_to_cart" value="' . $row['pr_id'] . '" placeholder="Quantity">
                                         <br>
                                         <br>
                                         <button class="btn-buy-cart" name="add_to_cart_btn" value="add_to_cart_btn_set">add to Cart</button>
                                         <!-- <div class="details-pr">5</div> -->
                                     </form>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
                 <div class="all-pr-de">
                     <h3>Product details</h3>
                     <ul>
                         <li class="li-pr-d">Name : ' . $row['pr_name'] . '</li>
                         <li class="li-pr-d">Price : ' . $row['pr_pr'] . '</li>
                         <li class="li-pr-d">color : ' . $row['pr_co'] . '</li>
                         <li class="li-pr-d">Brand : ' . $row['pr_brand'] . '</li>
                         
                     </ul>
                 </div>
             </div>';
            }
        }
    }
    ?>

    <!-- <div class="pro-details">
        <div class="main-con">
            <div class="head-ti">
                hello
            </div>
            <div class="img-con">
                <div class="pro-item-1" id="pro-de-im1">
                    <img src="img/products_img/pr_img1.jpg" alt="">
                </div>
                <div class="pro-item-1" id="pro-de-im2">
                    <div class="pro-info">
                        <div class="details-pr" id="d-p-1">
                            <u>₹100</u>
                        </div>
                        <div class="details-pr" id="d-p-2">
                            Lorem, ipsum dolor sit amet consectetur adipisicing elit.

                        </div>
                        <div class="details-pr" id="d-p-3">
                            <u>30 in stock</u>
                        </div>
                        <div class="details-pr" id="d-p-4">
                            <form action="">
                                <input type="number" placeholder="Quantity">
                                <br>
                                <br>
                                <button class="btn-buy-cart">add to Cart</button>
                                <button class="btn-buy-cart">Buy now</button>
                                //<div class="details-pr">5</div> 
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="all-pr-de">
            <h3>Product details</h3>
            <ul>
                <li class="li-pr-d">Name :</li>
                <li class="li-pr-d">Price :</li>
                <li class="li-pr-d">color :</li>
                <li class="li-pr-d">Brand :</li>
                <details>
                    hel.skjdb ,bvsk d,jmbvkjdfmhbvk czjfhmncbck zdjfmnbv zkj
                </details>
            </ul>
        </div>
    </div>-->
    <div class="reviews-section" style="max-width: 1200px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <h3 style="font-size: 1.5rem; color: #1f2937; margin-bottom: 1rem;">Customer Reviews</h3>
        <div style="text-align: center; padding: 3rem; background: #f9fafb; border-radius: 0.5rem; border: 1px dashed #e5e7eb;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-chat-square-text" viewBox="0 0 16 16" style="color: #9ca3af; margin-bottom: 1rem;">
                <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-2.5a2 2 0 0 0-1.6.8L8 14.333 6.1 11.8a2 2 0 0 0-1.6-.8H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2.5l3.5 4.5 3.5-4.5H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z" />
                <path d="M3 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3 6a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 6zm0 2.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z" />
            </svg>
            <p style="font-size: 1.1rem; color: #4b5563; font-weight: 500;">Reviews Coming Soon</p>
            <p style="color: #6b7280;">The reviews feature for this product is coming soon.</p>
        </div>
    </div>
    <?php
    include 'footer_all.php';
    ?>
</body>

</html>