
<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

// Fetch statistics
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'customer'"))['count'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'"))['total'] ?? 0;

// Check if contact_messages table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'contact_messages'");
$table_exists = mysqli_num_rows($table_check) > 0;

// Get unread messages count
$unread_messages = 0;
if ($table_exists) {
    $unread_messages = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0"))['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Aling Hera's Embroidery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-9 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                        <div class="card bg-primary text-white dashboard-stat-card h-100">
                            <div class="card-body d-flex flex-column justify-content-center text-center">
                                <h5 class="card-title mb-3">Total Products</h5>
                                <h2 class="card-text mb-0"><?php echo $total_products; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                        <div class="card bg-success text-white dashboard-stat-card h-100">
                            <div class="card-body d-flex flex-column justify-content-center text-center">
                                <h5 class="card-title mb-3">Total Orders</h5>
                                <h2 class="card-text mb-0"><?php echo $total_orders; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                        <div class="card bg-info text-white dashboard-stat-card h-100">
                            <div class="card-body d-flex flex-column justify-content-center text-center">
                                <h5 class="card-title mb-3">Total Customers</h5>
                                <h2 class="card-text mb-0"><?php echo $total_customers; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                        <div class="card bg-warning text-white dashboard-stat-card h-100">
                            <div class="card-body d-flex flex-column justify-content-center text-center">
                                <h5 class="card-title mb-3">Total Revenue</h5>
                                <h2 class="card-text mb-0">₱<?php echo number_format($total_revenue, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                        <a href="messages.php" class="text-decoration-none">
                            <div class="card <?php echo $unread_messages > 0 ? 'bg-danger' : 'bg-secondary'; ?> text-white dashboard-stat-card h-100">
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <h5 class="card-title mb-3">Unread Messages</h5>
                                    <h2 class="card-text mb-0"><?php echo $unread_messages; ?></h2>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT o.*, u.username FROM orders o
                                            JOIN users u ON o.user_id = u.id
                                            ORDER BY o.created_at DESC LIMIT 5";
                                    $result = mysqli_query($conn, $sql);
                                    while($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>#" . $row['id'] . "</td>";
                                        echo "<td>" . $row['username'] . "</td>";
                                        echo "<td>₱" . number_format($row['total_amount'], 2) . "</td>";
                                        echo "<td><span class='badge bg-" .
                                            ($row['status'] == 'delivered' ? 'success' :
                                            ($row['status'] == 'cancelled' ? 'danger' : 'warning')) .
                                            "'>" . ucfirst($row['status']) . "</span></td>";
                                        echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout-confirm.js"></script>
</body>
</html>
