<?php
session_start();
require_once "../config/database.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

// Check if order ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])){
    $_SESSION['error_message'] = "Order ID is required.";
    header("location: orders.php");
    exit;
}

$order_id = $_GET['id'];

// Get order details
$sql = "SELECT o.*, u.username, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) == 0){
    $_SESSION['error_message'] = "Order not found.";
    header("location: orders.php");
    exit;
}

$order = mysqli_fetch_assoc($result);

// Get order items
$order_items = [];

// First check if order_items table exists
$check_table = "SHOW TABLES LIKE 'order_items'";
$table_exists = mysqli_query($conn, $check_table);

if (mysqli_num_rows($table_exists) > 0) {
    // Table exists, try to get order items
    $sql = "SELECT oi.*, p.name as product_name, p.image as product_image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        $order_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

// If no order items found, create a dummy item for display purposes
if (empty($order_items)) {
    // Get the order total from the orders table
    $total_amount = $order['total_amount'];

    // Create a dummy order item
    $order_items[] = [
        'product_id' => 0,
        'product_name' => 'Order Item',
        'product_image' => 'assets/images/products/default.jpg',
        'price' => $total_amount,
        'quantity' => 1,
        'size' => 'N/A'
    ];
}

// Calculate order summary
$subtotal = 0;
foreach($order_items as $item){
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 100; // Fixed shipping cost
$total = $subtotal + $shipping;

// Get customer details
$customer_id = $order['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .order-header {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        .order-status {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.25rem;
        }
        .address-card {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Order Details</h1>
                    <div>
                        <?php if(isset($_GET['customer_id'])): ?>
                            <a href="customer_orders.php?id=<?php echo $_GET['customer_id']; ?>" class="btn btn-secondary me-2">
                                <i class="bi bi-arrow-left"></i> Back to Customer Orders
                            </a>
                        <?php else: ?>
                            <a href="orders.php" class="btn btn-secondary me-2">
                                <i class="bi bi-arrow-left"></i> Back to Orders
                            </a>
                        <?php endif; ?>
                        <a href="customer_profile.php?id=<?php echo $order['user_id']; ?>" class="btn btn-primary">
                            <i class="bi bi-person"></i> View Customer
                        </a>
                    </div>
                </div>

                <!-- Order Header -->
                <div class="order-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Order #<?php echo $order_id; ?></h4>
                            <p class="text-muted mb-0">Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="badge bg-<?php
                                echo $order['status'] === 'pending' ? 'warning' :
                                    ($order['status'] === 'processing' ? 'info' :
                                    ($order['status'] === 'shipped' ? 'primary' :
                                    ($order['status'] === 'delivered' ? 'success' : 'danger')));
                            ?> order-status">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                            <p class="mt-2 mb-0"><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Order Items -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Order Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Size</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($order_items as $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if($item['product_id'] > 0 && file_exists("../" . $item['product_image'])): ?>
                                                                <img src="../<?php echo $item['product_image']; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-image me-3">
                                                            <?php else: ?>
                                                                <div class="product-image me-3 bg-light d-flex align-items-center justify-content-center">
                                                                    <i class="bi bi-box text-secondary" style="font-size: 1.5rem;"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div>
                                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                                <?php if($item['product_id'] > 0): ?>
                                                                    <small class="text-muted">Product ID: <?php echo $item['product_id']; ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td><?php echo isset($item['size']) && $item['size'] ? htmlspecialchars($item['size']) : 'N/A'; ?></td>
                                                    <td class="text-end">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                                <td class="text-end">₱<?php echo number_format($subtotal, 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Shipping:</strong></td>
                                                <td class="text-end">₱<?php echo number_format($shipping, 2); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                                <td class="text-end"><strong>₱<?php echo number_format($total, 2); ?></strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Update Order Status -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Update Order Status</h5>
                            </div>
                            <div class="card-body">
                                <form action="update_order_status.php" method="post" class="d-flex">
                                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                    <select name="status" class="form-select me-2">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Customer and Shipping Info -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                                <p class="mb-3"><strong>Customer ID:</strong> <?php echo $order['user_id']; ?></p>
                                <a href="customer_profile.php?id=<?php echo $order['user_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-person"></i> View Customer Profile
                                </a>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Shipping Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="address-card">
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                </div>
                                <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($order['contact_number']); ?></p>
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
