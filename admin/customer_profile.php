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

// Get customer's addresses
$addresses = [];
$check_table = "SHOW TABLES LIKE 'addresses'";
$table_exists = mysqli_query($conn, $check_table);
if (mysqli_num_rows($table_exists) > 0) {
    $sql = "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $addresses[] = $row;
    }
}

// Calculate customer statistics
$total_orders = count($orders);
$total_spent = 0;
$completed_orders = 0;
$pending_orders = 0;

foreach($orders as $order){
    $total_spent += $order['total_amount'];

    if($order['status'] == 'delivered'){
        $completed_orders++;
    } elseif($order['status'] == 'pending' || $order['status'] == 'processing'){
        $pending_orders++;
    }
}

// Get latest order
$latest_order = !empty($orders) ? $orders[0] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .profile-header {
            background-color: #f8f9fa;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1rem;
        }
        .stat-card {
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            background-color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .stat-card .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
        }
        .address-card {
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .address-card.default {
            border-left: 4px solid #0d6efd;
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
                    <h1 class="h2">Customer Profile</h1>
                    <div>
                        <a href="customers.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Customers
                        </a>
                    </div>
                </div>

                <!-- Profile Header -->
                <div class="profile-header text-center">
                    <div class="profile-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <h2><?php echo htmlspecialchars($customer['username']); ?></h2>
                    <p class="text-muted"><?php echo htmlspecialchars($customer['email']); ?></p>
                    <p>Member since <?php echo date('F j, Y', strtotime($customer['created_at'])); ?></p>
                </div>

                <!-- Customer Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-primary">
                                <i class="bi bi-bag"></i>
                            </div>
                            <div class="stat-value"><?php echo $total_orders; ?></div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-success">
                                <i class="bi bi-cash"></i>
                            </div>
                            <div class="stat-value">₱<?php echo number_format($total_spent, 2); ?></div>
                            <div class="stat-label">Total Spent</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-info">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stat-value"><?php echo $completed_orders; ?></div>
                            <div class="stat-label">Completed Orders</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-warning">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div class="stat-value"><?php echo $pending_orders; ?></div>
                            <div class="stat-label">Pending Orders</div>
                        </div>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="row">
                    <!-- Addresses -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Saved Addresses</h5>
                            </div>
                            <div class="card-body">
                                <?php if(empty($addresses)): ?>
                                    <p class="text-muted">No saved addresses found.</p>
                                <?php else: ?>
                                    <?php foreach($addresses as $address): ?>
                                        <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">
                                                    <?php echo htmlspecialchars($address['address_name']); ?>
                                                    <?php if($address['is_default']): ?>
                                                        <span class="badge bg-primary ms-2">Default</span>
                                                    <?php endif; ?>
                                                </h6>
                                            </div>
                                            <p class="mb-1"><strong><?php echo htmlspecialchars($address['full_name']); ?></strong></p>
                                            <p class="mb-1"><?php echo htmlspecialchars($address['address_line1']); ?></p>
                                            <?php if(!empty($address['address_line2'])): ?>
                                                <p class="mb-1"><?php echo htmlspecialchars($address['address_line2']); ?></p>
                                            <?php endif; ?>
                                            <p class="mb-1"><?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['postal_code']); ?></p>
                                            <p class="mb-0"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($address['phone']); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Recent Orders</h5>
                                <a href="customer_orders.php?id=<?php echo $customer_id; ?>" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if(empty($orders)): ?>
                                    <p class="text-muted">No orders found.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Date</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Display only the 5 most recent orders
                                                $recent_orders = array_slice($orders, 0, 5);
                                                foreach($recent_orders as $order):
                                                ?>
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
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/logout-confirm.js"></script>
</body>
</html>
