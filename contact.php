<?php
session_start();
require_once "config/database.php";

$message = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING));
    $message_text = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        // Check if contact_messages table exists
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'contact_messages'");
        $table_exists = mysqli_num_rows($table_check) > 0;

        // If table doesn't exist, create it
        if (!$table_exists) {
            $create_table = "CREATE TABLE contact_messages (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            mysqli_query($conn, $create_table);
        }

        // Insert message into database
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message_text);

            if (mysqli_stmt_execute($stmt)) {
                $success = true;
                $message = "Thank you for your message! We will get back to you soon.";
            } else {
                $message = "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "Something went wrong. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Aling Hera's Embroidery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/contact.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">
    <?php include 'navigation.php'; ?>

    <!-- Contact Hero Section -->
    <section class="contact-hero py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <h1 class="contact-title mb-3">Get In Touch</h1>
                    <div class="section-divider mb-4">
                        <div class="divider-line"></div>
                        <div class="divider-icon">
                            <img src="assets/images/logo.png" alt="Divider Icon" class="divider-logo">
                        </div>
                        <div class="divider-line"></div>
                    </div>
                    <p class="contact-subtitle mb-5">We'd love to hear from you! Whether you have a question about our products, custom orders, or anything else, our team is ready to answer all your inquiries.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Info Cards -->
    <section class="contact-info py-5">
        <div class="container">
            <?php if(!empty($message)): ?>
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-8">
                        <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> mb-4 fade-in">
                            <?php echo $message; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="row contact-cards">
                        <div class="col-md-4 mb-4">
                            <div class="contact-card">
                                <div class="contact-card-icon">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <h3>Address</h3>
                                <p>purok 5 Wawa<br>Lumban,Laguna<br>Philippines</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="contact-card">
                                <div class="contact-card-icon">
                                    <i class="bi bi-telephone-fill"></i>
                                </div>
                                <h3>Phone</h3>
                                <p>09770077808<br>09770077808</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="contact-card">
                                <div class="contact-card-icon">
                                    <i class="bi bi-envelope-fill"></i>
                                </div>
                                <h3>Email</h3>
                                <p>jayzelyasona23@gmail.com<br>jasminclara0805@gmail.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form & Map Section -->
    <section class="contact-form-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="contact-wrapper">
                        <div class="row g-0">
                            <div class="col-lg-6">
                                <div class="contact-form-container">
                                    <h2 class="form-title">Send Us a Message</h2>
                                    <p class="form-subtitle mb-4">Fill out the form below and we'll get back to you as soon as possible.</p>

                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="contact-form">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required>
                                                    <label for="name"><i class="bi bi-person me-2"></i>Your Name</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
                                                    <label for="email"><i class="bi bi-envelope me-2"></i>Email Address</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" required>
                                                <label for="subject"><i class="bi bi-chat-left-text me-2"></i>Subject</label>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <div class="form-floating">
                                                <textarea class="form-control" id="message" name="message" placeholder="Your Message" style="height: 150px" required></textarea>
                                                <label for="message"><i class="bi bi-pencil me-2"></i>Your Message</label>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary btn-lg contact-submit-btn">
                                                <i class="bi bi-send me-2"></i>Send Message
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="map-container">
                                    <div class="map-overlay">
                                        <h3>Find Us</h3>
                                        <p>Visit our store in Wawa, Lumban, Laguna</p>
                                    </div>
                                    <div class="map-frame">
                                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7728.0953339668!2d121.44922990000001!3d14.308270399999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397fb1b5dd1efb3%3A0xcb7bb6e61bf827ed!2sWawa%2C%20Lumban%2C%20Laguna!5e0!3m2!1sen!2sph!4v1625214321774!5m2!1sen!2sph" allowfullscreen="" loading="lazy"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA Section -->
    <section class="contact-cta py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="cta-container text-center">
                        <h2>Connect With Us On Social Media</h2>
                        <p>Follow us to stay updated with our latest products and promotions</p>
                        <div class="social-icons mt-4">
                            <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-pinterest"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                        <li>Email: jayzelyasona23@gmail.com</li>
                        <li>Phone: 09770077808</li>
                        <li>Address: Wawa Lumban Laguna</li>
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
    <script src="assets/js/contact.js"></script>
</body>
</html>
