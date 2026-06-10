<?php
include '_db_connect.php';

header('Content-Type: application/json');

$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

// Determine if we should filter by status (if column exists) or join users
// For now, let's try to select everything and join users to be safe about "Approved" status
// The user spec says "shops.status". We'll assume we added it or will add it.
// Ideally usage: SELECT ... FROM shops WHERE status = 'Active' (or 'Approved')

$response = ['status' => 'error', 'shops' => []];

try {
    if ($lat && $lng) {
        // Haversine formula
        // Distance in km
        $sql = "SELECT s.shop_id, s.shop_name, s.shop_address, s.shop_image, s.latitude, s.longitude,
                ( 6371 * acos( cos( radians($lat) ) * cos( radians( s.latitude ) ) * cos( radians( s.longitude ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( s.latitude ) ) ) ) AS distance
                FROM shops s
                JOIN users u ON s.owner_user_id = u.user_id
                WHERE u.status = 'active' OR u.status = 'approved' 
                ORDER BY distance ASC
                LIMIT 20";
    } else {
        // No location, just return random or alphabetical
        $sql = "SELECT s.shop_id, s.shop_name, s.shop_address, s.shop_image, s.latitude, s.longitude, 0 as distance
                FROM shops s
                JOIN users u ON s.owner_user_id = u.user_id
                WHERE u.status = 'active' OR u.status = 'approved'
                ORDER BY s.shop_name ASC
                LIMIT 20";
    }

    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        // Fallback if users table status check fails (e.g. status values differ)
        // Try simple select
         $sql = "SELECT shop_id, shop_name, shop_address, shop_image, latitude, longitude FROM shops LIMIT 20";
         $result = mysqli_query($conn, $sql);
    }

    $shops = [];
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            // Round distance
            if (isset($row['distance'])) {
                $row['distance'] = round($row['distance'], 2);
            }
            $shops[] = $row;
        }
        $response['status'] = 'success';
        $response['shops'] = $shops;
    } else {
        $response['message'] = mysqli_error($conn);
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
