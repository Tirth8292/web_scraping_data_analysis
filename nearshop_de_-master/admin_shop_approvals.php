<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["admin_lg_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch pending shop owners
$sql = "SELECT u.user_id, u.f_name, u.l_name, s.shop_name, s.shop_category, s.shop_id, s.created_at 
        FROM users u 
        JOIN shops s ON u.user_id = s.owner_user_id 
        WHERE u.role = 'shop_owner' AND u.status = 'pending'
        ORDER BY s.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Shop Approvals - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        .btn-view {
            background: #4f46e5;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-view:hover { background: #4338ca; }
        .empty-state {
            padding: 3rem;
            text-align: center;
            color: #6b7280;
        }
    </style>
</head>
<body>
    
    <?php include 'sidebar_admin.php'; ?>

    <div class="main-content">
        <div class="header-title">
            <h1>Pending Shop Approvals</h1>
        </div>

        <div class="card">
            <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Shop Name</th>
                        <th>Owner</th>
                        <th>Category</th>
                        <th>Registered At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td style="font-weight: 500; color: #111827;"><?php echo $row['shop_name']; ?></td>
                        <td>
                            <?php echo $row['f_name'] . ' ' . $row['l_name']; ?><br>
                            <small style="color: #9ca3af;"><?php echo $row['user_id']; ?></small>
                        </td>
                        <td><span style="background: #eef2ff; color: #4f46e5; padding: 2px 8px; border-radius: 99px; font-size: 0.85rem;"><?php echo $row['shop_category']; ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="admin_shop_details.php?shop_id=<?php echo $row['shop_id']; ?>" class="btn-view">
                                <i class="bi bi-eye"></i> View Details
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-check2-all" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"></i>
                    <h3>No pending approvals</h3>
                    <p>All shop registrations have been processed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
