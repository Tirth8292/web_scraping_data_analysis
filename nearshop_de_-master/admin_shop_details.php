<?php
session_start();
include '_db_connect.php';

if (!isset($_SESSION["admin_lg_id"])) {
    header("Location: admin_login.php");
    exit();
}

$shop_id = $_GET['shop_id'];
if (!$shop_id) {
    echo "Invalid Shop ID";
    exit();
}

// Handle Actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $owner_id = $_POST['owner_id'];
    
    if ($action === 'approve') {
        $upd_user = "UPDATE users SET status = 'active' WHERE user_id = '$owner_id'";
        mysqli_query($conn, $upd_user);
        echo "<script>alert('Shop Approved!'); window.location.href='admin_shop_approvals.php';</script>";
        exit();
    } elseif ($action === 'reject') {
        $upd_user = "UPDATE users SET status = 'suspended' WHERE user_id = '$owner_id'";
        mysqli_query($conn, $upd_user);
        echo "<script>alert('Shop Rejected!'); window.location.href='admin_shop_approvals.php';</script>";
        exit();
    } elseif ($action === 'suspend') {
        $upd_user = "UPDATE users SET status = 'suspended' WHERE user_id = '$owner_id'";
        mysqli_query($conn, $upd_user);
        echo "<script>alert('Shop Suspended! Owner can no longer login.'); window.location.reload();</script>";
        exit();
    } elseif ($action === 'reactivate') {
            $upd_user = "UPDATE users SET status = 'active' WHERE user_id = '$owner_id'";
        mysqli_query($conn, $upd_user);
        echo "<script>alert('Shop Reactivated!'); window.location.reload();</script>";
        exit();
    }
}

// Fetch Details
$sql = "SELECT u.user_id, u.f_name, u.l_name, u.email_id, u.phone_no, u.status,
                s.shop_name, s.shop_category, s.shop_address, s.shop_image, s.shop_image_interior, s.shop_image_exterior, s.latitude, s.longitude, s.created_at
        FROM shops s 
        JOIN users u ON s.owner_user_id = u.user_id 
        WHERE s.shop_id = '$shop_id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "Shop not found.";
    exit();
}

// Fetch Documents
$doc_sql = "SELECT * FROM shop_documents WHERE shop_id = '$shop_id'";
$doc_res = mysqli_query($conn, $doc_sql);

// Fetch products logic
$prod_sql = "SELECT p.*, 
            (SELECT SUM(quantity) FROM order_items WHERE pr_id = p.pr_id) as sold_count 
            FROM product p 
            WHERE p.shop_id = '$shop_id'";
$prod_res = mysqli_query($conn, $prod_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($data['shop_name']); ?> - Admin Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin_style.css">
    
    <!-- Use inline styles for page-specific overrides -->
    <style>
        /* Page Specific */
        .card { 
            background: white; 
            padding: 1.5rem; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            margin-bottom: 2rem; 
        }
        .card h3 {
            margin-top: 0;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            color: #111827;
        }
        .info-row { display: flex; margin-bottom: 0.75rem; border-bottom: 1px dashed #f3f4f6; padding-bottom: 5px; }
        .info-label { width: 150px; font-weight: 600; color: #4b5563; }
        .info-val { color: #111827; flex: 1; }
        
        .img-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
        .img-box { border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; }
        .img-box img { width: 100%; height: 120px; object-fit: cover; display: block; }
        .img-label { background: #f9fafb; padding: 5px; text-align: center; font-size: 0.8rem; color: #6b7280; border-top: 1px solid #e5e7eb; }
        
        #map { height: 300px; border-radius: 8px; width: 100%; }
        
        .btn-approve { background: #10b981; color: white; border: none; padding: 1rem 2rem; border-radius: 8px; font-size: 1.1rem; cursor: pointer; width: 100%; transition: background 0.2s; }
        .btn-approve:hover { background: #059669; }
        
        .btn-reject { background: #ef4444; color: white; border: none; padding: 1rem 2rem; border-radius: 8px; font-size: 1.1rem; cursor: pointer; width: 100%; transition: background 0.2s; }
        .btn-reject:hover { background: #dc2626; }

        /* Sidebar include fix */
        /* If sidebar_admin.php brings its own css, these might overlap but that's fine */
    </style>
     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
     <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
</head>
<body>

    <?php include 'sidebar_admin.php'; ?>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
             <a href="admin_revenue.php" style="color: #6b7280; text-decoration: none; font-weight: 500;">
                <i class="bi bi-arrow-left"></i> Back to Revenue
            </a>
            
            <!-- Status Badge -->
            <?php if($data['status'] == 'active'): ?>
                <span style="background: #dcfce7; color: #166534; padding: 5px 15px; border-radius: 20px; font-weight: 600;">Active Shop</span>
            <?php elseif($data['status'] == 'suspended'): ?>
                <span style="background: #fee2e2; color: #991b1b; padding: 5px 15px; border-radius: 20px; font-weight: 600;">Suspended</span>
            <?php else: ?>
                 <span style="background: #fff7ed; color: #9a3412; padding: 5px 15px; border-radius: 20px; font-weight: 600;">Pending Application</span>
            <?php endif; ?>
        </div>
        
        <h2 style="margin-top: 0; margin-bottom: 2rem;"><?php echo htmlspecialchars($data['shop_name']); ?> Details</h2>

        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
             <!-- Shop Info -->
             <div class="card" style="flex: 1; min-width: 300px;">
                <h3><i class="bi bi-shop"></i> Shop Information</h3>
                <div class="info-row"><div class="info-label">Category:</div><div class="info-val"><?php echo $data['shop_category']; ?></div></div>
                <div class="info-row"><div class="info-label">Address:</div><div class="info-val"><?php echo $data['shop_address']; ?></div></div>
                <div class="info-row"><div class="info-label">Created On:</div><div class="info-val"><?php echo date('F j, Y, g:i a', strtotime($data['created_at'])); ?></div></div>
            </div>

            <!-- Owner Info -->
            <div class="card" style="flex: 1; min-width: 300px;">
                <h3><i class="bi bi-person-circle"></i> Owner Details</h3>
                <div class="info-row"><div class="info-label">Name:</div><div class="info-val"><?php echo $data['f_name'] . ' ' . $data['l_name']; ?></div></div>
                <div class="info-row"><div class="info-label">User ID:</div><div class="info-val"><?php echo $data['user_id']; ?></div></div>
                <div class="info-row"><div class="info-label">Email:</div><div class="info-val"><?php echo $data['email_id']; ?></div></div>
                <div class="info-row"><div class="info-label">Phone:</div><div class="info-val"><?php echo $data['phone_no']; ?></div></div>
            </div>
        </div>

        <!-- Images -->
        <div class="card">
             <h3><i class="bi bi-images"></i> Shop Images</h3>
             <div class="img-grid">
                <div class="img-box">
                    <a href="img/shop_uploads/<?php echo $data['shop_image']; ?>" target="_blank">
                        <img src="img/shop_uploads/<?php echo $data['shop_image']; ?>">
                    </a>
                    <div class="img-label">Main Image</div>
                </div>
                <?php if($data['shop_image_interior']): ?>
                <div class="img-box">
                     <a href="img/shop_uploads/interior/<?php echo $data['shop_image_interior']; ?>" target="_blank">
                        <img src="img/shop_uploads/interior/<?php echo $data['shop_image_interior']; ?>">
                     </a>
                    <div class="img-label">Interior</div>
                </div>
                <?php endif; ?>
                <?php if($data['shop_image_exterior']): ?>
                <div class="img-box">
                     <a href="img/shop_uploads/exterior/<?php echo $data['shop_image_exterior']; ?>" target="_blank">
                        <img src="img/shop_uploads/exterior/<?php echo $data['shop_image_exterior']; ?>">
                     </a>
                    <div class="img-label">Exterior</div>
                </div>
                <?php endif; ?>
             </div>
        </div>
        
        <!-- Docs -->
        <div class="card">
            <h3><i class="bi bi-file-earmark-lock"></i> Documents</h3>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <?php while($doc = mysqli_fetch_assoc($doc_res)): ?>
                <div style="padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-file-pdf" style="color: red; font-size: 1.2rem;"></i> 
                    <div>
                        <div style="font-weight: 500; font-size: 0.9rem;"><?php echo $doc['doc_name']; ?></div>
                        <a href="img/shop_documents/<?php echo $doc['doc_path']; ?>" target="_blank" style="color: #4f46e5; font-size: 0.8rem;">View Document</a>
                    </div>
                </div>
            <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Location -->
        <div class="card">
            <h3><i class="bi bi-geo-alt"></i> Shop Location</h3>
             <div id="map"></div>
        </div>
        
        <!-- Actions Area -->
        <div class="card" style="border-left: 5px solid <?php echo ($data['status'] == 'active') ? '#ef4444' : '#10b981'; ?>;">
            <h3>Actions</h3>
            <form method="post" onsubmit="return confirm('Please confirm this action. It will affect the shop access immediately.');">
                <input type="hidden" name="owner_id" value="<?php echo $data['user_id']; ?>">
                
                <?php if($data['status'] == 'pending'): ?>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="action" value="approve" class="btn-approve" style="flex:1;"><i class="bi bi-check-lg"></i> Approve Application</button>
                        <button type="submit" name="action" value="reject" class="btn-reject" style="flex:1;"><i class="bi bi-x-lg"></i> Reject Application</button>
                    </div>
                <?php elseif($data['status'] == 'active'): ?>
                    <p style="color: #ef4444; margin-bottom: 1rem;">Suspend this shop to prevent the user from logging in. Commission calculations/payouts may also be halted.</p>
                    <button type="submit" name="action" value="suspend" class="btn-reject" style="background: #ef4444; width: 100%;"><i class="bi bi-slash-circle"></i> Suspend Shop</button>
                <?php elseif($data['status'] == 'suspended'): ?>
                    <p style="color: #6b7280; margin-bottom: 1rem;">This shop is currently suspended.</p>
                    <button type="submit" name="action" value="reactivate" class="btn-approve" style="background: #10b981; width: 100%;"><i class="bi bi-arrow-repeat"></i> Reactivate Shop</button>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Products Section -->
        <div class="card" style="margin-top: 2rem;">
            <h3><i class="bi bi-box-seam"></i> Shop Products & Performance</h3>

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                     <tr style="text-align: left; border-bottom: 2px solid #f3f4f6;">
                        <th style="padding: 10px;">Product</th>
                        <th style="padding: 10px;">Price</th>
                        <th style="padding: 10px;">Stock</th>
                        <th style="padding: 10px;">Total Sold</th>
                        <th style="padding: 10px;">Action</th>
                     </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($prod_res) > 0): ?>
                        <?php while($prod = mysqli_fetch_assoc($prod_res)): ?>
                        <tr style="border-bottom: 1px solid #f9fafb;">
                            <td style="padding: 10px; display: flex; align-items: center; gap: 10px;">
                                <img src="img/products_img/<?php echo $prod['pr_img_n']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                <div>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($prod['pr_name']); ?></div>
                                    <small style="color: #9ca3af;">ID: <?php echo $prod['pr_id']; ?></small>
                                </div>
                            </td>
                            <td style="padding: 10px;">₹<?php echo $prod['pr_pr']; ?></td>
                            <td style="padding: 10px;">
                                <?php if($prod['pr_qu'] < 5): ?>
                                    <span style="color: #ef4444; font-weight: 600;"><?php echo $prod['pr_qu']; ?> (Low)</span>
                                <?php else: ?>
                                    <?php echo $prod['pr_qu']; ?>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px; font-weight: 600; color: #4f46e5;"><?php echo $prod['sold_count'] ?? 0; ?></td>
                            <td style="padding: 10px;">
                                <a href="admin_product_analytics.php?pr_id=<?php echo $prod['pr_id']; ?>" style="color: #4f46e5; background: #eef2ff; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 500;">
                                    <i class="bi bi-graph-up"></i> View Analytics
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="padding: 20px; text-align: center; color: #6b7280;">No products found for this shop.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        var lat = <?php echo $data['latitude'] ? $data['latitude'] : '20.5937'; ?>;
        var lng = <?php echo $data['longitude'] ? $data['longitude'] : '78.9629'; ?>;
        var hasLocation = <?php echo $data['latitude'] ? 'true' : 'false'; ?>;

        var map = L.map('map').setView([lat, lng], hasLocation ? 15 : 5);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        if (hasLocation) {
            L.marker([lat, lng]).addTo(map)
                .bindPopup("<b><?php echo htmlspecialchars($data['shop_name']); ?></b><br><?php echo htmlspecialchars($data['shop_address']); ?>")
                .openPopup();
        }
    </script>
</body>
</html>
