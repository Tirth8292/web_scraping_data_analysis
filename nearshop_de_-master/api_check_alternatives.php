<?php
session_start();
include '_db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['userid'];
// Get Cart ID
$res = mysqli_query($conn, "SELECT cart_id FROM cart_table WHERE user_id = '$user_id'");
if(mysqli_num_rows($res) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'No cart']);
    exit;
}
$cart = mysqli_fetch_assoc($res);
$cart_id = $cart['cart_id'];

// Get Cart Items
$items_res = mysqli_query($conn, "SELECT ci.quantity, p.pr_name, p.shop_id FROM cart_items ci JOIN product p ON ci.pr_id = p.pr_id WHERE ci.cart_id = '$cart_id'");
$cart_items = [];
$current_shop_id = null;
while($row = mysqli_fetch_assoc($items_res)) {
    $cart_items[] = $row;
    if(!$current_shop_id) $current_shop_id = $row['shop_id'];
}

if (empty($cart_items)) {
    echo json_encode(['status' => 'error', 'message' => 'Cart empty']);
    exit;
}

$total_items = count($cart_items);

// Prepare names for query
// Note: We use the global $conn variable from _db_connect.php for escaping
$names = array_map(function($item) use ($conn) {
    return "'" . mysqli_real_escape_string($conn, $item['pr_name']) . "'";
}, $cart_items);
$names_str = implode(',', $names);

// Find shops that contain ALL these product names
$sql_shops = "SELECT p.shop_id, s.shop_name, s.shop_address, s.latitude, s.longitude, COUNT(DISTINCT p.pr_name) as match_count
              FROM product p
              JOIN shops s ON p.shop_id = s.shop_id
              WHERE p.pr_name IN ($names_str)
              AND p.shop_id != '$current_shop_id'
              GROUP BY p.shop_id
              HAVING match_count = $total_items";

$shop_res = mysqli_query($conn, $sql_shops);
$valid_shops = [];

$user_lat = isset($_SESSION['user_lat']) ? $_SESSION['user_lat'] : null;
$user_lng = isset($_SESSION['user_lng']) ? $_SESSION['user_lng'] : null;

if($shop_res) {
    while($shop = mysqli_fetch_assoc($shop_res)) {
        // Check stock and Calculate total cost for each item in this shop
        $s_id = $shop['shop_id'];
        $valid = true;
        $total_cost = 0;
        
        foreach($cart_items as $item) {
            $p_name = mysqli_real_escape_string($conn, $item['pr_name']);
            $qty = $item['quantity'];
            
            // Find specific product in this shop
            $chk = mysqli_query($conn, "SELECT pr_id, pr_pr, pr_qu FROM product WHERE shop_id = '$s_id' AND pr_name = '$p_name' LIMIT 1");
            if($p_row = mysqli_fetch_assoc($chk)) {
                if($p_row['pr_qu'] < $qty) {
                    $valid = false; // Not enough stock
                    break;
                }
                $total_cost += ($p_row['pr_pr'] * $qty);
            } else {
                $valid = false; // Should satisfy COUNT(*) but checking just in case
                break;
            }
        }
        
        if($valid) {
            $dist = -1;
            if($user_lat && $shop['latitude']) {
                $lat1 = $user_lat; $lon1 = $user_lng;
                $lat2 = $shop['latitude']; $lon2 = $shop['longitude'];
                $theta = $lon1 - $lon2;
                $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $dist = $dist * 60 * 1.1515;
                $dist = $dist * 1.609344; // KM
                $dist = round($dist, 2);
            }
            
            $shop['total_cost'] = $total_cost;
            $shop['distance'] = $dist;
            $valid_shops[] = $shop;
        }
    }
}

// Sort by distance if available, else by cost
if($user_lat) {
    usort($valid_shops, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });
} else {
    usort($valid_shops, function($a, $b) {
        return $a['total_cost'] <=> $b['total_cost'];
    });
}

echo json_encode(['status' => 'success', 'shops' => $valid_shops]);
?>
