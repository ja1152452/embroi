<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

// Check if customer ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    $_SESSION['error_message'] = "Customer ID is required.";
    header("location: customers.php");
    exit;
}

$customer_id = $_GET['id'];

// Get customer details
$sql = "SELECT * FROM users WHERE id = ? AND role = 'customer'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0){
    $_SESSION['error_message'] = "Customer not found.";
    header("location: customers.php");
    exit;
}

$customer = mysqli_fetch_assoc($result);

// Get customer's orders
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$orders = mysqli_fetch_all($stmt->get_result(), MYSQLI_ASSOC);

// Calculate order statistics
$total_orders = count($orders);
$total_spent = 0;
$order_statuses = [
    'pending' => 0,
    'processing' => 0,
    'shipped' => 0,
    'delivered' => 0,
    'cancelled' => 0
];

foreach($orders as $order){
    $total_spent += $order['total_amount'];

    if(isset($order_statuses[$order['status']])){
        $order_statuses[$order['status']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .customer-info {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background-color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-9 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Customer Orders</h1>
                    <div>
                        <a href="customer_profile.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary me-2">
                            <i class="bi bi-person"></i> View Profile
                        </a>
                        <a href="customers.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Customers
                        </a>
                    </div>
                </div>

                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>

                <!-- Customer Info -->
                <div class="customer-info">
                    <div class="row">
                        <div class="col-md-6">
                            <h4><?php echo htmlspecialchars($customer['username']); ?></h4>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($customer['email']); ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-0"><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($customer['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Order Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $total_orders; ?></div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value">₱<?php echo number_format($total_spent, 2); ?></div>
                            <div class="stat-label">Total Spent</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $order_statuses['delivered']; ?></div>
                            <div class="stat-label">Completed Orders</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $order_statuses['pending'] + $order_statuses['processing']; ?></div>
                            <div class="stat-label">Pending/Processing</div>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order History</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($orders)): ?>
                            <p class="text-muted">No orders found for this customer.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Payment Method</th>
                                            <th>Shipping Address</th>
                                            <th>Actions</th>
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
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addressModal<?php echo $order['id']; ?>">
                                                        View Address
                                                    </button>
                                                </td>
                                                <td>
                                                    <a href="view_order.php?id=<?php echo $order['id']; ?>&customer_id=<?php echo $customer_id; ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteOrderModal<?php echo $order['id']; ?>">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Address Modal -->
                                            <div class="modal fade" id="addressModal<?php echo $order['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Shipping Address</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                                            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($order['contact_number']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Delete Order Modal -->
                                            <div class="modal fade" id="deleteOrderModal<?php echo $order['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Delete Order</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete Order #<?php echo $order['id']; ?>?</p>
                                                            <p class="text-danger"><strong>Warning:</strong> This action cannot be undone. All order data will be permanently deleted.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <form action="delete_order.php" method="post">
                                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                                <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                                                                <button type="submit" class="btn btn-danger">Delete Order</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout-confirm.js"></script>
</body>
</html>
