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
    echo "<div style='padding:2rem; text-align:center; color:red;'>Error: No shop associated with this account. Please register your shop first or contact admin.</div>";
    exit();
}

$shop_id = $shop['shop_id'];

// 15. Handling Orders for Shop
// Now using direct shop_id link
$sql = "SELECT o.order_id, o.user_id, o.order_date, o.total_amount, o.order_status 
        FROM order_table o
        WHERE o.shop_id = '$shop_id'
        ORDER BY o.order_date DESC";

$orders = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop Orders - Shop Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <?php include 'sidebar_shop.php'; ?>

    <div class="main-content">
        <div class="header-title">
            <h1>Orders</h1>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($orders) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 500; 
                                    background: <?php echo ($row['order_status']=='Pending')?'#fff7ed':'#ecfdf5'; ?>; 
                                    color: <?php echo ($row['order_status']=='Pending')?'#c2410c':'#047857'; ?>;">
                                    <?php echo $row['order_status'] ? $row['order_status'] : 'Pending'; ?>
                                </span>
                            </td>
                            <td style="font-weight: 600; color: #10b981;">₹<?php echo number_format($row['total_amount']); ?></td>
                            <td>
                                <a href="shop_order_details.php?id=<?php echo $row['order_id']; ?>" class="btn-primary" style="background: white; color: var(--primary); border: 1px solid #d1d5db; padding: 0.5rem 1rem; font-size: 0.9rem; text-decoration: none;">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-gray); padding: 2rem;">No pending orders for your products.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
