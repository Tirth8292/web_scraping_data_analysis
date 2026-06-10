<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["admin_lg_id"])) {
    header("Location: admin_login.php");
    exit();
}

// 1. Total Platform Values
// 1. Total Platform Values (Dynamic Calculation)
// 1. Total Platform Values (Dynamic Calculation)
// Fallback: If commission_amount column is missing, we assume 0 commission for now to prevent crash.
// We check for column existence? No, simpler to just wrap or select minimal.
// We will try to select SUM(unit_pr * quantity) which is safe.
// For commission, we will try to select it, but if it fails, we catch it? No, PHP fatal error.
// We must assume column might be missing.
// Let's use a try-catch equivalent query or just defaulting to 0 for commission if the previous query failed.

// Safe Query for Sales
// Helper to check column existence dynamically to prevent crashes on different environments
function checkCol($conn, $table, $col) {
    $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$col'");
    return mysqli_num_rows($res) > 0;
}

$has_commission = checkCol($conn, 'order_table', 'commission_amount');
$db_warning = "";

if ($has_commission) {
    // Column exists, we can fetch commission safely
    $total_score_sql = "SELECT 
    SUM(oi.unit_pr * oi.quantity) as sales,
    (SELECT SUM(commission_amount) FROM order_table WHERE order_status = 'Delivered') as commission
    FROM order_items oi 
    JOIN order_table o ON oi.order_id = o.order_id 
    WHERE o.order_status = 'Delivered'";
} else {
    // Column missing, fallback to safe query
    $total_score_sql = "SELECT SUM(oi.unit_pr * oi.quantity) as sales 
    FROM order_items oi 
    JOIN order_table o ON oi.order_id = o.order_id 
    WHERE o.order_status = 'Delivered'";
    $db_warning = "Note: Commission data is hidden because database update is required. Please run setup_commission_v5.php.";
}

$total_res = mysqli_query($conn, $total_score_sql);

$sales_val = 0;
$commission_val = 0;

if($total_res) {
    $row = mysqli_fetch_assoc($total_res);
    $sales_val = $row['sales'] ?? 0;
    if ($has_commission) {
        $commission_val = $row['commission'] ?? 0;
    }
}

$totals = ['sales' => $sales_val, 'commission' => $commission_val];

// 1.5 Calculate Delivery Fund
$del_fund_sql = "SELECT 
    (SELECT SUM(total_amount) FROM order_table) as total_collected,
    (SELECT SUM(unit_pr * quantity) FROM order_items) as product_cost";
$del_fund_res = mysqli_query($conn, $del_fund_sql);
$delivery_fund = 0;
if ($del_fund_res) {
    $del_data = mysqli_fetch_assoc($del_fund_res);
    $delivery_fund = ($del_data['total_collected'] ?? 0) - ($del_data['product_cost'] ?? 0);
    if($delivery_fund < 0) $delivery_fund = 0;
}

// 2. Monthly Breakdown
if ($has_commission) {
    $monthly_sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') as month, 
                    SUM(commission_amount) as revenue, 
                    COUNT(order_id) as orders
                    FROM order_table 
                    WHERE order_status = 'Delivered'
                    GROUP BY month 
                    ORDER BY month DESC";
} else {
    $monthly_sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') as month, 
                    0 as revenue, 
                    COUNT(order_id) as orders
                    FROM order_table 
                    WHERE order_status = 'Delivered'
                    GROUP BY month 
                    ORDER BY month DESC";
}
$monthly_res = mysqli_query($conn, $monthly_sql);

// 3. Shop Performance (Dynamic Aggregation)
// We calculate totals from order_table to ensure data availability even if shops table columns are missing.
// 3. Shop Performance (Dynamic Aggregation)
if ($has_commission) {
    $shops_sql = "SELECT 
                    s.shop_id, 
                    s.shop_name, 
                    COALESCE(SUM(o.total_amount), 0) as total_sales,
                    COALESCE(SUM(o.commission_amount), 0) as total_commission_paid,
                    COALESCE(SUM(o.shop_earning), 0) as total_earnings
                  FROM shops s
                  LEFT JOIN order_table o ON s.shop_id = o.shop_id AND o.order_status = 'Delivered'
                  GROUP BY s.shop_id
                  ORDER BY total_commission_paid DESC";
} else {
    $shops_sql = "SELECT 
                    s.shop_id, 
                    s.shop_name, 
                    COALESCE(SUM(o.total_amount), 0) as total_sales,
                    0 as total_commission_paid,
                    0 as total_earnings
                  FROM shops s
                  LEFT JOIN order_table o ON s.shop_id = o.shop_id AND o.order_status = 'Delivered'
                  GROUP BY s.shop_id
                  ORDER BY total_sales DESC";
}
$shops_res = mysqli_query($conn, $shops_sql);
// We will mock the missing columns in the display loop


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Revenue Report - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <?php include 'sidebar_admin.php'; ?>

    <div class="main-content">
        <div class="header-title">
            <h1>Revenue Dashboard</h1>
        </div>

        <?php if ($db_warning): ?>
        <div style="background: #fff7ed; border-left: 4px solid #ea580c; padding: 1rem; margin-bottom: 2rem; border-radius: 4px; color: #9a3412;">
            <strong><i class="bi bi-exclamation-triangle-fill"></i> Database Update Required:</strong> 
            Commission tracking columns are missing in your database. 
            <a href="setup_commission_v5.php" target="_blank" style="color: #ea580c; font-weight: 700; text-decoration: underline;">Click here to run the update script</a> to enable commission tracking.
        </div>
        <?php endif; ?>

        <!-- Totals -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="margin:0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="color: var(--text-gray); font-weight: 600;">TOTAL PLATFORM COMMISSION</span>
                    <div style="background: #f0fdf4; color: #166534; padding: 10px; border-radius: 8px;">
                        <i class="bi bi-cash-stack" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <h2 style="margin: 0; font-size: 2.5rem; color: var(--text-dark);">₹<?php echo number_format($totals['commission'] ?? 0); ?></h2>
                <div style="color: #6b7280; margin-top: 5px;">Total Earnings from Commission</div>
            </div>

            <div class="card" style="margin:0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="color: var(--text-gray); font-weight: 600;">TOTAL GMV (GROSS SALES)</span>
                    <div style="background: #eff6ff; color: #3b82f6; padding: 10px; border-radius: 8px;">
                        <i class="bi bi-graph-up-arrow" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <h2 style="margin: 0; font-size: 2.5rem; color: var(--text-dark);">₹<?php echo number_format($totals['sales'] ?? 0); ?></h2>
                <div style="color: #6b7280; margin-top: 5px;">Total Order Value Processed</div>
            </div>
            <div class="card" style="margin:0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="color: var(--text-gray); font-weight: 600;">DELIVERY FUND (PARTNER)</span>
                    <div style="background: #fefce8; color: #ca8a04; padding: 10px; border-radius: 8px;">
                        <i class="bi bi-truck" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <h2 style="margin: 0; font-size: 2.5rem; color: var(--text-dark);">₹<?php echo number_format($delivery_fund); ?></h2>
                <div style="color: #6b7280; margin-top: 5px;">Collected for Delivery Partners</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            
            <!-- Shop Breakdown -->
            <div class="card">
                <h3>Shop Performance</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Shop Name</th>
                            <th>Total Sales</th>
                            <th>Commission Paid</th>
                            <th>Shop Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($shops_res) > 0): ?>
                            <?php while ($shop = mysqli_fetch_assoc($shops_res)): ?>
                            <tr>
                                <td style="font-weight: 500;">
                                    <a href="admin_shop_details.php?shop_id=<?php echo $shop['shop_id']; ?>" style="color: #4f46e5; text-decoration: none;">
                                        <?php echo htmlspecialchars($shop['shop_name']); ?>
                                    </a>
                                </td>
                                <td>₹<?php echo number_format($shop['total_sales'] ?? 0); ?></td>
                                <td style="color: #166534; font-weight: 600;">₹<?php echo number_format($shop['total_commission_paid'] ?? 0); ?></td>
                                <td>₹<?php echo number_format($shop['total_earnings'] ?? 0); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No shops found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Monthly Summary -->
            <div class="card">
                <h3>Monthly Revenue</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($monthly_res) > 0): ?>
                            <?php while ($m = mysqli_fetch_assoc($monthly_res)): ?>
                            <tr>
                                <td><?php echo $m['month']; ?></td>
                                <td><?php echo $m['orders']; ?></td>
                                <td style="color: #166534; font-weight: 600;">₹<?php echo number_format($m['revenue']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No revenue data yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</body>
</html>
