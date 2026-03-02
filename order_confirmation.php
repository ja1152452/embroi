<?php
session_start();
require_once "config/database.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch order details
$sql = "SELECT o.*, u.username, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc($stmt->get_result());

// If order not found or doesn't belong to user, redirect to profile
if (!$order) {
    header("location: profile.php");
    exit;
}

// Fetch order items
$sql = "SELECT oi.*, p.name, p.price, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order_items = mysqli_fetch_all($stmt->get_result(), MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Aling Hera's Embroidery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="logged-in">
    <?php include 'navigation.php'; ?>

    <!-- Order Confirmation Content -->
    <div class="container py-5">
        <div class="text-center mb-5">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
            <h1 class="mt-3">Order Confirmed!</h1>
            <p class="text-muted">Thank you for your order. Your order number is #<?php echo $order_id; ?></p>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Order Details -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Order Details</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Order Number:</strong></p>
                                <p class="text-muted">#<?php echo $order_id; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Order Date:</strong></p>
                                <p class="text-muted"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Shipping Address:</strong></p>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Contact Number:</strong></p>
                                <p class="text-muted"><?php echo htmlspecialchars($order['contact_number']); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Payment Method:</strong></p>
                                <p class="text-muted"><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Order Status:</strong></p>
                                <p class="text-muted">
                                    <span class="badge bg-<?php
                                        echo $order['status'] === 'pending' ? 'warning' :
                                            ($order['status'] === 'processing' ? 'info' :
                                            ($order['status'] === 'shipped' ? 'primary' :
                                            ($order['status'] === 'delivered' ? 'success' : 'danger')));
                                    ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Order Items</h5>
                        <?php foreach($order_items as $item): ?>
                            <div class="row align-items-center mb-3">
                                <div class="col-2">
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="img-fluid rounded">
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-0"><?php echo $item['name']; ?></h6>
                                    <small class="text-muted">₱<?php echo number_format($item['price'], 2); ?></small>
                                    <?php if(!empty($item['size'])): ?>
                                        <small class="d-block text-muted">Size: <?php echo $item['size']; ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-3">
                                    <span class="text-muted">Quantity: <?php echo $item['quantity']; ?></span>
                                </div>
                                <div class="col-3 text-end">
                                    <span class="fw-bold">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Order Summary -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>₱<?php echo number_format($order['total_amount'] - 100, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>₱100.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong>
                        </div>
                        <a href="index.php" class="btn btn-primary w-100">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About Us</h5>
                    <p>Aling Hera's Embroidery offers high-quality handcrafted embroidered products for the whole family.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="about.php" class="text-light">About Us</a></li>
                        <li><a href="contact.php" class="text-light">Contact Us</a></li>
                        <li><a href="privacy.php" class="text-light">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li>Email: info@alinghera.com</li>
                        <li>Phone: (123) 456-7890</li>
                        <li>Address: 123 Embroidery St, City</li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/auth-modals.js"></script>
    <script src="assets/js/logout-confirm.js"></script>
    <script src="assets/js/search.js"></script>
</body>
</html>