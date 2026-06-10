<?php

function getCommissionRate($total_sales) {
    if ($total_sales <= 10000) {
        return 5;
    } elseif ($total_sales <= 50000) {
        return 4;
    } elseif ($total_sales <= 100000) {
        return 3;
    } else {
        return 2;
    }
}

function processOrderCommission($conn, $order_id, $shop_id) {
    // 1. Check if commission is already calculated (idempotency)
    // We check if commission_amount is already set or if we have a flag. 
    // Since we just added columns (default 0), we check if commission_amount > 0 AND/OR check status.
    // Better to check if it was already processed to avoid double counting.
    
    $check_sql = "SELECT commission_amount, total_amount, order_status FROM order_table WHERE order_id = '$order_id'";
    $check_res = mysqli_query($conn, $check_sql);
    $order = mysqli_fetch_assoc($check_res);

    if (!$order) {
        return ["status" => "error", "message" => "Order not found"];
    }

    if ($order['commission_amount'] > 0) {
        return ["status" => "skipped", "message" => "Commission already calculated"];
    }
    
    // 2. Get Shop's total sales
    $shop_sql = "SELECT total_sales FROM shops WHERE shop_id = '$shop_id'";
    $shop_res = mysqli_query($conn, $shop_sql);
    $shop = mysqli_fetch_assoc($shop_res);
    
    if (!$shop) {
        return ["status" => "error", "message" => "Shop not found"];
    }
    
    $current_sales = $shop['total_sales'];
    
    // 3. Calculate Base Amount (Product Total Only, Excluding Delivery)
    // Fetch sum of items
    $item_sum_sql = "SELECT SUM(unit_pr * quantity) as product_total FROM order_items WHERE order_id = '$order_id'";
    $item_sum_res = mysqli_query($conn, $item_sum_sql);
    $item_data = mysqli_fetch_assoc($item_sum_res);
    $product_total = $item_data['product_total'] ?? 0;
    
    // Fallback if no items found (shouldn't happen)
    if ($product_total == 0) {
        $product_total = $order['total_amount']; // Fallback to avoid 0 commission if items missing
    }

    // 4. Calculate Commission
    $rate = getCommissionRate($current_sales);
    $commission_amount = ($product_total * $rate) / 100;
    
    // Shop Earning = Product Value - Commission
    // The delivery charge (Difference between total_amount and product_total) is NOT included in shop earning 
    // based on "delivery charge is separate from the shop owner"
    $shop_earning = $product_total - $commission_amount;
    
    // 5. Update Order
    $update_order = "UPDATE order_table SET 
                     commission_percentage = '$rate',
                     commission_amount = '$commission_amount',
                     shop_earning = '$shop_earning'
                     WHERE order_id = '$order_id'";
                     
    if (!mysqli_query($conn, $update_order)) {
        return ["status" => "error", "message" => "Failed to update order: " . mysqli_error($conn)];
    }
    
    // 5. Update Shop Stats
    $update_shop = "UPDATE shops SET 
                    total_sales = total_sales + '$order_amount',
                    total_commission_paid = total_commission_paid + '$commission_amount',
                    total_earnings = total_earnings + '$shop_earning'
                    WHERE shop_id = '$shop_id'";
                    
    if (!mysqli_query($conn, $update_shop)) {
        return ["status" => "error", "message" => "Failed to update shop stats: " . mysqli_error($conn)];
    }
    
    return ["status" => "success", "rate" => $rate, "commission" => $commission_amount];
}
?>
