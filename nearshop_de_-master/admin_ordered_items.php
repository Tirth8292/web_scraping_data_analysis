<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["admin_lg_id"])) {
    header("Location: admin_login.php");
    exit();
}

$or_id_for_items = isset($_POST['view_items']) ? $_POST['view_items'] : (isset($_POST['delivered_or_items']) ? $_POST['delivered_or_items'] : null);
$type = isset($_POST['type']) ? $_POST['type'] : 'active'; // active or delivered

if (!$or_id_for_items) {
    header("Location: admin_total_orders.php");
    exit();
}

// Fetch Items depending on type
// Active items are in `order_items`
// Delivered items are in `delivered_order_items` (legacy table)

$items = [];
$subtotal = 0;

if ($type == 'delivered') {
    // Fetch from delivered_order_items
     $sql27 = "SELECT i.*, p.pr_name, p.pr_img_n 
              FROM `delivered_order_items` i
              LEFT JOIN `product` p ON i.pr_id = p.pr_id
              WHERE i.order_id = '$or_id_for_items'";
} else {
    // Fetch from order_items
     $sql27 = "SELECT i.*, p.pr_name, p.pr_img_n 
              FROM `order_items` i
              LEFT JOIN `product` p ON i.pr_id = p.pr_id
              WHERE i.order_id = '$or_id_for_items'";
}

$relq27 = mysqli_query($conn, $sql27);

if ($relq27) {
    while($row = mysqli_fetch_assoc($relq27)) {
        $items[] = $row;
        $subtotal += ($row['unit_pr'] * $row['quantity']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Items #<?php echo $or_id_for_items; ?> - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <?php include 'sidebar_admin.php'; ?>

    <div class="main-content">
        <a href="admin_total_orders.php" style="color: #6b7280; text-decoration: none; display: inline-block; margin-bottom: 1rem;">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
        <div class="header-title">
            <h1>Order #<?php echo $or_id_for_items; ?> Details <?php echo $type == 'delivered' ? '(Delivered)' : '(Active)'; ?></h1>
        </div>

        <div class="card">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                    <?php foreach($items as $item): ?>
                    <tr>
                        <td style="display: flex; align-items: center; gap: 10px;">
                            <?php if($item['pr_img_n']): ?>
                                <img src="img/products_img/<?php echo $item['pr_img_n']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            <?php endif; ?>
                            <div>
                                <div style="font-weight: 500; color: var(--text-dark);"><?php echo $item['pr_name'] ? $item['pr_name'] : 'Product #' . $item['pr_id']; ?></div>
                                <small style="color: var(--text-gray);">ID: <?php echo $item['pr_id']; ?></small>
                            </div>
                        </td>
                        <td>₹<?php echo number_format($item['unit_pr']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td style="font-weight: 500;">₹<?php echo number_format($item['unit_pr'] * $item['quantity']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--text-gray);">Items data not available or deleted.</td></tr>
                    <?php endif; ?>
                    
                    <!-- Summary Rows -->
                    <tr style="border-top: 2px solid #e5e7eb;">
                        <td colspan="3" style="text-align: right; padding-right: 2rem; color: var(--text-gray);">Subtotal</td>
                        <td style="font-weight: 600;">₹<?php echo number_format($subtotal); ?></td>
                    </tr>
                    <tr>
                         <td colspan="3" style="text-align: right; padding-right: 2rem; color: var(--text-gray);">Delivery Fee</td>
                         <td style="font-weight: 600;">₹40</td>
                    </tr>
                    <tr style="background: #f9fafb;">
                         <td colspan="3" style="text-align: right; padding-right: 2rem; font-weight: 700; color: var(--text-dark);">Grand Total</td>
                         <td style="font-weight: 700; color: #4f46e5; font-size: 1.1rem;">₹<?php echo number_format($subtotal + 40); ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- View Only - No Action Buttons -->
        </div>
    </div>

</body>
</html>