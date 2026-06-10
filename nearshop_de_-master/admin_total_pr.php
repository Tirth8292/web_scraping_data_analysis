<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["admin_lg_id"])) {
    header("Location: admin_login.php");
    exit();
}

// Handle Delete (Moderation)
if (isset($_POST['pr_delete'])) {
    $product_id_del = mysqli_real_escape_string($conn, $_POST['pr_delete']);
    // Log deletion or just delete
    $sql19 = "DELETE FROM `product` WHERE `pr_id` = '$product_id_del'";
    mysqli_query($conn, $sql19);
}

// Fetch All Products with Shop Details
$sql2 = "SELECT p.*, s.shop_name 
         FROM `product` p
         LEFT JOIN `shops` s ON p.shop_id = s.shop_id 
         ORDER BY p.created_at DESC";
$relq2 = mysqli_query($conn, $sql2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Products - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <?php include 'sidebar_admin.php'; ?>

    <div class="main-content">
        <div class="header-title">
            <h1>All Products (Marketplace View)</h1>
            <!-- Add New button removed -->
        </div>

        <div class="card">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Shop</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($relq2) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($relq2)): ?>
                        <tr>
                            <td><?php echo $row['pr_id']; ?></td>
                            <td>
                                <img src="img/products_img/<?php echo $row['pr_img_n']; ?>" alt="img" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td style="font-weight: 500; color: var(--text-dark);"><?php echo $row['pr_name']; ?></td>
                            <td>
                                <?php if($row['shop_name']): ?>
                                    <span style="background: #eef2ff; color: #4338ca; padding: 2px 8px; border-radius: 99px; font-size: 0.8rem; font-weight: 600;">
                                        <i class="bi bi-shop"></i> <?php echo $row['shop_name']; ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #9ca3af; font-size: 0.8rem;">(Legacy/System)</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['pr_cat']; ?></td>
                            <td>₹<?php echo $row['pr_pr']; ?></td>
                            <td>
                                <?php if($row['pr_qu'] < 5): ?>
                                    <span style="background: #fee2e2; color: #b91c1c; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo $row['pr_qu']; ?>
                                    </span>
                                <?php else: ?>
                                    <span style="background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo $row['pr_qu']; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="admin_page.php" method="post" style="display: inline;">
                                    <button name="pr_edit" value="<?php echo $row['pr_id']; ?>" style="border: 1px solid #d1d5db; background: white; border-radius: 4px; padding: 4px 8px; cursor: pointer; color: #4f46e5;" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </form>
                                <form action="admin_total_pr.php" method="post" onsubmit="return confirm('ADMIN WARNING: Are you sure you want to remove this product from the marketplace?');" style="display: inline;">
                                    <button name="pr_delete" value="<?php echo $row['pr_id']; ?>" style="border: 1px solid #d1d5db; background: white; border-radius: 4px; padding: 4px 8px; cursor: pointer;" title="Remove Product (Moderation)">
                                        <i class="bi bi-trash" style="color: #ef4444;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align: center; color: var(--text-gray);">No products found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>