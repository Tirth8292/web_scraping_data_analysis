<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["shop_userid"])) {
    header("Location: shop_login.php");
    exit();
}

$user_id = $_SESSION["shop_userid"];
$shop_res = mysqli_query($conn, "SELECT shop_id FROM shops WHERE owner_user_id = '$user_id'");
$shop = mysqli_fetch_assoc($shop_res);

if (!$shop) {
    echo "Error: Shop not found.";
    exit();
}

$shop_id = $shop['shop_id'];

$order_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : null;
if(!$order_id) { header("Location: shop_orders.php"); exit(); }

// Fetch Order Items related to this shop
$sql = "SELECT oi.*, p.pr_name, p.pr_img_n 
        FROM order_items oi 
        JOIN product p ON oi.pr_id = p.pr_id 
        WHERE oi.order_id = '$order_id' AND p.shop_id = '$shop_id'";
$items = mysqli_query($conn, $sql);

if(mysqli_num_rows($items) == 0) {
    echo "No items found for this shop in this order.";
    exit();
}

// Calculate Total for this shop
$shop_total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?php echo $order_id; ?> - Shop Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <?php include 'sidebar_shop.php'; ?>

    <div class="main-content">
        <a href="shop_orders.php" style="color: #6b7280; text-decoration: none; display: inline-block; margin-bottom: 1rem;">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
        <div class="header-title">
            <h1>Order #<?php echo $order_id; ?> Items</h1>
        </div>

        <div class="card">
            <h3 style="margin-top: 0; color: #4b5563;">Items to Fulfill</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($items)): ?>
                    <?php $shop_total += ($item['unit_pr'] * $item['quantity']); ?>
                    <tr>
                        <td style="display: flex; align-items: center; gap: 10px;">
                            <img src="img/products_img/<?php echo $item['pr_img_n']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            <div>
                                <div style="font-weight: 500; color: var(--text-dark);"><?php echo $item['pr_name']; ?></div>
                                <small style="color: var(--text-gray);">ID: <?php echo $item['pr_id']; ?></small>
                            </div>
                        </td>
                        <td>₹<?php echo $item['unit_pr']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td style="font-weight: 600;">₹<?php echo $item['unit_pr'] * $item['quantity']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <tr style="background: #f9fafb;">
                         <td colspan="3" style="text-align: right; padding-right: 2rem; font-weight: 700; color: var(--text-dark);">Your Share Total</td>
                         <td style="font-weight: 700; color: #4f46e5; font-size: 1.1rem;">₹<?php echo number_format($shop_total); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 2rem; display:flex; gap: 20px; align-items:flex-start;">
                
                <div style="flex: 1; padding: 1.5rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <h3 style="margin-top:0; font-size: 1.1rem; margin-bottom: 1rem;">Update Order Status</h3>
                    
                    <?php
                    // Fetch current status and commission info
                    $st_sql = "SELECT order_status, commission_amount, shop_earning, commission_percentage, total_amount FROM order_table WHERE order_id = '$order_id'";
                    $st_res = mysqli_query($conn, $st_sql);
                    $order_info = mysqli_fetch_assoc($st_res);
                    $c_status = $order_info ? $order_info['order_status'] : '';

                    // Handle Update
                    if(isset($_POST['update_status'])) {
                        $new_status = $_POST['status'];
                        
                        // Update status
                        $update_sql = "UPDATE order_table SET order_status='$new_status' WHERE order_id='$order_id' AND shop_id='$shop_id'";
                        if(mysqli_query($conn, $update_sql)) {
                            
                            // If marked as Delivered, calculate commission
                            if($new_status == 'Delivered') {
                                include_once 'commission_helper.php';
                                $comm_result = processOrderCommission($conn, $order_id, $shop_id);
                                if($comm_result['status'] == 'error') {
                                    echo "<div style='color:red; margin-bottom:10px;'>Error calculating commission: ".$comm_result['message']."</div>";
                                }
                            }
                            
                            echo "<script>window.location.href='shop_order_details.php?id=$order_id&msg=success';</script>";
                        }
                    }
                    ?>

                    <form method="post">
                        <label style="display:block; margin-bottom:5px; font-weight:500;">Current Status: <span style="color:#4f46e5;"><?php echo $c_status; ?></span></label>
                        <select name="status" style="width:100%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; margin-bottom: 15px;">
                            <option value="Pending" <?php echo ($c_status=='Pending')?'selected':''; ?>>Pending</option>
                            <option value="Accepted" <?php echo ($c_status=='Accepted')?'selected':''; ?>>Accepted</option>
                            <option value="Packed" <?php echo ($c_status=='Packed')?'selected':''; ?>>Packed</option>
                            <option value="Ready for Delivery" <?php echo ($c_status=='Ready for Delivery')?'selected':''; ?>>Ready for Delivery</option>
                            <option value="Delivered" <?php echo ($c_status=='Delivered')?'selected':''; ?>>Delivered</option>
                        </select>
                        <button type="submit" name="update_status" style="width:100%; padding: 10px; background: #4f46e5; color: white; border: none; border-radius: 6px; cursor: pointer;">Update Status</button>
                    </form>
                </div>

                <div style="flex: 1; display: flex; flex-direction: column; gap: 1rem;">
                    <div style="padding: 1rem; background: #fffbeb; color: #b45309; border-radius: 6px; font-size: 0.9rem;">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Instructions:</strong><br>
                        1. <b>Accept</b> the order to acknowledge it.<br>
                        2. Mark as <b>Packed</b> when items are packed.<br>
                        3. Mark as <b>Ready for Delivery</b> to notify delivery partners.<br>
                        4. Mark as <b>Delivered</b> when customer receives it.
                    </div>

                    <?php if($order_info && $order_info['commission_amount'] > 0): ?>
                    <div style="padding: 1.5rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                        <h3 style="margin-top:0; color: #166534; font-size: 1.1rem; margin-bottom: 1rem;">Revenue Breakdown</h3>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: #15803d;">Total Order Value</span>
                            <span style="font-weight: 600;">₹<?php echo number_format($order_info['total_amount'], 2); ?></span> 
                        </div>
                         <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: #b91c1c;">Platform Commission (<?php echo $order_info['commission_percentage']; ?>%)</span>
                            <span style="font-weight: 600; color: #b91c1c;">- ₹<?php echo number_format($order_info['commission_amount'], 2); ?></span>
                        </div>
                        <div style="border-top: 1px dashed #15803d; margin: 0.5rem 0;"></div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #166534; font-weight: 700;">Net Earning</span>
                            <span style="font-weight: 700; color: #166534; font-size: 1.2rem;">₹<?php echo number_format($order_info['shop_earning'], 2); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
