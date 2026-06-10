<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["admin_lg_id"])) {
    header("Location: admin_login.php");
    exit();
}

$pr_ed_status = false;
$row = [];

// If not viewing/editing, redirect back because Admin can't Add.
if (!isset($_POST['pr_edit'])) {
    header("Location: admin_total_pr.php");
    exit();
}

if (isset($_POST['pr_edit'])) {
    $pr_id_edit = $_POST['pr_edit'];
    // Fetch with Shop Name
    $sql19 = "SELECT p.*, s.shop_name FROM `product` p LEFT JOIN shops s ON p.shop_id = s.shop_id WHERE p.`pr_id` = '$pr_id_edit'";
    $pr_edit_result = mysqli_query($conn,$sql19);
    $row = mysqli_fetch_assoc($pr_edit_result);
    $pr_ed_status = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Product - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <?php include 'sidebar_admin.php'; ?>

    <div class="main-content">
        <a href="admin_total_pr.php" style="color: #6b7280; text-decoration: none; display: inline-block; margin-bottom: 1rem;">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
        <div class="header-title">
            <h1>Product Details (Read Only)</h1>
        </div>

        <div class="card" style="max-width: 800px;">
            <form>
                
                <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 1.5rem;">
                     Viewing Product #<?php echo $row['pr_id']; ?> from Shop: <strong><?php echo htmlspecialchars($row['shop_name'] ?? 'Unknown'); ?></strong>
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    
                    <div class="form-group">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" value="<?php echo $row['pr_name']; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Product ID</label>
                        <input type="text" class="form-control" value="<?php echo $row['pr_id']; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price</label>
                        <input type="text" class="form-control" value="<?php echo $row['pr_pr']; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Quantity</label>
                        <input type="text" class="form-control" value="<?php echo $row['pr_qu']; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Brand</label>
                        <input type="text" class="form-control" value="<?php echo $row['pr_brand']; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-control" disabled>
                            <option value="1" <?php echo ($row['pr_cat']==1) ? 'selected' : ''; ?>>Category 1</option>
                            <option value="2" <?php echo ($row['pr_cat']==2) ? 'selected' : ''; ?>>Category 2</option>
                            <option value="3" <?php echo ($row['pr_cat']==3) ? 'selected' : ''; ?>>Category 3</option>
                            <option value="4" <?php echo ($row['pr_cat']==4) ? 'selected' : ''; ?>>Category 4</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" value="<?php echo $row['pr_co']; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Product Image</label>
                         <div style="margin-bottom:5px;">
                             <img src="img/products_img/<?php echo $row['pr_img_n']; ?>" style="height: 100px; border-radius: 8px; border: 1px solid #e5e7eb;">
                         </div>
                    </div>

                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="4" readonly><?php echo $row['pr_de']; ?></textarea>
                </div>

                <!-- No Submit Button -->

            </form>
        </div>
    </div>

</body>
</html>