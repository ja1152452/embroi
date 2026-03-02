
<?php
session_start();
require_once "config/database.php";

$error = '';
$is_ajax = isset($_POST['ajax']) && $_POST['ajax'] === 'true';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password']; // Don't trim passwords as spaces might be part of the password

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Prevent SQL injection using prepared statements
        $sql = "SELECT id, username, email, password, role FROM users WHERE email = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $email, $hashed_password, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Session already started at the top of the file

                            // Set session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;

                            // Regenerate session ID for security
                            session_regenerate_id();

                            // Set login success message
                            $_SESSION["login_success"] = true;

                            // Handle AJAX request differently
                            if ($is_ajax) {
                                $response = [
                                    'success' => true,
                                    'message' => 'Login successful!',
                                    'redirect' => $role == 'admin' ? 'admin/dashboard.php' : 'index.php'
                                ];
                                echo json_encode($response);
                                exit;
                            } else {
                                // Redirect based on role for regular form submission
                                if ($role == 'admin') {
                                    header("location: admin/dashboard.php");
                                    exit; // Important to prevent further execution
                                } else {
                                    header("location: index.php");
                                    exit; // Important to prevent further execution
                                }
                            }
                        } else {
                            $error = "Invalid email or password.";
                        }
                    }
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = "Database error. Please try again later.";
        }
    }

    // If this is an AJAX request and we have an error, return JSON
    if ($is_ajax) {
        $response = [
            'success' => false,
            'message' => $error
        ];
        echo json_encode($response);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aling Hera's Online Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-logo">
            <img src="assets/images/logo.png" alt="Shop Logo" onerror="this.src='assets/images/logo.png'">
            <h1 class="auth-title">Aling Hera's Online Shoping</h1>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <h2 class="auth-subtitle">Email address</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <h2 class="auth-subtitle">Passdog</h2>
                <div class="password-field">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                <div class="forgot-password">
                    <a href="forgot-password.php">Forgot password?</a>
                </div>
            </div>

            <button type="submit" class="auth-btn">Login</button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Create one now</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>
</body>
</html>
