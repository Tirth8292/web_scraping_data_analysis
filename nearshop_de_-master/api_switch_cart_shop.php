<?php
session_start();
include '_db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['userid']) || !isset($data['shop_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    exit;
}

$user_id = $_SESSION['userid'];
$target_shop_id = intval($data['shop_id']);

// Get Cart ID
$res = mysqli_query($conn, "SELECT cart_id FROM cart_table WHERE user_id = '$user_id'");
if(mysqli_num_rows($res) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'No cart']);
    exit;
}
$cart = mysqli_fetch_assoc($res);
$cart_id = $cart['cart_id'];

// Get current cart items to know names and quantities
$items_res = mysqli_query($conn, "SELECT ci.pr_id, ci.quantity, p.pr_name FROM cart_items ci JOIN product p ON ci.pr_id = p.pr_id WHERE ci.cart_id = '$cart_id'");

$updates = []; // Array of {old_pr_id, new_pr_id}

while($item = mysqli_fetch_assoc($items_res)) {
    $p_name = mysqli_real_escape_string($conn, $item['pr_name']);
    
    // Find corresponding product in NEW shop
    $find_sql = "SELECT pr_id FROM product WHERE shop_id = '$target_shop_id' AND pr_name = '$p_name' LIMIT 1";
    $find_res = mysqli_query($conn, $find_sql);
    
    if($new_prod = mysqli_fetch_assoc($find_res)) {
        $updates[] = [
            'old_pr_id' => $item['pr_id'],
            'new_pr_id' => $new_prod['pr_id']
        ];
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Product ' . $item['pr_name'] . ' not found in target shop']);
        exit;
    }
}

// Perform Updates
// Since we are updating the PR_ID in cart_items where cart_id is fixed.
// We must be careful about duplicate keys if for some reason the structure allows it, but here (cart_id, pr_id) might be unique?
// Let's assume (cart_id, pr_id) is unique.

foreach($updates as $up) {
    $old = $up['old_pr_id'];
    $new = $up['new_pr_id'];
    mysqli_query($conn, "UPDATE cart_items SET pr_id = '$new' WHERE cart_id = '$cart_id' AND pr_id = '$old'");
}

// Update Session Selection
$_SESSION['selected_shop_id'] = $target_shop_id;
$shop_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT shop_name FROM shops WHERE shop_id = '$target_shop_id'"));
$_SESSION['selected_shop_name'] = $shop_info['shop_name'];

echo json_encode(['status' => 'success']);
?>
