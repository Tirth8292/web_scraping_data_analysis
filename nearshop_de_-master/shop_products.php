<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["shop_userid"])) {
    header("Location: shop_login.php");
    exit();
}

$user_id = $_SESSION["shop_userid"];
$shop_res = mysqli_query($conn, "SELECT shop_id FROM shops WHERE owner_user_id = '$user_id'"); // Fetch shop by user
$shop = mysqli_fetch_assoc($shop_res);

if (!$shop) {
    // If user is logged in as 'shop_owner' but has no shop data, it's an anomaly or they are actually a customer with stale session.
    // Force logout or error.
    echo "<div style='padding:40px; text-align:center;'>";
    echo "<h2>Access Denied</h2>";
    echo "<p>No Shop Profile found for this user.</p>"; 
    echo "<a href='shop_login.php'>Go to Shop Login</a>";
    echo "</div>";
    exit();
}

$shop_id = $shop['shop_id'];

// Handle Delete
if (isset($_GET['delete'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete']);
    // Ensure ownership before deleting
    mysqli_query($conn, "DELETE FROM product WHERE pr_id = '$del_id' AND shop_id = '$shop_id'");
    header("Location: shop_products.php?msg=deleted");
    exit();
}

$products = mysqli_query($conn, "SELECT * FROM product WHERE shop_id = '$shop_id' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products - Shop Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <?php include 'sidebar_shop.php'; ?>

    <div class="main-content">
        <div class="header-title">
            <h1>My Products</h1>
            <a href="shop_add_product.php" class="btn-primary" style="text-decoration: none; font-size: 0.9rem;">
                <i class="bi bi-plus-lg"></i> Add New
            </a>
        </div>

        <div class="card">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($products) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td>
                                <img src="img/products_img/<?php echo $row['pr_img_n']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td style="font-weight: 500; color: var(--text-dark);"><?php echo $row['pr_name']; ?></td>
                            <td><?php echo $row['pr_cat']; ?></td>
                            <td>₹<?php echo $row['pr_pr']; ?></td>
                            <td>
                                <?php if($row['pr_qu'] < 5): ?>
                                    <span style="background: #fee2e2; color: #b91c1c; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo $row['pr_qu']; ?> (Low)
                                    </span>
                                <?php else: ?>
                                    <span style="background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo $row['pr_qu']; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="shop_edit_product.php?id=<?php echo $row['pr_id']; ?>" style="border: 1px solid #d1d5db; background: white; border-radius: 4px; padding: 4px 8px; cursor: pointer; text-decoration: none; color: #4f46e5;">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="shop_products.php?delete=<?php echo $row['pr_id']; ?>" onclick="return confirm('Delete this product?')" style="border: 1px solid #d1d5db; background: white; border-radius: 4px; padding: 4px 8px; cursor: pointer; text-decoration: none; color: #ef4444;">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-gray); padding: 2rem;">No products listed yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
