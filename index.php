<?php
session_start();
require_once "config/database.php";

// Check for login success message
$login_success = false;
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
    $login_success = true;
    // Remove the flag so the message doesn't show again on refresh
    unset($_SESSION['login_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aling Hera's Embroidery</title>
    <meta name="description" content="Discover our exquisite collection of handcrafted embroidered products, meticulously created with passion and precision for men, women, and kids.">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">
    <?php if ($login_success): ?>
    <div class="alert alert-success alert-dismissible fade show notification-top" role="alert">
        <strong>Success!</strong> You have been logged in successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <?php include 'navigation.php'; ?>

    <!-- Hero Section -->
    <div class="hero-banner">
        <div class="hero-content">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 hero-text-container">
                        <h1 class="hero-title">Handcrafted Elegance</h1>
                        <h2 class="hero-subtitle">Premium Embroidery For Every Occasion</h2>
                        <p class="hero-description">Discover our exquisite collection of handcrafted embroidered products, meticulously created with passion and precision.</p>
                        <div class="hero-buttons">
                            <a href="#featured-products" class="btn btn-primary btn-lg shop-now-btn">Shop Collection</a>
                            <a href="about.php" class="btn btn-outline-primary btn-lg">Our Story</a>
                        </div>
                        <div class="hero-features">
                            <div class="feature-item">
                                <i class="bi bi-truck"></i>
                                <span>Free Shipping</span>
                            </div>
                            <div class="feature-item">
                                <i class="bi bi-shield-check"></i>
                                <span>Quality Guarantee</span>
                            </div>
                            <div class="feature-item">
                                <i class="bi bi-arrow-repeat"></i>
                                <span>Easy Returns</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 hero-image-container">
                        <img src="assets/images/logo.png" alt="Embroidery Collection" class="hero-image">
                        <div class="hero-badge">
                            <div class="badge-content">
                                <span class="badge-text">Handmade</span>
                                <span class="badge-subtext">with love</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Showcase -->
    <div class="categories-showcase">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <a href="category.php?cat=men" class="category-card">
                        <div class="category-overlay"></div>
                        <h3>Men's Collection</h3>
                        <span class="category-link">Shop Now <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="category.php?cat=women" class="category-card">
                        <div class="category-overlay"></div>
                        <h3>Women's Collection</h3>
                        <span class="category-link">Shop Now <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="category.php?cat=kids" class="category-card">
                        <div class="category-overlay"></div>
                        <h3>Kids' Collection</h3>
                        <span class="category-link">Shop Now <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Products -->
    <section id="featured-products" class="featured-products-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Collection</h2>
                <p class="section-subtitle">Discover our most popular handcrafted pieces</p>
                <div class="section-divider">
                    <span class="divider-icon">
                        <img src="assets/images/logo.png" alt="Aling Hera's Embroidery" class="divider-logo">
                    </span>
                </div>
            </div>

            <div class="product-filter">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="men">Men</button>
                <button class="filter-btn" data-filter="women">Women</button>
                <button class="filter-btn" data-filter="kids">Kids</button>
            </div>

            <div class="row g-4 product-grid">
                <?php
                // Fetch featured products from database
                $sql = "SELECT p.*, c.name as category_name FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        ORDER BY p.created_at DESC LIMIT 6";
                $result = mysqli_query($conn, $sql);

                while($row = mysqli_fetch_assoc($result)) {
                    // Use a placeholder image if the image path doesn't exist
                    $imagePath = file_exists($row['image']) ? $row['image'] : 'assets/images/placeholder.jpg';
                    $categoryClass = strtolower($row['category_name'] ?? 'all');

                    echo '<div class="col-md-4 col-lg-4 mb-4 product-item ' . $categoryClass . '">';
                    echo '<div class="product-card">';
                    echo '<div class="product-badge">' . ($row['stock'] <= 5 && $row['stock'] > 0 ? 'Limited Stock' : ($row['stock'] <= 0 ? 'Sold Out' : 'New Arrival')) . '</div>';
                    echo '<div class="product-image">';
                    echo '<img src="' . $imagePath . '" alt="' . $row['name'] . '">';
                    echo '<div class="product-actions">';
                    echo '<a href="product.php?id=' . $row['id'] . '" class="action-btn view-btn" title="View Details"><i class="bi bi-eye"></i></a>';

                    if($row['stock'] > 0) {
                        echo '<button class="action-btn add-to-cart" data-product-id="' . $row['id'] . '" title="Add to Cart"><i class="bi bi-cart-plus"></i></button>';
                    } else {
                        echo '<button class="action-btn disabled" disabled title="Out of Stock"><i class="bi bi-x-circle"></i></button>';
                    }


                    echo '</div></div>';

                    echo '<div class="product-info">';
                    echo '<h3 class="product-title"><a href="product.php?id=' . $row['id'] . '">' . $row['name'] . '</a></h3>';
                    echo '<div class="product-category">' . ($row['category_name'] ?? 'Uncategorized') . '</div>';
                    echo '<div class="product-rating">';

                    // Generate random rating between 4 and 5 for demo purposes
                    $rating = mt_rand(40, 50) / 10;
                    $fullStars = floor($rating);
                    $halfStar = $rating - $fullStars >= 0.5;

                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $fullStars) {
                            echo '<i class="bi bi-star-fill"></i>';
                        } elseif ($i == $fullStars + 1 && $halfStar) {
                            echo '<i class="bi bi-star-half"></i>';
                        } else {
                            echo '<i class="bi bi-star"></i>';
                        }
                    }

                    echo '<span class="rating-count">(' . mt_rand(10, 50) . ')</span>';
                    echo '</div>';

                    echo '<div class="product-price">';
                    echo '<span class="current-price">₱' . number_format($row['price'], 2) . '</span>';

                    // Show a fake original price for some products to simulate a discount
                    if (mt_rand(0, 1)) {
                        $originalPrice = $row['price'] * (mt_rand(110, 130) / 100);
                        echo '<span class="original-price">₱' . number_format($originalPrice, 2) . '</span>';
                    }

                    echo '</div>';

                    echo '</div></div></div>';
                }
                ?>
            </div>


        </div>
    </section>

    <!-- About Us Section -->
    <section class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 about-image-col">
                    <div class="about-image-container">
                        <img src="assets/images/logo.png" alt="Aling Hera's Embroidery" class="about-image">
                        <div class="experience-badge">
                            <span class="years">3</span>
                            <span class="text">Years of<br>Excellence</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 about-content-col">
                    <div class="section-header text-start">
                        <span class="section-subtitle">Our Story</span>
                        <h2 class="section-title">Crafting Tradition With Modern Elegance</h2>
                        <div class="section-divider justify-content-start">
                            <span class="divider-line"></span>
                        </div>
                    </div>

                    <div class="about-content">
                        <p class="lead">Aling Hera's Embroidery was established in 2022 by Monica Yasoña, a passionate artisan with over 3 years of experience in traditional Filipino embroidery.</p>

                        <p>Our mission is to preserve the rich tradition of Filipino embroidery while creating beautiful, wearable art for modern consumers. We combine traditional techniques with contemporary designs to create unique products for the whole family.</p>

                        <div class="about-features">
                            <div class="feature">
                                <div class="feature-icon">
                                    <i class="bi bi-award"></i>
                                </div>
                                <div class="feature-text">
                                    <h4>Premium Quality</h4>
                                    <p>Every stitch is crafted with precision and care</p>
                                </div>
                            </div>

                            <div class="feature">
                                <div class="feature-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="feature-text">
                                    <h4>Artisan Crafted</h4>
                                    <p>Supporting local artisans and traditional techniques</p>
                                </div>
                            </div>
                        </div>

                        <a href="about.php" class="btn btn-primary btn-lg about-btn">Discover Our Journey <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">        <div class="container">
            <div class="section-header">
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="section-subtitle">Trusted by customers across the Philippines</p>
                <div class="section-divider">
                    <span class="divider-icon">
                        <img src="assets/images/logo.png" alt="Aling Hera's Embroidery" class="divider-logo">
                    </span>
                </div>
            </div>

            <div class="row testimonials-slider">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <div class="testimonial-text">
                            <p>"The quality of embroidery is exceptional. I ordered a custom barong for my wedding and it exceeded all my expectations. The attention to detail is remarkable."</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-image">
                                <img src="assets/images/team/Yasoña.jpeg" alt="Customer">
                            </div>
                            <div class="author-info">
                                <h4>Jayzel Yasoña</h4>
                                <p>Lumban</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <div class="testimonial-text">
                            <p>"I've been a loyal customer for years. Their embroidered dresses always get me compliments. The craftsmanship is unmatched and the designs are timeless yet modern."</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-image">
                                <img src="assets/images/team/Pacia.jpg" alt="Customer">
                            </div>
                            <div class="author-info">
                                <h4>Jasmin Clara Pacia</h4>
                                <p>Los Baños</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-half"></i>
                        </div>
                        <div class="testimonial-text">
                            <p>"The embroidered items I purchased for my children are not only beautiful but also durable. After multiple washes, they still look as good as new. Truly worth every peso!"</p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-image">
                                <img src="assets/images/team/Orphiano.jpg" alt="Customer">
                            </div>
                            <div class="author-info">
                                <h4>Jhim Rainer Orphiano</h4>
                                <p>Magdalena</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="newsletter-content">
                            <h3>Subscribe to Our Newsletter</h3>
                            <p>Stay updated with our latest collections, exclusive offers, and embroidery tips.</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <form class="newsletter-form">
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Your Email Address" required>
                                <button class="btn btn-primary" type="submit">Subscribe <i class="bi bi-arrow-right"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-main">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6 footer-info">
                        <div class="footer-logo">
                            <a href="index.php">
                                <img src="assets/images/logo.png" alt="Aling Hera's Embroidery Logo" class="footer-brand-logo">
                                Aling Hera's Embroidery
                            </a>
                        </div>
                        <p>Aling Hera's Embroidery offers high-quality handcrafted embroidered products for the whole family, combining traditional Filipino craftsmanship with modern designs.</p>
                        <div class="social-links">
                            <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="pinterest"><i class="bi bi-pinterest"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6 footer-links">
                        <h4>Shop</h4>
                        <ul>
                            <li><a href="category.php?cat=men">Men's Collection</a></li>
                            <li><a href="category.php?cat=women">Women's Collection</a></li>
                            <li><a href="category.php?cat=kids">Kids' Collection</a></li>
                            <li><a href="shop.php">All Products</a></li>
                            <li><a href="#">New Arrivals</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-md-6 footer-links">
                        <h4>Information</h4>
                        <ul>
                            <li><a href="about.php">About Us</a></li>
                            <li><a href="contact.php">Contact Us</a></li>
                            <li><a href="#">Shipping & Returns</a></li>
                            <li><a href="privacy.php">Privacy Policy</a></li>
                            <li><a href="#">Terms & Conditions</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-4 col-md-6 footer-contact">
                        <h4>Contact Us</h4>
                        <p>
                            <i class="bi bi-geo-alt"></i> Wawa, Lumban, Laguna<br>
                            <i class="bi bi-phone"></i> +63 977 007 7808<br>
                            <i class="bi bi-envelope"></i> jayzelyasona23@gmail.com<br>
                        </p>

                        <div class="payment-methods">
                            <h5>We Accept</h5>
                            <div class="payment-icons">
                                <i class="bi bi-credit-card"></i>
                                <i class="bi bi-paypal"></i>
                                <i class="bi bi-cash-coin"></i>
                                <i class="bi bi-bank"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="copyright">
                    &copy; <?php echo date('Y'); ?> <strong>Aling Hera's Embroidery</strong>. All Rights Reserved.
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
