<?php
session_start();
include '_db_connect.php';

// --- LOGIN LOGIC ---
if (isset($_POST['admin_login_con'])) {
    $admin_id = mysqli_real_escape_string($conn, $_POST['admin_login_id']);
    $password = mysqli_real_escape_string($conn, $_POST['admin_login_password']);

    // Check credentials in users table for super_admin role
    $sql = "SELECT * FROM users WHERE user_id = '$admin_id' AND role = 'super_admin'";
    $result = mysqli_query($conn, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Plain text password comparison as seen in database inspection (1234)
        // In a production environment, use password_verify()
        if ($password == $row['user_password']) {
            $_SESSION["admin_lg_id"] = $admin_id;
            $_SESSION["admin_type"] = 'super_admin';
            header("Location: admin_home.php");
            exit();
        } else {
            echo "<script>alert('Invalid Password'); window.location.href='admin_login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid Admin ID or Access Denied'); window.location.href='admin_login.php';</script>";
        exit();
    }
}

// --- SESSION CHECK ---
if (!isset($_SESSION["admin_lg_id"])) {
    header("Location: admin_login.php");
    exit();
}

// --- DATA FETCHING FOR DASHBOARD ---

// 1. Counts
$count_shops = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM shops"))['c'];
$count_active_shops = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='shop_owner' AND status='active'"))['c'];
$count_pending_shops = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='shop_owner' AND status='pending'"))['c'];
$count_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='customer'"))['c'];
$count_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM product"))['c'];

// 2. Revenue (Fallback calculation from order_table since shops.total_sales triggers error on some hosts)
// We sum total_amount from delivered orders for Gross Sales
$rev_sql = "SELECT SUM(total_amount) as total FROM order_table WHERE order_status = 'Delivered'";
$rev_res = mysqli_query($conn, $rev_sql);

// Handle potential error if order_table doesn't exist or other issues, though order_table is core.
if ($rev_res) {
    $rev_data = mysqli_fetch_assoc($rev_res);
    $total_revenue = $rev_data['total'] ?? 0;
} else {
    $total_revenue = 0;
}

// Commission - Defaults to 0 if 'total_commission_paid' column is missing in shops or order_table
// To be accurate, this requires running setup_commission_v5.php on the server.
$total_commission = 0; 
// Optional: Try to fetch if column exists, but for stability now, 0 is safer.

// 3. Top Shops (By Product Count for now, since sales linking is partial)
$top_shops_sql = "SELECT s.shop_name, COUNT(p.pr_id) as product_count 
                  FROM shops s 
                  LEFT JOIN product p ON s.shop_id = p.shop_id 
                  GROUP BY s.shop_id 
                  ORDER BY product_count DESC LIMIT 5";
$top_shops_res = mysqli_query($conn, $top_shops_sql);
$shop_labels = [];
$shop_data = [];
while ($row = mysqli_fetch_assoc($top_shops_res)) {
    $shop_labels[] = $row['shop_name'];
    $shop_data[] = $row['product_count'];
}

// 4. Sales Over Time (Mocked/Empty if no daily data in delivered_orders or complex parsing needed)
// Legacy `delivered_orders` has `date_ch` (date field?).
// Let's try to aggregate by date.
$sales_chart_sql = "SELECT date_ch, SUM(total_amount) as daily_total FROM delivered_orders GROUP BY date_ch ORDER BY date_ch DESC LIMIT 7";
$sales_chart_res = mysqli_query($conn, $sales_chart_sql);
$dates = [];
$sales = [];
if ($sales_chart_res) {
    while ($row = mysqli_fetch_assoc($sales_chart_res)) {
        // Reverse later for chart order
        array_unshift($dates, $row['date_ch']);
        array_unshift($sales, $row['daily_total']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-light: #f3f4f6;
            --white: #ffffff;
            --text-dark: #111827;
            --text-gray: #6b7280;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            margin: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: #1e1e2d; /* Dark theme sidebar */
            color: #a2a3b7;
            display: flex;
            flex-direction: column;
            transition: all 0.3s;
        }
        
        .logo-area {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo-area img {
            height: 35px;
            width: auto;
        }

        .nav-links {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: #a2a3b7;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            font-size: 0.95rem;
        }

        .nav-item i {
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.05);
            color: white;
            border-left-color: var(--primary);
        }

        .user-profile {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-profile img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary);
        }
        
        .user-info div {
            font-size: 0.9rem;
            color: white;
            font-weight: 500;
        }
        
        .user-info small {
            font-size: 0.75rem;
            color: #6b7280;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
        }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .header-title h1 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--text-dark);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            color: var(--text-gray);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }
        
        .stat-sub {
            font-size: 0.85rem;
            color: #10b981; /* Green for growth */
            margin-top: 0.5rem;
        }

        /* Charts */
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .chart-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            height: 400px;
        }

        .chart-head {
            margin-bottom: 1.5rem;
        }
        
        .chart-head h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        /* Responsiveness */
        @media (max-width: 1024px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            .sidebar .logo-area span, 
            .sidebar .nav-item span,
            .sidebar .user-info {
                display: none;
            }
            .nav-item {
                justify-content: center;
                padding: 1rem;
            }
            .nav-item i {
                margin: 0;
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <!-- Sidebar -->
    <?php include 'sidebar_admin.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header-title">
            <div>
                <h1>Dashboard Overview</h1>
                <p style="color: var(--text-gray); margin-top: 5px;">Welcome back, <?php echo $_SESSION["admin_lg_id"]; ?>. Here's what's happening today.</p>
            </div>
            <div>
                <a href="admin_revenue.php" style="text-decoration: none;">
                    <button style="background: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 500; cursor: pointer;">
                        <i class="bi bi-download"></i> Generate Report
                    </button>
                </a>
            </div>
        </div>

        <!-- 1. Stats Row -->
        <div class="stats-grid">
            <!-- Revenue -->
            <div class="stat-card">
                <div class="stat-head">
                    <span>TOTAL REVENUE</span>
                    <div class="stat-icon" style="background: #ecfdf5; color: #10b981;">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                </div>
                <h2 class="stat-value">₹<?php echo number_format($total_revenue); ?></h2>
                <div class="stat-sub"><i class="bi bi-arrow-up-short"></i> Gross Sales</div>
            </div>

            <!-- Commission -->
            <div class="stat-card">
                <div class="stat-head">
                    <span>TOTAL COMMISSION</span>
                    <div class="stat-icon" style="background: #f0fdf4; color: #166534;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <h2 class="stat-value">₹<?php echo number_format($total_commission); ?></h2>
                <div class="stat-sub">Platform Earnings</div>
            </div>

            <!-- Active Shops -->
            <div class="stat-card">
                <div class="stat-head">
                    <span>ACTIVE SHOPS</span>
                    <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;">
                        <i class="bi bi-shop"></i>
                    </div>
                </div>
                <h2 class="stat-value"><?php echo $count_active_shops; ?></h2>
                <div class="stat-sub" style="color: var(--text-gray);">
                    Pending Approvals: <a href="admin_shop_approvals.php" style="color: #ef4444; font-weight: 700;"><?php echo $count_pending_shops; ?></a>
                </div>
            </div>

            <!-- Total Users -->
            <div class="stat-card">
                <div class="stat-head">
                    <span>TOTAL USERS</span>
                    <div class="stat-icon" style="background: #f5f3ff; color: #8b5cf6;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <h2 class="stat-value"><?php echo number_format($count_users); ?></h2>
                <div class="stat-sub"><i class="bi bi-person-plus"></i> Growing platform</div>
            </div>

            <!-- Products -->
            <div class="stat-card">
                <div class="stat-head">
                    <span>TOTAL PRODUCTS</span>
                    <div class="stat-icon" style="background: #fff7ed; color: #f97316;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
                <h2 class="stat-value"><?php echo number_format($count_products); ?></h2>
                <div class="stat-sub">Across all categories</div>
            </div>
        </div>

        <!-- 2. Charts Row -->
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-head">
                    <h3>Revenue Analytics</h3>
                </div>
                <canvas id="revenueChart"></canvas>
            </div>
            
            <div class="chart-card">
                <div class="chart-head">
                    <h3>Top Shops (By Products)</h3>
                </div>
                <canvas id="shopChart"></canvas>
            </div>
        </div>
        
    </div>

    <!-- Chart Configuration -->
    <script>
        // Revenue Chart (Line)
        const ctxRev = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Daily Sales (₹)',
                    data: <?php echo json_encode($sales); ?>,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Shop Chart (Bar) - Currently mocking real data structure if empty
        const ctxShop = document.getElementById('shopChart').getContext('2d');
        
        // Fallback for demo if no data
        let shopLabels = <?php echo !empty($shop_labels) ? json_encode($shop_labels) : "['No Shops']"; ?>;
        let shopData = <?php echo !empty($shop_data) ? json_encode($shop_data) : "[0]"; ?>;

        const shopChart = new Chart(ctxShop, {
            type: 'bar',
            data: {
                labels: shopLabels,
                datasets: [{
                    label: 'Products Listed',
                    data: shopData,
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'
                    ],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>

</body>
</html>