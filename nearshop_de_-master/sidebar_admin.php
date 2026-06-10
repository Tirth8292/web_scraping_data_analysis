
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-area">
            <i class="bi bi-ui-checks-grid" style="color: var(--primary);"></i>
            <span>SuperAdmin</span>
        </div>
        
        <div class="nav-links">
            <a href="admin_home.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_home.php' ? 'active' : ''; ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
            <a href="admin_shop_approvals.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_shop_approvals.php' ? 'active' : ''; ?>">
                <i class="bi bi-shop-window"></i>
                <span>Shop Approvals</span>
            </a>
            <a href="admin_users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i>
                <span>User Management</span>
            </a>
            <a href="admin_total_pr.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_total_pr.php' ? 'active' : ''; ?>">
                <i class="bi bi-box-seam-fill"></i>
                <span>All Products</span>
            </a>
            <a href="admin_revenue.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_revenue.php' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up-arrow"></i>
                <span>Revenue Report</span>
            </a>
            <a href="admin_total_orders.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_total_orders.php' ? 'active' : ''; ?>">
                <i class="bi bi-cart-check-fill"></i>
                <span>All Orders</span>
            </a>
            <a href="#" class="nav-item">
                <i class="bi bi-gear-fill"></i>
                <span>Platform Settings</span>
            </a>
            <!-- Removed New Product Link -->
        </div>
        
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=Admin&background=random" alt="Admin">
            <div class="user-info">
                <div>Super Admin</div>
                <small><?php echo isset($_SESSION["admin_lg_id"]) ? $_SESSION["admin_lg_id"] : 'Admin'; ?></small>
            </div>
            <form action="admin_login.php" method="post" style="margin-left: auto;">
                <button name="log_out_admin" value="1" style="background: none; border: none; color: #ef4444; cursor: pointer;">
                    <i class="bi bi-box-arrow-right" style="font-size: 1.2rem;"></i>
                </button>
            </form>
        </div>
    </div>
