<?php
session_start();
include '_db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select a Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { background: #f3f4f6; font-family: 'Inter', sans-serif; }
        .container-sel { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header-sel { text-align: center; margin-bottom: 30px; }
        .header-sel h1 { color: #1f2937; margin-bottom: 10px; }
        .btn-loc { background: #4f46e5; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 1rem; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-loc:hover { background: #4338ca; }
        
        .layout-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; height: 600px; }
        @media (max-width: 768px) { .layout-grid { grid-template-columns: 1fr; height: auto; } #map-container { height: 300px; } }

        #shop-list { background: white; border-radius: 12px; overflow-y: auto; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); height: 100%; box-sizing: border-box; }
        #map-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); height: 100%; position: relative; }
        #map { width: 100%; height: 100%; }

        .shop-card { display: flex; align-items: center; gap: 15px; padding: 15px; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 15px; cursor: pointer; transition: 0.2s; }
        .shop-card:hover { border-color: #4f46e5; background: #f9fafb; transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .shop-card img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; }
        .shop-info h3 { margin: 0 0 5px; font-size: 1.1rem; color: #111827; }
        .shop-info p { margin: 0; color: #6b7280; font-size: 0.9rem; }
        .shop-dist { margin-left: auto; text-align: right; font-weight: 600; color: #4f46e5; min-width: 60px; }

        .loading-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(255,255,255,0.8); display:flex; justify-content:center; align-items:center; z-index: 9999; visibility: hidden; opacity: 0; transition: 0.3s; }
        .loading-overlay.show { visibility: visible; opacity: 1; }
        .spinner { width: 40px; height: 40px; border: 4px solid #e5e7eb; border-top: 4px solid #4f46e5; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="loading-overlay" id="loader">
    <div class="spinner"></div>
</div>

<div class="container-sel">
    <div class="header-sel">
        <h1>Select a Store</h1>
        <p>Choose a nearby store to see available products.</p>
        <button class="btn-loc" onclick="getLocation()"><i class="bi bi-geo-alt-fill"></i> Find Nearest Shops</button>
    </div>

    <div class="layout-grid">
        <div id="shop-list">
            <h3 style="margin-top: 0; margin-bottom: 15px; color: #374151;">Available Shops</h3>
            <div id="list-content">
                <p style="color: #6b7280; text-align: center; margin-top: 50px;">Use valid location to see nearby shops.</p>
            </div>
        </div>
        <div id="map-container">
            <div id="map"></div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([20.5937, 78.9629], 5);
    
    // Custom Icons
    var userIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    var shopIcon = new L.Icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var markers = [];
    var shopsData = [];

    // On Load, fetch all shops (top 20)
    // On Load, try to get user location immediately for "already zoom" experience
    document.addEventListener("DOMContentLoaded", function() {
        if(<?php echo isset($_SESSION['user_lat']) ? 'true' : 'false'; ?>) {
            // If we have session data, use it
            var sLat = <?php echo isset($_SESSION['user_lat']) ? $_SESSION['user_lat'] : 'null'; ?>;
            var sLng = <?php echo isset($_SESSION['user_lng']) ? $_SESSION['user_lng'] : 'null'; ?>;
            fetchShops(sLat, sLng);
        } else {
            // Otherwise try to auto-geolocate
            getLocation();
        }
    });

    function getLocation() {
        showLoader(true);
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                fetchShops(position.coords.latitude, position.coords.longitude);
            }, error => {
                 alert("Geolocation permission denied or error. Showing all shops.");
                 fetchShops();
                 showLoader(false);
            });
        } else {
            alert("Geolocation is not supported by this browser.");
            fetchShops();
        }
    }

    function fetchShops(lat = null, lng = null) {
        showLoader(true);
        let url = 'api_get_nearby_shops.php';
        if (lat && lng) {
            url += `?lat=${lat}&lng=${lng}`;
            // Move map
            // Move map
            map.setView([lat, lng], 14);
            // Add user marker
            L.marker([lat, lng], {icon: userIcon}).addTo(map).bindPopup("<b>You are Here</b>").openPopup();
        }

        fetch(url)
            .then(res => res.json())
            .then(data => {
                showLoader(false);
                if (data.status === 'success') {
                    renderShops(data.shops);
                } else {
                    console.error(data.message);
                }
            })
            .catch(err => {
                showLoader(false);
                console.error(err);
            });
    }

    function renderShops(shops) {
        shopsData = shops;
        const listContainer = document.getElementById('list-content');
        listContainer.innerHTML = '';
        
        // Clear markers
        markers.forEach(m => map.removeLayer(m));
        markers = [];

        if (shops.length === 0) {
            listContainer.innerHTML = '<p style="text-align:center;color:gray;">No shops found.</p>';
            return;
        }

        shops.forEach(shop => {
            // Add to list
            let distHtml = shop.distance ? `<div class="shop-dist">${shop.distance} km</div>` : '';
            let html = `
                <div class="shop-card" onclick="selectShop(${shop.shop_id})">
                    <img src="img/shop_uploads/${shop.shop_image}" onerror="this.src='img/products_img/default.png'"> <!-- Fallback image need -->
                    <div class="shop-info">
                        <h3>${shop.shop_name}</h3>
                        <p>${shop.shop_address}</p>
                    </div>
                    ${distHtml}
                </div>
            `;
            listContainer.innerHTML += html;

            // Add marker
            if (shop.latitude && shop.longitude) {
                let m = L.marker([shop.latitude, shop.longitude], {icon: shopIcon})
                    .addTo(map)
                    .bindPopup(`<b>${shop.shop_name}</b><br>${shop.shop_address}<br><button onclick="selectShop(${shop.shop_id})" style="margin-top:5px;cursor:pointer;">Select Shop</button>`);
                markers.push(m);
            }
        });
    }

    function selectShop(id) {
        if (confirm("Select this shop? If you have items in your cart from another shop, they will be removed.")) {
            showLoader(true);
            fetch('api_set_shop.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ shop_id: id, force: true })
            })
            .then(res => res.json())
            .then(data => {
                showLoader(false);
                if (data.status === 'success') {
                    window.location.href = 'index.php';
                } else if (data.status === 'conflict') {
                   // Should handle via force parameter, but we passed force=true
                   alert(data.message);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                showLoader(false);
                console.error(err);
            });
        }
    }

    function showLoader(show) {
        const l = document.getElementById('loader');
        if (show) l.classList.add('show');
        else l.classList.remove('show');
    }

</script>
</body>
</html>
