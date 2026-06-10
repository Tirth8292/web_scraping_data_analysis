<?php
session_start();
include '_db_connect.php';

$error = "";
$success = "";

if (isset($_POST['register_shop'])) {
    // 1. Collect User Inputs
    $fname = mysqli_real_escape_string($conn, $_POST['f_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['l_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $password = $_POST['password']; // Plaintext for legacy consistency
    $confirm_password = $_POST['confirm_password'];
    
    // 2. Collect Shop Inputs
    $shop_name = mysqli_real_escape_string($conn, $_POST['shop_name']);
    $shop_category = mysqli_real_escape_string($conn, $_POST['shop_category']);
    $shop_address = mysqli_real_escape_string($conn, $_POST['shop_address']);
    
    // Geolocation
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : 'NULL';
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : 'NULL';

    // 3. Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if User ID exists
        $check_user = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'");
        if (mysqli_num_rows($check_user) > 0) {
            $error = "User ID already exists.";
        } else {
            // 4. File Upload Handling
            $upload_err = "";
            $shop_img_name = "";
            $shop_int_name = "";
            $shop_ext_name = "";
            $doc_name = "";
            
            // Helper function for upload
            function uploadFile($file, $target_dir, $prefix) {
                if (isset($file) && $file['error'] == 0) {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $new_name = $prefix . "_" . time() . "_" . uniqid() . "." . $ext;
                    if (move_uploaded_file($file['tmp_name'], $target_dir . $new_name)) {
                        return $new_name;
                    }
                }
                return false;
            }

            // Main Shop Image
            $shop_img_name = uploadFile($_FILES['shop_image'], "img/shop_uploads/", "shop_main");
            if (!$shop_img_name) $upload_err = "Main shop image is required.";
            
            // Interior Image
            $shop_int_name = uploadFile($_FILES['shop_image_interior'], "img/shop_uploads/interior/", "shop_int");
            // Exterior Image
            $shop_ext_name = uploadFile($_FILES['shop_image_exterior'], "img/shop_uploads/exterior/", "shop_ext");

            // Document
            $doc_name = uploadFile($_FILES['shop_document'], "img/shop_documents/", "doc");
            if (!$doc_name) $upload_err = "Shop verification document is required.";

            if ($upload_err) {
                $error = $upload_err;
            } else {
                // 5. Insert Data
                // Insert User (Pending)
                $sql_user = "INSERT INTO `users` (`f_name`, `l_name`, `user_id`, `email_id`, `phone_no`, `user_password`, `role`, `status`) 
                             VALUES ('$fname', '$lname', '$user_id', '$email', '$phone', '$password', 'shop_owner', 'pending')";
                
                if (mysqli_query($conn, $sql_user)) {
                    // Handle lat/long which are numeric or NULL
                    $lat_val = ($latitude === 'NULL') ? "NULL" : "'$latitude'";
                    $long_val = ($longitude === 'NULL') ? "NULL" : "'$longitude'";
                    
                    // Insert Shop
                    $sql_shop = "INSERT INTO `shops` (`owner_user_id`, `shop_name`, `shop_address`, `shop_category`, `shop_image`, `shop_image_interior`, `shop_image_exterior`, `latitude`, `longitude`) 
                                 VALUES ('$user_id', '$shop_name', '$shop_address', '$shop_category', '$shop_img_name', '$shop_int_name', '$shop_ext_name', $lat_val, $long_val)";
                    
                    if (mysqli_query($conn, $sql_shop)) {
                        $shop_db_id = mysqli_insert_id($conn);
                        
                        // Insert Document
                        $sql_doc = "INSERT INTO `shop_documents` (`shop_id`, `doc_name`, `doc_path`) 
                                    VALUES ('$shop_db_id', 'Registration Cert', '$doc_name')";
                        mysqli_query($conn, $sql_doc);

                        $success = "Registration Successful! Your account is pending approval from the Admin.";
                    } else {
                        $error = "Error creating shop profile: " . mysqli_error($conn);
                        mysqli_query($conn, "DELETE FROM users WHERE user_id='$user_id'"); // Rollback
                    }
                } else {
                    $error = "Error creating user account: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Registration</title>
    <link rel="stylesheet" href="css/style3.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #fae8ff 100%);
            min-height: 100vh;
            padding: 2rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container1 {
            background: #ffffff;
            box-shadow: 0 10px 40px -10px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            max-width: 800px;
            padding: 0;
            /* overflow: hidden; */
            border-radius: 16px;
            margin: 2rem auto;
        }

        .form-header {
            background: linear-gradient(to right, #4f46e5, #4338ca);
            padding: 2rem;
            color: white;
            text-align: center;
        }

        .form-header h2 {
            margin: 0;
            font-size: 1.8rem;
            color: white;
        }
        
        .form-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }

        /* .form-body { padding: 2.5rem; } */

        .section-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
        }
        .section-title i { color: #4f46e5; font-size: 1.25rem; }

        .split-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .full-width { grid-column: span 2; }
        @media (max-width: 600px) {
            .split-form { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
            .form-body { padding: 1.5rem; }
        }

        .lo-lab label {
            color: #374151;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
            display: block;
        }

        .lo-inp input, .lo-inp select {
            background: #ffffff;
            border: 1px solid #d1d5db;
            color: #111827;
            width: 100%;
            padding: 0.75rem;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .file-upload-box {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
        }
        .file-upload-box:hover {
            border-color: #4f46e5;
            background: #f5f3ff;
        }

        #map {
            height: 300px;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            margin-top: 0.5rem;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }
    </style>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>
<body>
    <div class="container1">
        <div class="form-header">
            <h2><i class="bi bi-shop-window"></i> Agri Store Registration</h2>
            <p>Join Krishi Market and sell farming supplies to local farmers.</p>
        </div>
        
        <div class="form-body">
            
            <?php if ($error): ?>
                <div style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; padding: 2rem; border-radius: 12px; text-align: center;">
                    <i class="bi bi-check-circle-fill" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem; display: block;"></i>
                    <h3 style="margin: 0 0 0.5rem; font-size: 1.5rem;">Registration Submitted!</h3>
                    <p style="color: #065f46; margin-bottom: 1.5rem;">Your account is pending approval from the Admin. You will be notified once approved.</p>
                    <a href="shop_login.php" class="btn" style="background: #10b981; text-decoration: none; display: inline-block; width: auto; padding: 0.75rem 2rem;">Go to Shop Login</a>
                </div>
            <?php else: ?>

            <form action="shop_register.php" method="post" enctype="multipart/form-data">
                
                <!-- Section 1: Owner Details -->
                <div class="section-box">
                    <div class="section-title"><i class="bi bi-person-circle"></i> Owner Details</div>
                    <div class="split-form">
                        <div class="content-form"><div class="lo-lab"><label>First Name <span style="color:red">*</span></label></div><div class="lo-inp"><input type="text" name="f_name" required></div></div>
                        <div class="content-form"><div class="lo-lab"><label>Last Name <span style="color:red">*</span></label></div><div class="lo-inp"><input type="text" name="l_name" required></div></div>
                    </div>
                    <div class="split-form">
                        <div class="content-form"><div class="lo-lab"><label>Email <span style="color:red">*</span></label></div><div class="lo-inp"><input type="email" name="email" required></div></div>
                        <div class="content-form"><div class="lo-lab"><label>Phone <span style="color:red">*</span></label></div><div class="lo-inp"><input type="text" name="phone" required></div></div>
                    </div>
                    <div class="content-form">
                        <div class="lo-lab"><label>User ID <span style="color:red">*</span></label></div>
                        <div class="lo-inp"><input type="text" name="user_id" required style="background: #eff6ff;"></div>
                    </div>
                    <div class="split-form">
                        <div class="content-form"><div class="lo-lab"><label>Password <span style="color:red">*</span></label></div><div class="lo-inp"><input type="password" name="password" required></div></div>
                        <div class="content-form"><div class="lo-lab"><label>Confirm <span style="color:red">*</span></label></div><div class="lo-inp"><input type="password" name="confirm_password" required></div></div>
                    </div>
                </div>

                <!-- Section 2: Shop Details -->
                <div class="section-box">
                    <div class="section-title"><i class="bi bi-shop"></i> Shop Details</div>
                    <div class="split-form">
                        <div class="content-form full-width"><div class="lo-lab"><label>Shop Name <span style="color:red">*</span></label></div><div class="lo-inp"><input type="text" name="shop_name" required></div></div>
                        <div class="content-form full-width">
                            <div class="lo-lab"><label>Category <span style="color:red">*</span></label></div>
                            <div class="lo-inp">
                                <select name="shop_category" required>
                                    <option value="" disabled selected>Select a Category</option>
                                    <option value="Pesticide Dealer">Pesticide Dealer</option>
                                    <option value="Fertilizer Supplier">Fertilizer Supplier</option>
                                    <option value="Seed Store">Seed Store</option>
                                    <option value="Farm Equipment">Farm Equipment</option>
                                    <option value="Irrigation Systems">Irrigation Systems</option>
                                    <option value="General Agri Store">General Agri Store</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Location -->
                    <div class="split-form" style="margin-top: 1rem;">
                        <div class="content-form full-width">
                            <div class="lo-lab"><label>Shop Address <span style="color:red">*</span></label></div>
                            <div class="lo-inp"><input type="text" name="shop_address" placeholder="e.g. 123 Street Name" required></div>
                        </div>
                    </div>
                    
                    <div class="content-form full-width" style="margin-top: 1rem;">
                        <div class="lo-lab"><label>Mark Shop on Map <span style="color:red">*</span></label></div>
                        <small style="color: #6b7280; margin-bottom: 5px; display:block;">Drag the marker to your shop's exact location.</small>
                        <div id="map"></div>
                        <input type="hidden" id="lat" name="latitude">
                        <input type="hidden" id="lng" name="longitude">
                    </div>
                </div>
                
                <!-- Section 3: Visuals & Docs -->
                <div class="section-box">
                    <div class="section-title"><i class="bi bi-images"></i> Visuals & Documents</div>
                    
                    <div class="split-form">
                         <!-- Main Image -->
                         <div class="content-form">
                            <div class="lo-lab"><label>Shop Main Image <span style="color:red">*</span></label></div>
                            <div class="file-upload-box" onclick="document.getElementById('shop_img_in').click()">
                                <i class="bi bi-card-image" style="font-size: 2rem; color: #9ca3af;"></i>
                                <p style="margin: 5px 0 0; font-size: 0.8rem;">Main Image (Thumb)</p>
                                <input type="file" id="shop_img_in" name="shop_image" accept="image/*" required style="display: none;" onchange="this.previousElementSibling.innerText = this.files[0].name">
                            </div>
                         </div>
                         <!-- Interior -->
                         <div class="content-form">
                            <div class="lo-lab"><label>Interior Image</label></div>
                             <div class="file-upload-box" onclick="document.getElementById('shop_int_in').click()">
                                <i class="bi bi-house-door" style="font-size: 2rem; color: #9ca3af;"></i>
                                <p style="margin: 5px 0 0; font-size: 0.8rem;">Interior View</p>
                                <input type="file" id="shop_int_in" name="shop_image_interior" accept="image/*" style="display: none;" onchange="this.previousElementSibling.innerText = this.files[0].name">
                            </div>
                         </div>
                         <!-- Exterior -->
                         <div class="content-form">
                            <div class="lo-lab"><label>Exterior Image</label></div>
                             <div class="file-upload-box" onclick="document.getElementById('shop_ext_in').click()">
                                <i class="bi bi-building" style="font-size: 2rem; color: #9ca3af;"></i>
                                <p style="margin: 5px 0 0; font-size: 0.8rem;">Exterior View</p>
                                <input type="file" id="shop_ext_in" name="shop_image_exterior" accept="image/*" style="display: none;" onchange="this.previousElementSibling.innerText = this.files[0].name">
                            </div>
                         </div>
                         <!-- Docs -->
                         <div class="content-form">
                            <div class="lo-lab"><label>Legal Document <span style="color:red">*</span></label></div>
                             <div class="file-upload-box" onclick="document.getElementById('shop_doc_in').click()">
                                <i class="bi bi-file-earmark-lock" style="font-size: 2rem; color: #9ca3af;"></i>
                                <p style="margin: 5px 0 0; font-size: 0.8rem;">License/Cert (PDF)</p>
                                <input type="file" id="shop_doc_in" name="shop_document" accept=".pdf,image/*" required style="display: none;" onchange="this.previousElementSibling.innerText = this.files[0].name">
                            </div>
                         </div>
                    </div>
                </div>

                <div class="content-form" id="btn-l-r">
                    <button class="btn" name="register_shop" value="1">Submit Registration</button>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="shop_login.php" style="color: #4f46e5; font-weight: 500; text-decoration: none;">Back to Login</a>
                </div>

            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Initialize Leaflet Map
        // Default to some location (e.g. Center of India or User location)
        var map = L.map('map').setView([20.5937, 78.9629], 5); // India center

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker;

        // Try to get user location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;
                var latlng = [lat, lng];
                
                map.setView(latlng, 15);
                setMarker(latlng);
            });
        }

        map.on('click', function(e) {
            setMarker(e.latlng);
        });

        function setMarker(latlng) {
            if (marker) {
                marker.setLatLng(latlng);
            } else {
                marker = L.marker(latlng, {draggable: true}).addTo(map);
                marker.on('dragend', function(event) {
                    var marker = event.target;
                    var position = marker.getLatLng();
                    updateInput(position);
                });
            }
            updateInput(latlng);
        }

        function updateInput(latlng) {
            document.getElementById('lat').value = latlng.lat;
            document.getElementById('lng').value = latlng.lng;
        }
    </script>
</body>
</html>
