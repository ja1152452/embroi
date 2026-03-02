<?php
session_start();
require_once "config/database.php";

// Get category from URL
$category = isset($_GET['cat']) ? strtolower($_GET['cat']) : '';

// Validate category
$valid_categories = ['men', 'women', 'kids'];
if (!in_array($category, $valid_categories)) {
    header("location: index.php");
    exit;
}

// Fetch category details
$sql = "SELECT * FROM categories WHERE LOWER(name) = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $category);
mysqli_stmt_execute($stmt);
$category_result = mysqli_stmt_get_result($stmt);
$category_data = mysqli_fetch_assoc($category_result);

// Fetch products in this category
$sql = "SELECT * FROM products WHERE category_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $category_data['id']);
mysqli_stmt_execute($stmt);
$products = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($category); ?> - Aling Hera's Embroidery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'navigation.php'; ?>

    <!-- Category Header -->
    <div class="bg-light py-5">
        <div class="container">
            <h1 class="display-4"><?php echo ucfirst($category); ?> Collection</h1>
            <p class="lead"><?php echo $category_data['description']; ?></p>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="container py-5">
        <div class="row">
            <?php while($product = mysqli_fetch_assoc($products)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <p class="card-text"><?php echo substr($product['description'], 0, 100) . '...'; ?></p>
                            <p class="card-text"><strong>₱<?php echo number_format($product['price'], 2); ?></strong></p>
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                            <?php if($product['stock'] > 0): ?>
                                <button class="btn btn-success add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
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