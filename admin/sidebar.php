<!-- Sidebar -->
<nav id="sidebar" class="col-md-3 col-lg-3 d-md-block bg-dark sidebar">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h5 class="text-white">Admin Dashboard</h5>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'add_product.php' || basename($_SERVER['PHP_SELF']) == 'edit_product.php' ? 'active' : ''; ?>" href="products.php">
                    <i class="bi bi-box"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                    <i class="bi bi-cart"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' || basename($_SERVER['PHP_SELF']) == 'customer_profile.php' || basename($_SERVER['PHP_SELF']) == 'customer_orders.php' ? 'active' : ''; ?>" href="customers.php">
                    <i class="bi bi-people"></i> Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                    <i class="bi bi-tags"></i> Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' || basename($_SERVER['PHP_SELF']) == 'message_view.php' ? 'active' : ''; ?>" href="messages.php">
                    <i class="bi bi-envelope"></i> Messages
                </a>
            </li>
            <!-- Database update links hidden as requested -->
            <?php if(false): // These links are hidden but can be re-enabled by changing false to true ?>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'update_db.php' ? 'active' : ''; ?>" href="update_db.php">
                    <i class="bi bi-database-gear"></i> Update Products DB
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'update_order_items.php' ? 'active' : ''; ?>" href="../update_order_items.php">
                    <i class="bi bi-database-gear"></i> Update Orders DB
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item mt-3">
                <a class="nav-link" href="../index.php" target="_blank">
                    <i class="bi bi-house"></i> View Site
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="logoutBtn">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
