<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

// Handle customer deletion
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Check if customer has orders
    $check_sql = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $order_count = mysqli_fetch_assoc($check_result)['order_count'];

    if($order_count > 0) {
        // Customer has orders, set error message
        $_SESSION['error_message'] = "Cannot delete customer with existing orders. Please delete their orders first.";
    } else {
        // Delete the customer
        $sql = "DELETE FROM users WHERE id = ? AND role = 'customer'";
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Customer deleted successfully.";
            } else {
                $_SESSION['error_message'] = "Error deleting customer. Please try again.";
            }
        }
    }

    header("location: customers.php");
    exit;
}

// Fetch all customers
$sql = "SELECT u.*,
        COUNT(o.id) as total_orders,
        SUM(CASE WHEN o.status != 'cancelled' THEN o.total_amount ELSE 0 END) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id
        WHERE u.role = 'customer'
        GROUP BY u.id
        ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $sql);
$customers = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-9 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Customers</h1>
                    <a href="add_customer.php" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Add New Customer
                    </a>
                </div>

                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <!-- Customers Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Joined Date</th>
                                <th>Total Orders</th>
                                <th>Total Spent</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($customers as $customer): ?>
                                <tr>
                                    <td><?php echo $customer['id']; ?></td>
                                    <td><?php echo htmlspecialchars($customer['username']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($customer['created_at'])); ?></td>
                                    <td><?php echo $customer['total_orders']; ?></td>
                                    <td>₱<?php echo number_format($customer['total_spent'] ?? 0, 2); ?></td>
                                    <td>
                                        <a href="customer_profile.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-person"></i> View Profile
                                        </a>
                                        <a href="customer_orders.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-bag"></i> Orders
                                        </a>
                                        <a href="customers.php?delete=<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this customer? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>

                                <!-- View Customer Modal -->
                                <div class="modal fade" id="viewCustomerModal<?php echo $customer['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Customer Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><strong>Username:</strong></p>
                                                        <p class="text-muted"><?php echo htmlspecialchars($customer['username']); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><strong>Email:</strong></p>
                                                        <p class="text-muted"><?php echo htmlspecialchars($customer['email']); ?></p>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><strong>Joined Date:</strong></p>
                                                        <p class="text-muted"><?php echo date('F j, Y', strtotime($customer['created_at'])); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><strong>Total Orders:</strong></p>
                                                        <p class="text-muted"><?php echo $customer['total_orders']; ?></p>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><strong>Total Spent:</strong></p>
                                                        <p class="text-muted">₱<?php echo number_format($customer['total_spent'] ?? 0, 2); ?></p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <h6>Order History</h6>
                                                <?php
                                                // Fetch customer's orders
                                                $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
                                                $stmt = mysqli_prepare($conn, $sql);
                                                mysqli_stmt_bind_param($stmt, "i", $customer['id']);
                                                mysqli_stmt_execute($stmt);
                                                $orders = mysqli_fetch_all($stmt->get_result(), MYSQLI_ASSOC);
                                                ?>
                                                <?php if(empty($orders)): ?>
                                                    <p class="text-muted">No orders found.</p>
                                                <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Order ID</th>
                                                                    <th>Date</th>
                                                                    <th>Total</th>
                                                                    <th>Status</th>
                                                                    <th>Payment Method</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach($orders as $order): ?>
                                                                    <tr>
                                                                        <td>#<?php echo $order['id']; ?></td>
                                                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                                                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                                                        <td>
                                                                            <span class="badge bg-<?php
                                                                                echo $order['status'] === 'pending' ? 'warning' :
                                                                                    ($order['status'] === 'processing' ? 'info' :
                                                                                    ($order['status'] === 'shipped' ? 'primary' :
                                                                                    ($order['status'] === 'delivered' ? 'success' : 'danger')));
                                                                            ?>">
                                                                                <?php echo ucfirst($order['status']); ?>
                                                                            </span>
                                                                        </td>
                                                                        <td><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout-confirm.js"></script>
</body>
</html>