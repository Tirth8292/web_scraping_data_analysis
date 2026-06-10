<?php
session_start();
include '_db_connect.php';

// Check Login and Role
if (!isset($_SESSION["shop_userid"])) {
    header("Location: shop_login.php");
    exit();
}

$user_id = $_SESSION["shop_userid"];

// 1. Fetch Shop Details
$shop_res = mysqli_query($conn, "SELECT * FROM shops WHERE owner_user_id = '$user_id'");
$shop = mysqli_fetch_assoc($shop_res);

if (!$shop) {
    echo "Shop not found. Please contact support.";
    exit();
}

$shop_id = $shop['shop_id'];

// 2. Fetch Stats

// Product Count
$prod_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM product WHERE shop_id = '$shop_id'"))['c'];

// Order Count (Logic: Count unique orders in order_items that contain products from this shop)
// Note: This relies on Phase 4.1 adding shop_id to products. Existing legacy products might have NULL shop_id.
$order_sql = "SELECT COUNT(DISTINCT oi.order_id) as c 
              FROM order_items oi 
              JOIN product p ON oi.pr_id = p.pr_id 
              JOIN order_table o ON oi.order_id = o.order_id
              WHERE p.shop_id = '$shop_id' AND o.order_status = 'Delivered'";
$order_count = mysqli_fetch_assoc(mysqli_query($conn, $order_sql))['c'];

// Total Revenue (Approximate based on products sold from this shop)
$rev_sql = "SELECT SUM(oi.unit_pr * oi.quantity) as total 
            FROM order_items oi 
            JOIN product p ON oi.pr_id = p.pr_id 
            JOIN order_table o ON oi.order_id = o.order_id
            WHERE p.shop_id = '$shop_id' AND o.order_status = 'Delivered'";
$rev_res = mysqli_query($conn, $rev_sql);
$revenue = mysqli_fetch_assoc($rev_res)['total'] ?? 0;

// Dynamic Net Earnings Calculation
// Earnings = Revenue - Commission Paid
// We trust the commission paid column as it tracks actual deductions
$net_earnings = $revenue - ($shop['total_commission_paid'] ?? 0);

// Recent Products
$recent_prods = mysqli_query($conn, "SELECT * FROM product WHERE shop_id = '$shop_id' ORDER BY pr_id DESC LIMIT 5");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css"> <!-- Reusing Admin Styles -->
</head>
<body>

    <?php include 'sidebar_shop.php'; ?>

    <div class="main-content">
        <div class="header-title">
            <div>
                <h1><?php echo htmlspecialchars($shop['shop_name']); ?> Dashboard</h1>
                <p style="color: var(--text-gray); margin-top: 5px;">Welcome back, <?php echo $_SESSION['shop_header_name']; ?>.</p>
            </div>
            <div>
                 <a href="shop_add_product.php" class="btn-primary" style="text-decoration: none;">
                    <i class="bi bi-plus-lg"></i> Add Product
                 </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            
            <div class="card" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="color: var(--text-gray); font-weight: 600; font-size: 0.9rem;">TOTAL SALES</span>
                    <div style="background: #ecfdf5; color: #10b981; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <!-- Use calculated revenue (pure products) to exclude delivery fees -->
                <h2 style="margin: 0; font-size: 2rem; color: var(--text-dark);">₹<?php echo number_format($revenue); ?></h2>
                <div style="color: #6b7280; font-size: 0.85rem; margin-top: 5px;">Gross Sales</div>
            </div>

            <div class="card" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="color: var(--text-gray); font-weight: 600; font-size: 0.9rem;">NET EARNINGS</span>
                    <div style="background: #f0fdf4; color: #166534; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
                <h2 style="margin: 0; font-size: 2rem; color: var(--text-dark);">₹<?php echo number_format($net_earnings); ?></h2>
                <div style="color: #6b7280; font-size: 0.85rem; margin-top: 5px;">After Commission</div>
            </div>

            <div class="card" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="color: var(--text-gray); font-weight: 600; font-size: 0.9rem;">COMMISSION</span>
                    <div style="background: #fef2f2; color: #ef4444; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-pie-chart"></i>
                    </div>
                </div>
                <h2 style="margin: 0; font-size: 2rem; color: var(--text-dark);">₹<?php echo number_format($shop['total_commission_paid'] ?? 0); ?></h2>
                <div style="color: #6b7280; font-size: 0.85rem; margin-top: 5px;">Paid to Platform</div>
            </div>

            <div class="card" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="color: var(--text-gray); font-weight: 600; font-size: 0.9rem;">TOTAL ORDERS</span>
                    <div style="background: #eff6ff; color: #3b82f6; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-basket2-fill"></i>
                    </div>
                </div>
                <h2 style="margin: 0; font-size: 2rem; color: var(--text-dark);"><?php echo $order_count; ?></h2>
                <div style="color: #6b7280; font-size: 0.85rem; margin-top: 5px;">All Time</div>
            </div>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0;">Recent Products</h3>
                <a href="shop_products.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">View All</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recent_prods) > 0): ?>
                        <?php while ($rp = mysqli_fetch_assoc($recent_prods)): ?>
                        <tr>
                            <td>
                                <img src="img/products_img/<?php echo $rp['pr_img_n']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td style="font-weight: 500; color: var(--text-dark);"><?php echo $rp['pr_name']; ?></td>
                            <td>₹<?php echo $rp['pr_pr']; ?></td>
                            <td><?php echo $rp['pr_qu']; ?></td>
                            <td>
                                <span style="background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 600;">Active</span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-gray);">No products found. Start adding!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>

</body>
</html>
