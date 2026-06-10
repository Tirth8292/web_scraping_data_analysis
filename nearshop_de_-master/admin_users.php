<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["admin_lg_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Handle User Actions
if(isset($_GET['action']) && isset($_GET['uid'])) {
    $act = $_GET['action'];
    $uid = mysqli_real_escape_string($conn, $_GET['uid']);
    
    if($act == 'suspend') {
        mysqli_query($conn, "UPDATE users SET status='suspended' WHERE user_id='$uid'");
    } elseif($act == 'activate') {
        mysqli_query($conn, "UPDATE users SET status='active' WHERE user_id='$uid'");
    }
    // Redirect to remove query params
    $role_param = isset($_GET['role']) ? "&role=".$_GET['role'] : "";
    header("Location: admin_users.php?msg=updated$role_param");
    exit();
}

$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
$query = "SELECT * FROM users WHERE role != 'super_admin'";
if($role_filter != 'all') {
    $safe_role = mysqli_real_escape_string($conn, $role_filter);
    $query .= " AND role = '$safe_role'";
}
$query .= " ORDER BY time DESC"; // 'time' is timestamp

$users = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
         .status-badge {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-active { background: #dcfce7; color: #15803d; }
        .status-pending { background: #fef9c3; color: #a16207; }
        .status-suspended { background: #fee2e2; color: #b91c1c; }

        .role-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            background: #e0e7ff;
            color: #4338ca;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            color: #374151;
            font-size: 0.9rem;
        }
        .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
    </style>
</head>
<body>
    
    <?php include 'sidebar_admin.php'; ?>

    <div class="main-content">
        <div class="header-title">
            <h1>User Management</h1>
            <div style="display: flex; gap: 10px;">
                <a href="admin_users.php?role=all" class="filter-btn <?php echo $role_filter=='all'?'active':''; ?>">All</a>
                <a href="admin_users.php?role=customer" class="filter-btn <?php echo $role_filter=='customer'?'active':''; ?>">Customers</a>
                <a href="admin_users.php?role=shop_owner" class="filter-btn <?php echo $role_filter=='shop_owner'?'active':''; ?>">Shop Owners</a>
                <a href="admin_users.php?role=delivery_partner" class="filter-btn <?php echo $role_filter=='delivery_partner'?'active':''; ?>">Delivery</a>
            </div>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Email/Phone</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($users) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #111827;"><?php echo $row['f_name'] . " " . $row['l_name']; ?></div>
                                <small>ID: <?php echo $row['user_id']; ?></small>
                            </td>
                            <td><span class="role-badge"><?php echo ucfirst(str_replace('_', ' ', $row['role'])); ?></span></td>
                            <td>
                                <div><?php echo $row['email_id']; ?></div>
                                <small><?php echo $row['phone_no']; ?></small>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $row['status']; ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    $time = strtotime($row['time']);
                                    echo $time ? date('M d, Y', $time) : '-'; 
                                ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 'active'): ?>
                                    <a href="admin_users.php?action=suspend&uid=<?php echo $row['user_id']; ?>&role=<?php echo $role_filter; ?>" onclick="return confirm('Suspend user?')" style="color: #ef4444; text-decoration: none; font-size: 0.9rem;"><i class="bi bi-slash-circle"></i> Suspend</a>
                                <?php else: ?>
                                    <a href="admin_users.php?action=activate&uid=<?php echo $row['user_id']; ?>&role=<?php echo $role_filter; ?>" onclick="return confirm('Activate user?')" style="color: #10b981; text-decoration: none; font-size: 0.9rem;"><i class="bi bi-check-circle"></i> Activate</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-gray); padding: 2rem;">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
