<?php
session_start();
require_once "config/database.php";

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details
$sql = "SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

// If product not found, redirect to homepage
if (!$product) {
    header("location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Aling Hera's Embroidery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">
    <?php include 'navigation.php'; ?>

    <!-- Product Details -->
    <div class="container py-5">
        <div class="row">
            <!-- Product Image -->
            <div class="col-md-6 mb-4">
                <div class="product-detail-image-container">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="product-detail-image">
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="category.php?cat=<?php echo strtolower($product['category_name']); ?>"><?php echo $product['category_name']; ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
                    </ol>
                </nav>

                <h1 class="product-title"><?php echo $product['name']; ?></h1>
                <p class="product-price">₱<?php echo number_format($product['price'], 2); ?></p>

                <div class="mb-4">
                    <p class="product-description"><?php echo nl2br($product['description']); ?></p>
                </div>

                <div class="mb-4">
                    <p class="mb-2"><strong>Category:</strong> <?php echo $product['category_name']; ?></p>
                    <p class="mb-2"><strong>Availability:</strong>
                        <?php if($product['stock'] > 0): ?>
                            <span class="text-success">In Stock (<?php echo $product['stock']; ?> available)</span>
                        <?php else: ?>
                            <span class="text-danger">Out of Stock</span>
                        <?php endif; ?>
                    </p>
                </div>

                <?php if($product['stock'] > 0): ?>
                    <?php
                    // Check if product has sizes
                    $has_sizes = !empty($product['sizes']);
                    $available_sizes = $has_sizes ? explode(',', $product['sizes']) : [];
                    ?>

                    <?php if($has_sizes): ?>
                    <div class="mb-3">
                        <label class="form-label">Size:</label>
                        <div class="size-options d-flex flex-wrap gap-2">
                            <?php foreach($available_sizes as $size): ?>
                                <div class="form-check">
                                    <input class="form-check-input size-option" type="radio" name="size" id="size-<?php echo $size; ?>" value="<?php echo $size; ?>" <?php echo ($size === $available_sizes[0]) ? 'checked' : ''; ?>>
                                    <label class="form-check-label size-label" for="size-<?php echo $size; ?>">
                                        <?php echo $size; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <div class="input-group quantity-control" style="width: 150px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="decrementQuantity()">-</button>
                            <input type="number" class="form-control text-center quantity-input" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="incrementQuantity()">+</button>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-4">
                        <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </button>
                        <button class="btn btn-success buy-now" data-product-id="<?php echo $product['id']; ?>">
                            <i class="bi bi-lightning-fill"></i> Buy Now
                        </button>
                    </div>
                <?php endif; ?>
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
    <script>
        function incrementQuantity() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.getAttribute('max'));
            const currentValue = parseInt(input.value) || 1;
            if (currentValue < max) {
                input.value = currentValue + 1;
                // Trigger change event to update any listeners
                input.dispatchEvent(new Event('change'));
            }
        }

        function decrementQuantity() {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value) || 1;
            if (currentValue > 1) {
                input.value = currentValue - 1;
                // Trigger change event to update any listeners
                input.dispatchEvent(new Event('change'));
            }
        }

        // Ensure the quantity input always has a valid value
        document.getElementById('quantity').addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            let value = parseInt(this.value) || 1;

            // Ensure value is at least 1
            if (value < 1) value = 1;

            // Ensure value doesn't exceed max stock
            if (value > max) value = max;

            this.value = value;
        });

        // Override addToCart function for this page
        document.querySelector('.add-to-cart').addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantity = parseInt(document.getElementById('quantity').value);

            // Add to cart array
            const existingItem = cart.find(item => item.id === productId);
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    id: productId,
                    quantity: quantity
                });
            }

            // Save to localStorage
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();

            // Show success message
            showNotification('Product added to cart!', 'success');
        });

        // Buy Now button functionality
        document.querySelector('.buy-now').addEventListener('click', function() {
            // Check if user is logged in
            if (!isUserLoggedIn()) {
                // Show login modal instead of redirecting
                if (typeof showModal === 'function') {
                    showModal('loginModal');
                    showNotification('Please log in to complete your purchase', 'info');
                } else {
                    // Fallback if modal function is not available
                    showNotification('Please log in to complete your purchase', 'warning');
                }
                return;
            }

            const productId = this.dataset.productId;
            const quantity = parseInt(document.getElementById('quantity').value);

            // Check if size options are available
            const sizeOptions = document.querySelectorAll('.size-option');
            let selectedSize = null;

            if (sizeOptions.length > 0) {
                // Get selected size
                const selectedSizeOption = document.querySelector('.size-option:checked');
                if (selectedSizeOption) {
                    selectedSize = selectedSizeOption.value;
                } else {
                    showNotification('Please select a size', 'warning');
                    return;
                }
            }

            // Clear existing cart
            cart = [];

            // Add only this product to cart
            if (selectedSize) {
                cart.push({
                    id: productId,
                    size: selectedSize,
                    quantity: quantity
                });
            } else {
                cart.push({
                    id: productId,
                    quantity: quantity
                });
            }

            // Save to localStorage
            localStorage.setItem('cart', JSON.stringify(cart));

            // Redirect to checkout page
            window.location.href = 'checkout.php';
        });

        // Helper function to check if user is logged in
        function isUserLoggedIn() {
            return <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} notification`;
            notification.textContent = message;
            document.body.appendChild(notification);

            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>