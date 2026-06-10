<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["shop_userid"])) {
    header("Location: shop_login.php");
    exit();
}

$user_id = $_SESSION["shop_userid"];
$shop_res = mysqli_query($conn, "SELECT shop_id FROM shops WHERE owner_user_id = '$user_id'");
$shop = mysqli_fetch_assoc($shop_res);
$shop_id = $shop['shop_id'];

// Get Product to Edit
$pr_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : null;
if(!$pr_id) { header("Location: shop_products.php"); exit(); }

$prod_res = mysqli_query($conn, "SELECT * FROM product WHERE pr_id = '$pr_id' AND shop_id = '$shop_id'");
$product = mysqli_fetch_assoc($prod_res);
if(!$product) { echo "Product not found or access denied."; exit(); }

// Handle Update
$message = "";
if (isset($_POST['update_product'])) {
    $p_name = mysqli_real_escape_string($conn, $_POST['pr_name']);
    $p_price = mysqli_real_escape_string($conn, $_POST['pr_price']);
    $p_qty = mysqli_real_escape_string($conn, $_POST['pr_qty']);
    $p_desc = mysqli_real_escape_string($conn, $_POST['pr_desc']);
    
    // Image Upload (Optional)
    if (!empty($_FILES["pr_img"]["name"])) {
        $target_dir = "img/products_img/";
        $img_name = basename($_FILES["pr_img"]["name"]);
        $target_file = $target_dir . $img_name;
        if(move_uploaded_file($_FILES["pr_img"]["tmp_name"], $target_file)){
            $img_sql = ", pr_img = '$target_file', pr_img_n = '$img_name'";
        }
    } else {
        $img_sql = "";
    }

    $sql = "UPDATE product SET pr_name='$p_name', pr_pr='$p_price', pr_qu='$p_qty', pr_de='$p_desc' $img_sql WHERE pr_id='$pr_id' AND shop_id='$shop_id'";
    
    if (mysqli_query($conn, $sql)) {
         $message = "Product updated successfully!";
         // Refresh data
         $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM product WHERE pr_id = '$pr_id'"));
    } else {
         $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - Shop Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

    <?php include 'sidebar_shop.php'; ?>

    <div class="main-content">
        <a href="shop_products.php" style="color: #6b7280; text-decoration: none; display: inline-block; margin-bottom: 1rem;">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
        <div class="header-title">
            <h1>Edit Product</h1>
        </div>

        <?php if($message): ?>
            <div style="padding: 1rem; background: #dcfce7; color: #15803d; border-radius: 6px; margin-bottom: 1rem;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 800px;">
            <form method="post" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    
                    <div class="form-group">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="pr_name" class="form-control" value="<?php echo htmlspecialchars($product['pr_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Product ID</label>
                        <input type="text" class="form-control" value="<?php echo $product['pr_id']; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price (₹)</label>
                        <input type="number" name="pr_price" class="form-control" value="<?php echo $product['pr_pr']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="pr_qty" class="form-control" value="<?php echo $product['pr_qu']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Current Image</label>
                        <img src="img/products_img/<?php echo $product['pr_img_n']; ?>" style="height: 60px; display: block; margin-bottom: 5px;">
                        <input type="file" name="pr_img" class="form-control">
                    </div>

                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="pr_desc" class="form-control" rows="4"><?php echo htmlspecialchars($product['pr_de']); ?></textarea>
                </div>

                <button type="submit" name="update_product" class="btn-primary">Update Product</button>
            </form>
        </div>
    </div>

</body>
</html>
