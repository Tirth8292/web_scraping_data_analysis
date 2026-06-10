<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["admin_lg_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch Active Orders
$sql_active = "SELECT * FROM `order_table` ORDER BY order_date DESC";
$res_active = mysqli_query($conn, $sql_active);

// Fetch Delivered/Past Orders
$sql_history = "SELECT * FROM `delivered_orders` ORDER BY date_ch DESC"; // date_ch is delivery date? or order_date? Schema says date_ch.
$res_history = mysqli_query($conn, $sql_history);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Orders - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <?php include 'sidebar_admin.php'; ?>

    <div class="main-content">
        <div class="header-title">
            <h1>Order History</h1>
        </div>

        <!-- Tabs or Sections? Let's use Sections for simplicity -->
        
        <div class="card">
            <h3 style="margin-top: 0; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">Active Orders (Pending Delivery)</h3>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($res_active) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($res_active)): ?>
                        <tr>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                            <td style="font-weight: 600; color: #10b981;">₹<?php echo number_format($row['total_amount']); ?></td>
                            <td>
                                <!-- View Only -->
                                <form action="admin_ordered_items.php" method="post">
                                    <button name="view_items" value="<?php echo $row['order_id']; ?>" style="border: 1px solid #d1d5db; background: white; border-radius: 4px; padding: 4px 8px; cursor: pointer; color: #4f46e5;">
                                        <i class="bi bi-eye"></i> View Items
                                    </button>
                                    <input type="hidden" name="type" value="active">
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-gray);">No active orders.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="margin-top: 2rem;">
            <h3 style="margin-top: 0; color: #4b5563; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">Past Delivered Orders</h3>
             <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Order Date</th>
                        <th>Delivered Date</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($res_history) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($res_history)): ?>
                        <tr>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['date_ch'])); ?></td>
                            <td style="font-weight: 600; color: #6b7280;">₹<?php echo number_format($row['total_amount']); ?></td>
                            <td>
                                <!-- View Only -->
                                <form action="admin_ordered_items.php" method="post">
                                    <button name="view_items" value="<?php echo $row['order_id']; ?>" style="border: 1px solid #d1d5db; background: white; border-radius: 4px; padding: 4px 8px; cursor: pointer; color: #4f46e5;">
                                        <i class="bi bi-eye"></i> View Items
                                    </button>
                                    <input type="hidden" name="type" value="delivered">
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-gray);">No history found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>