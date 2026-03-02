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
$customer_id = $order['user_id'];

// Get products from the order
$products = [];
$sql = "SELECT p.*, oi.quantity, oi.price as order_price, oi.size
        FROM products p
        JOIN order_items oi ON p.id = oi.product_id
        WHERE oi.order_id = ?";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }
}

// If no products found, try to get from cart_items table
if (empty($products)) {
    $sql = "SELECT p.*, ci.quantity, p.price as order_price, ci.size
            FROM products p
            JOIN cart_items ci ON p.id = ci.product_id
            JOIN carts c ON ci.cart_id = c.id
            WHERE c.user_id = ? AND c.status = 'ordered'";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $customer_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $products[] = $row;
            }
        }
    }
}

// Calculate total
$subtotal = 0;
foreach ($products as $product) {
    $subtotal += $product['order_price'] * $product['quantity'];
}
$shipping = 100; // Fixed shipping cost
$total = $subtotal + $shipping;
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
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.25rem;
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
                    <h1 class="h2">Order #<?php echo $order_id; ?></h1>
                    <div>
                        <?php if(isset($_GET['customer_id'])): ?>
                            <a href="customer_orders.php?id=<?php echo $_GET['customer_id']; ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Customer Orders
                            </a>
                        <?php else: ?>
                            <a href="orders.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Orders
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>

                <!-- Order Header -->
                <div class="order-header">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Placed on:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                            <p class="mb-1"><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</p>
                            <p class="mb-0"><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-1">
                                <strong>Status:</strong> 
                                <span class="badge bg-<?php
                                    echo $order['status'] === 'pending' ? 'warning' :
                                        ($order['status'] === 'processing' ? 'info' :
                                        ($order['status'] === 'shipped' ? 'primary' :
                                        ($order['status'] === 'delivered' ? 'success' : 'danger')));
                                ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </p>
                            <p class="mb-1"><strong>Total Amount:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
                            <a href="customer_profile.php?id=<?php echo $order['user_id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-person"></i> View Customer Profile
                            </a>
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
                                <?php if(!empty($products)): ?>
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
                                                <?php foreach($products as $product): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <?php if(!empty($product['image']) && file_exists("../" . $product['image'])): ?>
                                                                    <img src="../<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image me-2">
                                                                <?php else: ?>
                                                                    <div class="product-image me-2 bg-light d-flex align-items-center justify-content-center">
                                                                        <i class="bi bi-box text-secondary"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div>
                                                                    <h6 class="mb-0"><?php echo htmlspecialchars($product['name']); ?></h6>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>₱<?php echo number_format($product['order_price'], 2); ?></td>
                                                        <td><?php echo $product['quantity']; ?></td>
                                                        <td><?php echo isset($product['size']) && !empty($product['size']) ? htmlspecialchars($product['size']) : 'N/A'; ?></td>
                                                        <td class="text-end">₱<?php echo number_format($product['order_price'] * $product['quantity'], 2); ?></td>
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
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <p class="mb-0">No detailed product information available for this order.</p>
                                        <p class="mb-0">Total Order Amount: ₱<?php echo number_format($order['total_amount'], 2); ?></p>
                                    </div>
                                <?php endif; ?>
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
                                <p class="mb-3"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                <p class="mb-0"><strong>Contact Number:</strong> <?php echo htmlspecialchars($order['contact_number']); ?></p>
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
