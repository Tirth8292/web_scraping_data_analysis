<?php
session_start();
include '_db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to add items to cart', 'redirect' => 'login.php']);
    exit();
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product']);
    exit();
}

$userid_login = $_SESSION["userid"];
$productid = $_POST['product_id'];
$pro_qu = isset($_POST['quantity']) ? $_POST['quantity'] : 1;

// 1. Verify User Exists
$check_user_sql = "SELECT * FROM `users` WHERE `user_id` = '$userid_login'";
if (mysqli_num_rows(mysqli_query($conn, $check_user_sql)) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Session invalid. Please login again.', 'redirect' => 'login.php']);
    exit();
}

// 2. Get or Create Cart
$sql6 = "SELECT cart_id FROM `cart_table` WHERE `user_id` = '$userid_login'";
$rel_cart = mysqli_query($conn, $sql6);
$cartid_of_user = null;

if (mysqli_num_rows($rel_cart) > 0) {
    $cart_row = mysqli_fetch_assoc($rel_cart);
    $cartid_of_user = $cart_row['cart_id'];
} else {
    $sql_create_cart = "INSERT INTO `cart_table` (`user_id`) VALUES ('$userid_login')";
    if (mysqli_query($conn, $sql_create_cart)) {
        $cartid_of_user = mysqli_insert_id($conn);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: Unable to create cart']);
        exit();
    }
}

// 2.5 Check Shop Constraint
// Get shop_id of the product being added
$prod_sql = "SELECT shop_id FROM product WHERE pr_id = '$productid'";
$prod_res = mysqli_query($conn, $prod_sql);
if (!$prod_res || mysqli_num_rows($prod_res) == 0) {
     echo json_encode(['status' => 'error', 'message' => 'Product not found']);
     exit();
}
$prod_row = mysqli_fetch_assoc($prod_res);
$new_shop_id = $prod_row['shop_id'];

// Check existing items in cart
$check_cart_sql = "SELECT DISTINCT p.shop_id FROM cart_items ci JOIN product p ON ci.pr_id = p.pr_id WHERE ci.cart_id = '$cartid_of_user'";
$check_cart_res = mysqli_query($conn, $check_cart_sql);

if (mysqli_num_rows($check_cart_res) > 0) {
    $existing_row = mysqli_fetch_assoc($check_cart_res);
    $existing_shop_id = $existing_row['shop_id'];
    
    if ($existing_shop_id != $new_shop_id) {
        // Conflict
        echo json_encode(['status' => 'error', 'message' => 'You cannot mix products from different shops. Please clear your cart or finish the current order first.']);
        exit();
    }
}


// 3. Add Item
if ($cartid_of_user) {
    // Check if item already exists to update quantity? 
    // The previous implementation used INSERT, which implies duplicates allow or the table allows it.
    // Based on `INSERT INTO cart_items`, let's stick to simple insert. 
    // Ideally we should check for duplicates, but we will replicate existing logic for now.
    
    $sql5 = "INSERT INTO `cart_items` (`cart_id`, `pr_id`, `quantity`) VALUES ('$cartid_of_user', '$productid', '$pro_qu')";
    if (mysqli_query($conn, $sql5)) {
        echo json_encode(['status' => 'success', 'message' => 'Item added to your cart successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to initialize cart']);
}
?>
