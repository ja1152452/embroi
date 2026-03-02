<?php
session_start();
require_once "config/database.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Aling Hera's Embroidery</title>
    <meta name="description" content="Learn about Aling Hera's Embroidery, our story, our craft, our values, and meet our team of skilled artisans.">
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
    <?php include 'navigation.php'; ?>

    <!-- About Us Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-hero-content">
                        <h1 class="about-title">About Aling Hera's Embroidery</h1>
                        <div class="section-divider justify-content-start">
                            <span class="divider-line"></span>
                        </div>
                        <p class="about-subtitle">Crafting Tradition with Modern Elegance</p>
                        <p class="about-description">
                            Welcome to Aling Hera's Embroidery, where traditional Filipino craftsmanship meets contemporary design.
                            We are dedicated to preserving the rich heritage of Filipino embroidery while creating beautiful,
                            wearable art for the modern world.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-hero-image">  
                        <img src="assets/images/logo.png" alt="Aling Hera's Embroidery Workshop" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="about-story">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="section-header text-center">
                        <h2 class="section-title">Our Story</h2>
                        <div class="section-divider">
                            <span class="divider-icon">
                                <img src="assets/images/logo.png" alt="Aling Hera's Embroidery" class="divider-logo">
                            </span>
                        </div>
                    </div>

                    <div class="story-content">
                        <p class="lead text-center mb-5">
                            Aling Hera's Embroidery was established in 2022 by Monica Yasoña, a passionate artisan with over 3 years
                            of experience in traditional Filipino embroidery. What started as a small home-based business has grown
                            into a respected brand known for its quality craftsmanship and attention to detail.
                        </p>

                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4>2022</h4>
                                    <p>Aling Hera's Embroidery was founded as a small home-based business in Lumban, Laguna.</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4>2023</h4>
                                    <p>Expanded our product line to include a wider range of embroidered items for the whole family.</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4>2024</h4>
                                    <p>Celebrated our 2nd anniversary and launched our online store to reach customers nationwide.</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h4>Today</h4>
                                    <p>Continuing to grow while staying true to our mission of preserving Filipino embroidery traditions.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Craft Section -->
    <section class="about-craft">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="craft-image">
                        <img src="assets/images/logo.png" alt="Embroidery Craftsmanship" class="img-fluid rounded">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="craft-content">
                        <h2 class="section-title">Our Craft</h2>
                        <div class="section-divider justify-content-start">
                            <span class="divider-line"></span>
                        </div>
                        <p>
                            Each piece from Aling Hera's Embroidery is meticulously handcrafted by our team of skilled artisans.
                            We take pride in our attention to detail and commitment to quality, ensuring that every stitch is
                            perfect and every design is unique.
                        </p>
                        <p>
                            We use only the finest materials, from premium fabrics to high-quality threads, to create products
                            that are not only beautiful but also durable and long-lasting. Our embroidery techniques have been
                            passed down through generations, preserving the authentic artistry of Filipino craftsmanship.
                        </p>
                        <div class="craft-features">
                            <div class="feature-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Hand-selected premium materials</span>
                            </div>
                            <div class="feature-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Traditional embroidery techniques</span>
                            </div>
                            <div class="feature-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Attention to every detail</span>
                            </div>
                            <div class="feature-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Unique, one-of-a-kind designs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values Section -->
    <section class="about-values">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Our Values</h2>
                <div class="section-divider">
                    <span class="divider-icon">
                        <img src="assets/images/logo.png" alt="Aling Hera's Embroidery" class="divider-logo">
                    </span>
                </div>
                <p class="section-subtitle">The principles that guide everything we do</p>
            </div>

            <div class="row values-grid">
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <h4>Quality</h4>
                        <p>We never compromise on the quality of our products, ensuring that each piece meets our high standards.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h4>Community</h4>
                        <p>We support local artisans and contribute to the preservation of traditional Filipino crafts.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                        <h4>Passion</h4>
                        <p>Our love for embroidery drives us to create beautiful pieces that bring joy to our customers.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="bi bi-globe"></i>
                        </div>
                        <h4>Sustainability</h4>
                        <p>We are committed to environmentally responsible practices in our production process.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Meet Our Team Section -->
    

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
                            <li><a href="index.php#featured-products">All Products</a></li>
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
