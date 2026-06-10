<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["admin_lg_id"])) {
    header("Location: admin_login.php");
    exit();
}

$pr_id = isset($_GET['pr_id']) ? $_GET['pr_id'] : null;

if (!$pr_id) {
    echo "Product ID required.";
    exit();
}

// 1. Fetch Product Details
$pr_sql = "SELECT p.*, s.shop_name 
           FROM product p 
           JOIN shops s ON p.shop_id = s.shop_id 
           WHERE p.pr_id = '$pr_id'";
$pr_res = mysqli_query($conn, $pr_sql);
$product = mysqli_fetch_assoc($pr_res);

if (!$product) {
    echo "Product not found.";
    exit();
}

// 2. Fetch Sales Metrics
// Total quantity sold, total revenue from this product
$stats_sql = "SELECT COUNT(oi.order_id) as total_orders, 
              SUM(oi.quantity) as total_sold, 
              SUM(oi.unit_pr * oi.quantity) as total_revenue,
              COUNT(DISTINCT user_id) as unique_customers
              FROM order_items oi
              JOIN order_table ot ON oi.order_id = ot.order_id 
              WHERE oi.pr_id = '$pr_id' AND ot.order_status = 'Delivered'";
$stats_res = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_res);

// 3. Recent Orders
$orders_sql = "SELECT ot.order_id, ot.order_date, ot.user_id, oi.quantity, (oi.unit_pr * oi.quantity) as amount, ot.order_status
               FROM order_items oi 
               JOIN order_table ot ON oi.order_id = ot.order_id 
               WHERE oi.pr_id = '$pr_id' 
               ORDER BY ot.order_date DESC LIMIT 10";
$orders_res = mysqli_query($conn, $orders_sql);

// 4. Sales Over Time (Chart)
$chart_sql = "SELECT DATE_FORMAT(ot.order_date, '%Y-%m-%d') as sale_date, SUM(oi.quantity) as qty 
              FROM order_items oi 
              JOIN order_table ot ON oi.order_id = ot.order_id 
              WHERE oi.pr_id = '$pr_id' AND ot.order_status = 'Delivered'
              GROUP BY sale_date 
              ORDER BY sale_date DESC LIMIT 14";
$chart_res = mysqli_query($conn, $chart_sql);
$dates = [];
$quantities = [];

while($row = mysqli_fetch_assoc($chart_res)) {
    array_unshift($dates, $row['sale_date']);
    array_unshift($quantities, $row['qty']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Analytics - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <?php include 'sidebar_admin.php'; ?>

    <div class="main-content">
        <a href="admin_shop_details.php?shop_id=<?php echo $product['shop_id']; ?>" style="color: #6b7280; text-decoration: none; display: inline-block; margin-bottom: 1rem;">
            <i class="bi bi-arrow-left"></i> Back to Shop
        </a>
        
        <div class="header-title">
            <div style="display: flex; gap: 20px; align-items: center;">
                <img src="img/products_img/<?php echo $product['pr_img_n']; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb;">
                <div>
                    <h1 style="font-size: 1.5rem; margin-bottom: 5px;"><?php echo htmlspecialchars($product['pr_name']); ?></h1>
                    <span style="background: #e0e7ff; color: #4338ca; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                        <?php echo htmlspecialchars($product['shop_name']); ?>
                    </span>
                    <span style="margin-left: 10px; color: #6b7280;">ID: <?php echo $product['pr_id']; ?></span>
                </div>
            </div>
            <div>
                 <h2 style="margin:0; text-align:right;">₹<?php echo $product['pr_pr']; ?></h2>
                 <small style="color: #6b7280;">Current Price</small>
            </div>
        </div>

        <!-- KPI Cards -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="margin:0; padding:1.5rem; text-align:center;">
                <div style="font-size: 0.9rem; color: #6b7280; margin-bottom: 5px;">Total Sold</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #111827;"><?php echo $stats['total_sold'] ?? 0; ?></div>
                <div style="font-size: 0.8rem; color: #10b981;">Units</div>
            </div>
             <div class="card" style="margin:0; padding:1.5rem; text-align:center;">
                <div style="font-size: 0.9rem; color: #6b7280; margin-bottom: 5px;">Revenue Generated</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #111827;">₹<?php echo number_format($stats['total_revenue'] ?? 0); ?></div>
                <div style="font-size: 0.8rem; color: #10b981;">Gross</div>
            </div>
             <div class="card" style="margin:0; padding:1.5rem; text-align:center;">
                <div style="font-size: 0.9rem; color: #6b7280; margin-bottom: 5px;">Customer Reach</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #111827;"><?php echo $stats['unique_customers'] ?? 0; ?></div>
                <div style="font-size: 0.8rem; color: #3b82f6;">Unique Buyers</div>
            </div>
             <div class="card" style="margin:0; padding:1.5rem; text-align:center;">
                <div style="font-size: 0.9rem; color: #6b7280; margin-bottom: 5px;">Current Stock</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #111827;"><?php echo $product['pr_qu']; ?></div>
                <div style="font-size: 0.8rem; color: #f59e0b;">Remaining</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            
            <!-- Sales Chart -->
            <div class="card">
                <h3>Sales Trend (Last 14 Days)</h3>
                <div style="height: 300px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card">
                <h3>Recent Orders</h3>
                 <table style="font-size: 0.9rem;">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Qty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($ord = mysqli_fetch_assoc($orders_res)): ?>
                        <tr>
                            <td>#<?php echo $ord['order_id']; ?></td>
                            <td><?php echo date('M d', strtotime($ord['order_date'])); ?></td>
                            <td><?php echo $ord['quantity']; ?></td>
                            <td>
                                <span style="font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; background: #f3f4f6;">
                                    <?php echo $ord['order_status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                 </table>
            </div>

        </div>

    </div>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Units Sold',
                    data: <?php echo json_encode($quantities); ?>,
                    backgroundColor: '#4f46e5',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    </script>
</body>
</html>
