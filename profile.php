<?php
session_start();
require_once "config/database.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Get user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$result = $stmt->get_result();
$user = mysqli_fetch_assoc($result);

// Debug user data
if (!$user) {
    // If user data is not found, create a default user object
    $user = [
        'username' => $_SESSION["username"] ?? 'User',
        'email' => 'user@example.com'
    ];
}

// Get user's orders
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$orders = mysqli_fetch_all($stmt->get_result(), MYSQLI_ASSOC);

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        // Check if username exists
        $sql = "SELECT id FROM users WHERE username = ? AND id != ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $username, $_SESSION["user_id"]);
        mysqli_stmt_execute($stmt);
        if(mysqli_stmt_get_result($stmt)->num_rows > 0){
            $error = "This username is already taken.";
        } else {
            // Check if email exists
            $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $email, $_SESSION["user_id"]);
            mysqli_stmt_execute($stmt);
            if(mysqli_stmt_get_result($stmt)->num_rows > 0){
                $error = "This email is already taken.";
            } else {
                // Update profile
                $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $_SESSION["user_id"]);
                if(mysqli_stmt_execute($stmt)){
                    $success = "Profile updated successfully.";
                    $_SESSION["username"] = $username;
                    // Refresh user details
                    $sql = "SELECT * FROM users WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
                    mysqli_stmt_execute($stmt);
                    $user = mysqli_fetch_assoc($stmt->get_result());
                } else {
                    $error = "Something went wrong. Please try again later.";
                }
            }
        }
    } elseif (isset($_POST['update_password'])) {
        $current_password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        // Verify current password
        if(password_verify($current_password, $user['password'])){
            if($new_password === $confirm_password){
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $_SESSION["user_id"]);
                if(mysqli_stmt_execute($stmt)){
                    $success = "Password updated successfully.";
                } else {
                    $error = "Something went wrong. Please try again later.";
                }
            } else {
                $error = "New passwords do not match.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Aling Hera's Embroidery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">
    <?php include 'navigation.php'; ?>

    <!-- Profile Header -->
    <section class="profile-header">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="profile-avatar-container">
                        <div class="profile-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="profile-avatar-edit">
                            <i class="bi bi-pencil"></i>
                        </div>
                    </div>
                    <h1 class="profile-title"><?php echo htmlspecialchars($user['username']); ?></h1>
                    <p class="profile-subtitle mt-2"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Notification Messages -->
    <div class="container">
        <?php if(isset($success)): ?>
            <div class="alert alert-success fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div><?php echo $success; ?></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <div><?php echo $error; ?></div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Profile Content -->
    <div class="container pb-5">
        <div class="row">
            <!-- Profile Navigation -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="profile-nav">
                    <a href="#account-info" class="profile-nav-item active">
                        <i class="bi bi-person-circle profile-nav-icon"></i>
                        Account Information
                    </a>
                    <a href="#security" class="profile-nav-item">
                        <i class="bi bi-shield-lock profile-nav-icon"></i>
                        Security
                    </a>
                    <a href="#orders" class="profile-nav-item">
                        <i class="bi bi-bag-check profile-nav-icon"></i>
                        Order History
                    </a>
                    <a href="#addresses" class="profile-nav-item">
                        <i class="bi bi-geo-alt profile-nav-icon"></i>
                        Addresses
                    </a>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="col-lg-9 col-md-8">
                <div class="tab-content">
                    <!-- Account Information Tab -->
                    <div class="tab-pane fade show active" id="account-info">
                        <div class="profile-card">
                            <div class="profile-card-header">
                                <h5 class="profile-card-title">
                                    <i class="bi bi-person-circle profile-card-icon"></i>
                                    Account Information
                                </h5>
                            </div>
                            <div class="profile-card-body">
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="profile-form">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end mt-3">
                                        <button type="submit" name="update_profile" class="btn btn-primary">
                                            UPDATE PROFILE
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security">
                        <div class="profile-card">
                            <div class="profile-card-header">
                                <h5 class="profile-card-title">
                                    <i class="bi bi-shield-lock profile-card-icon"></i>
                                    Change Password
                                </h5>
                            </div>
                            <div class="profile-card-body">
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="profile-form">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-end mt-4">
                                        <button type="submit" name="update_password" class="btn btn-primary">
                                            <i class="bi bi-shield-check me-2"></i>Change Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Order History Tab -->
                    <div class="tab-pane fade" id="orders">
                        <div class="profile-card">
                            <div class="profile-card-header">
                                <h5 class="profile-card-title">
                                    <i class="bi bi-bag-check profile-card-icon"></i>
                                    Order History
                                </h5>
                            </div>
                            <div class="profile-card-body">
                                <?php if(empty($orders)): ?>
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-bag-x"></i>
                                        </div>
                                        <h5>No Orders Yet</h5>
                                        <p class="empty-state-text">You haven't placed any orders yet.</p>
                                        <a href="index.php" class="btn btn-primary">Start Shopping</a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="order-history-table">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Date</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Payment</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($orders as $order): ?>
                                                    <tr>
                                                        <td class="order-id">#<?php echo $order['id']; ?></td>
                                                        <td class="order-date"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                                        <td class="order-total">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                                        <td>
                                                            <span class="order-badge bg-<?php
                                                                echo $order['status'] === 'pending' ? 'warning' :
                                                                    ($order['status'] === 'processing' ? 'info' :
                                                                    ($order['status'] === 'shipped' ? 'primary' :
                                                                    ($order['status'] === 'delivered' ? 'success' : 'danger')));
                                                            ?>">
                                                                <?php echo ucfirst($order['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="order-payment"><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary view-order-btn" data-order-id="<?php echo $order['id']; ?>">
                                                                View
                                                            </button>
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

                    <!-- Addresses Tab -->
                    <div class="tab-pane fade" id="addresses">
                        <div class="profile-card">
                            <div class="profile-card-header">
                                <h5 class="profile-card-title">
                                    <i class="bi bi-geo-alt profile-card-icon"></i>
                                    Shipping Addresses
                                </h5>
                            </div>
                            <div class="profile-card-body">
                                <div id="addresses-container">
                                    <!-- Addresses will be loaded here -->
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2">Loading addresses...</p>
                                    </div>
                                </div>

                                <div id="no-addresses" class="empty-state" style="display: none;">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-geo"></i>
                                    </div>
                                    <h5>No Addresses Yet</h5>
                                    <p class="empty-state-text">You haven't added any shipping addresses yet.</p>
                                </div>

                                <div class="text-center mt-4">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                        <i class="bi bi-plus-circle me-2"></i>Add New Address
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Address Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAddressModalLabel">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addressForm" class="profile-form">
                        <div class="mb-3">
                            <label for="address_name" class="form-label">Address Name*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" class="form-control" id="address_name" name="address_name" placeholder="Home, Office, etc." required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address_line1" class="form-label">Address Line 1*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-house"></i></span>
                                <input type="text" class="form-control" id="address_line1" name="address_line1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address_line2" class="form-label">Address Line 2 (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <input type="text" class="form-control" id="address_line2" name="address_line2">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City*</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Postal Code*</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-mailbox"></i></span>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                                </div>
                            </div>
                        </div>
                        <div id="address-form-error" class="alert alert-danger mt-3" style="display: none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="saveAddressBtn" class="btn btn-primary">Save Address</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Address Modal -->
    <div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAddressModalLabel">Edit Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAddressForm" class="profile-form">
                        <input type="hidden" id="edit_address_id" name="address_id">
                        <div class="mb-3">
                            <label for="edit_address_name" class="form-label">Address Name*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" class="form-control" id="edit_address_name" name="address_name" placeholder="Home, Office, etc." required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_full_name" class="form-label">Full Name*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Phone Number*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address_line1" class="form-label">Address Line 1*</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-house"></i></span>
                                <input type="text" class="form-control" id="edit_address_line1" name="address_line1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address_line2" class="form-label">Address Line 2 (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <input type="text" class="form-control" id="edit_address_line2" name="address_line2">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_city" class="form-label">City*</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" class="form-control" id="edit_city" name="city" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_postal_code" class="form-label">Postal Code*</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-mailbox"></i></span>
                                    <input type="text" class="form-control" id="edit_postal_code" name="postal_code" required>
                                </div>
                            </div>
                        </div>
                        <div id="edit-address-form-error" class="alert alert-danger mt-3" style="display: none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="updateAddressBtn" class="btn btn-primary">Update Address</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Address Confirmation Modal -->
    <div class="modal fade" id="deleteAddressModal" tabindex="-1" aria-labelledby="deleteAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAddressModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this address?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                    <input type="hidden" id="delete_address_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteAddressBtn" class="btn btn-danger">Delete Address</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="order-details-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading order details...</p>
                    </div>

                    <div id="order-details-content" style="display: none;">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Order Information</h6>
                                <div class="card">
                                    <div class="card-body">
                                        <p class="mb-1"><strong>Order ID:</strong> <span id="order-id"></span></p>
                                        <p class="mb-1"><strong>Date:</strong> <span id="order-date"></span></p>
                                        <p class="mb-1"><strong>Status:</strong> <span id="order-status"></span></p>
                                        <p class="mb-1"><strong>Payment Method:</strong> <span id="order-payment"></span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Shipping Address</h6>
                                <div class="card">
                                    <div class="card-body" id="order-address">
                                        <!-- Shipping address will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-muted mb-3">Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Size</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="order-items">
                                    <!-- Order items will be loaded here -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end" id="order-subtotal"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Shipping:</strong></td>
                                        <td class="text-end" id="order-shipping"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-end" id="order-total"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div id="order-details-error" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
    <script src="assets/js/profile.js"></script>
</body>
</html>