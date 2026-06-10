
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-area">
            <i class="bi bi-shop-window" style="color: var(--primary);"></i>
            <span>ShopPortal</span>
        </div>
        
        <div class="nav-links">
            <a href="shop_dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'shop_dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
            <a href="shop_products.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'shop_products.php' ? 'active' : ''; ?>">
                <i class="bi bi-box-seam-fill"></i>
                <span>My Products</span>
            </a>
            <a href="shop_add_product.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'shop_add_product.php' ? 'active' : ''; ?>">
                <i class="bi bi-plus-square-fill"></i>
                <span>Add Product</span>
            </a>
            <a href="shop_orders.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'shop_orders.php' ? 'active' : ''; ?>">
                <i class="bi bi-cart-check-fill"></i>
                <span>Orders</span>
            </a>
            <a href="shop_settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'shop_settings.php' ? 'active' : ''; ?>">
                <i class="bi bi-gear-fill"></i>
                <span>Settings</span>
            </a>
        </div>
        
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=<?php echo isset($_SESSION['shop_header_name']) ? $_SESSION['shop_header_name'] : 'User'; ?>&background=random" alt="User">
            <div class="user-info">
                <div><?php echo isset($_SESSION['shop_header_name']) ? $_SESSION['shop_header_name'] : 'Shop'; ?></div>
                <small>Shop Owner</small>
            </div>
            <form action="shop_login.php" method="post" style="margin-left: auto;">
                <button name="shop_logout" value="1" style="background: none; border: none; color: #ef4444; cursor: pointer;">
                    <i class="bi bi-box-arrow-right" style="font-size: 1.2rem;"></i>
                </button>
            </form>
        </div>
    </div>
