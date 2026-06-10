<?php
session_start();
include '_db_connect.php';

header('Content-Type: application/json');

// Accept JSON input
$data = json_decode(file_get_contents('php://input'), true);
$shop_id = isset($data['shop_id']) ? intval($data['shop_id']) : 0;
$force = isset($data['force']) ? $data['force'] : false; // If true, clear cart without asking (assumes frontend asked)

if (!$shop_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Shop ID']);
    exit;
}

// Verify shop exists
$sql = "SELECT shop_id, shop_name FROM shops WHERE shop_id = '$shop_id'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Shop not found']);
    exit;
}
$shop = mysqli_fetch_assoc($result);

// Check for existing cart items from a DIFFERENT shop
$cart_conflict = false;
$cart_id = null;

if (isset($_SESSION['userid'])) {
    $user_id = $_SESSION['userid'];
    $cart_res = mysqli_query($conn, "SELECT cart_id FROM cart_table WHERE user_id = '$user_id'");
    if ($cart_row = mysqli_fetch_assoc($cart_res)) {
        $cart_id = $cart_row['cart_id'];
        
        // Check items in cart
        // We need to know which shop the CURRENT items belong to.
        // Join cart_items -> product -> check shop_id
        $check_sql = "SELECT p.shop_id FROM cart_items ci
                      JOIN product p ON ci.pr_id = p.pr_id
                      WHERE ci.cart_id = '$cart_id'
                      LIMIT 1";
        $check_res = mysqli_query($conn, $check_sql);
        if ($check_row = mysqli_fetch_assoc($check_res)) {
            $existing_shop_id = $check_row['shop_id'];
            if ($existing_shop_id != $shop_id) {
                $cart_conflict = true;
            }
        }
    }
}

if ($cart_conflict && !$force) {
    echo json_encode(['status' => 'conflict', 'message' => 'Your cart contains items from another shop. Switching shops will clear your cart.']);
    exit;
}

if ($cart_conflict && $force && $cart_id) {
    // Clear cart
    mysqli_query($conn, "DELETE FROM cart_items WHERE cart_id = '$cart_id'");
}

// Set session
$_SESSION['selected_shop_id'] = $shop_id;
$_SESSION['selected_shop_name'] = $shop['shop_name'];

echo json_encode(['status' => 'success', 'shop_name' => $shop['shop_name']]);
?>
