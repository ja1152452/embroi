<?php
session_start();
require_once "config/database.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Get user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc($stmt->get_result());

// Get user's saved addresses
$addresses = [];
$check_table = "SHOW TABLES LIKE 'addresses'";
$table_exists = mysqli_query($conn, $check_table);
if (mysqli_num_rows($table_exists) > 0) {
    $sql = "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $addresses[] = $row;
    }
}

$error = '';
$success = '';

// Process order submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get address option
    $address_option = filter_input(INPUT_POST, 'address_option', FILTER_SANITIZE_STRING);
    $payment_method = trim(filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING));
    $total_amount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);

    // Initialize variables
    $shipping_address = '';
    $contact_number = '';
    $address_id = null;

    // Process based on address option
    if ($address_option === 'saved') {
        // Get selected address ID
        $address_id = filter_input(INPUT_POST, 'saved_address_id', FILTER_VALIDATE_INT);

        if ($address_id) {
            // Get address details from database
            $sql = "SELECT * FROM addresses WHERE id = ? AND user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ii", $address_id, $user_id);
            mysqli_stmt_execute($stmt);
            $address_data = mysqli_fetch_assoc($stmt->get_result());

            if ($address_data) {
                // Format address for storage
                $shipping_address = $address_data['full_name'] . "\n" .
                                   $address_data['address_line1'] . "\n" .
                                   ($address_data['address_line2'] ? $address_data['address_line2'] . "\n" : '') .
                                   $address_data['city'] . ", " . $address_data['postal_code'];
                $contact_number = $address_data['phone'];
            } else {
                $error = "Selected address not found.";
            }
        } else {
            $error = "Invalid address selected.";
        }
    } else {
        // Using new address
        $shipping_address = trim(filter_input(INPUT_POST, 'shipping_address', FILTER_SANITIZE_STRING));
        $contact_number = trim(filter_input(INPUT_POST, 'contact_number', FILTER_SANITIZE_STRING));

        // Check if user wants to save this address
        $save_address = filter_input(INPUT_POST, 'save_address', FILTER_VALIDATE_INT);
    }

    // Validate required data
    if (empty($shipping_address) || empty($contact_number) || empty($payment_method) || $total_amount <= 0) {
        $error = "Please fill in all required fields with valid information.";
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            // Save new address to profile if requested
            if ($address_option === 'new' && isset($save_address) && $save_address == 1) {
                // Extract address parts (this is a simplified version)
                $address_lines = explode("\n", $shipping_address);
                $full_name = $user['username']; // Default to username if not provided
                $address_line1 = isset($address_lines[0]) ? $address_lines[0] : '';
                $address_line2 = isset($address_lines[1]) ? $address_lines[1] : '';
                $city_postal = isset($address_lines[2]) ? $address_lines[2] : '';

                // Extract city and postal code (simplified)
                $city_parts = explode(',', $city_postal);
                $city = isset($city_parts[0]) ? trim($city_parts[0]) : '';
                $postal_code = isset($city_parts[1]) ? trim($city_parts[1]) : '';

                // Check if this is the first address (to set as default)
                $is_default = empty($addresses) ? 1 : 0;

                // Insert address
                $sql = "INSERT INTO addresses (user_id, address_name, full_name, phone, address_line1, address_line2, city, postal_code, is_default)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                $address_name = "Address " . (count($addresses) + 1);
                mysqli_stmt_bind_param($stmt, "isssssssi", $user_id, $address_name, $full_name, $contact_number, $address_line1, $address_line2, $city, $postal_code, $is_default);
                mysqli_stmt_execute($stmt);
            }

            // Create order
            $sql = "INSERT INTO orders (user_id, total_amount, status, shipping_address, contact_number, payment_method)
                    VALUES (?, ?, 'pending', ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "idsss", $user_id, $total_amount, $shipping_address, $contact_number, $payment_method);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error creating order: " . mysqli_stmt_error($stmt));
            }

            $order_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            // Process order items from cart
            $cart_items = json_decode($_POST['cart_items'], true);

            if (!empty($cart_items)) {
                foreach ($cart_items as $item) {
                    // Validate item data
                    if (!isset($item['id']) || !isset($item['quantity'])) {
                        continue;
                    }

                    $product_id = $item['id'];
                    $quantity = $item['quantity'];
                    $size = isset($item['size']) ? $item['size'] : null;

                    // Get product price
                    $sql = "SELECT price FROM products WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $product_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $product = mysqli_fetch_assoc($result);
                    mysqli_stmt_close($stmt);

                    if (!$product) {
                        continue;
                    }

                    $price = $product['price'];

                    // Insert order item
                    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "iiids", $order_id, $product_id, $quantity, $price, $size);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    // Update product stock
                    $sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "ii", $quantity, $product_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }

            // Commit transaction
            mysqli_commit($conn);

            // Redirect to order confirmation
            header("location: order_confirmation.php?id=" . $order_id);
            exit;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "An error occurred while processing your order: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Aling Hera's Embroidery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">
    <?php include 'navigation.php'; ?>

    <!-- Checkout Content -->
    <div class="container py-5">
        <h1 class="mb-4">Checkout</h1>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <!-- Shipping Information -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Shipping Information</h5>
                        <form id="checkout-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <!-- Address Selection -->
                            <div class="mb-3">
                                <label class="form-label">Shipping Address</label>

                                <?php if (!empty($addresses)): ?>
                                <!-- Saved Addresses -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="address_option" id="use_saved_address" value="saved" checked>
                                        <label class="form-check-label" for="use_saved_address">
                                            Use a saved address
                                        </label>
                                    </div>

                                    <div id="saved_address_container" class="mt-3">
                                        <select class="form-select" id="saved_address_id" name="saved_address_id">
                                            <?php foreach ($addresses as $address): ?>
                                                <option value="<?php echo $address['id']; ?>"
                                                    data-phone="<?php echo htmlspecialchars($address['phone']); ?>"
                                                    data-address="<?php echo htmlspecialchars($address['address_line1']);
                                                        if (!empty($address['address_line2'])) echo "\n" . htmlspecialchars($address['address_line2']);
                                                        echo "\n" . htmlspecialchars($address['city']) . ", " . htmlspecialchars($address['postal_code']); ?>">
                                                    <?php echo htmlspecialchars($address['address_name']); ?> -
                                                    <?php echo htmlspecialchars($address['full_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <div class="card mt-2 p-2 bg-light">
                                            <div id="selected_address_details"></div>
                                        </div>
                                    </div>

                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="radio" name="address_option" id="use_new_address" value="new">
                                        <label class="form-check-label" for="use_new_address">
                                            Use a new address
                                        </label>
                                    </div>
                                </div>
                                <?php else: ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="address_option" id="use_new_address" value="new" checked>
                                        <label class="form-check-label" for="use_new_address">
                                            Enter a new address
                                        </label>
                                    </div>
                                <?php endif; ?>

                                <!-- New Address Form -->
                                <div id="new_address_container" class="mt-3" <?php echo !empty($addresses) ? 'style="display: none;"' : ''; ?>>
                                    <div class="mb-3">
                                        <label for="shipping_address" class="form-label">Address Details</label>
                                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" <?php echo !empty($addresses) ? '' : 'required'; ?>></textarea>
                                        <div class="form-text">Enter your complete address including street, building, city, and postal code</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contact_number" class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" id="contact_number" name="contact_number" <?php echo !empty($addresses) ? '' : 'required'; ?>>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="save_address" name="save_address" value="1">
                                        <label class="form-check-label" for="save_address">
                                            Save this address to my profile
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="cod">Cash on Delivery</option>
                                    <option value="gcash">GCash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <input type="hidden" name="total_amount" id="total_amount">
                            <input type="hidden" name="cart_items" id="cart_items">
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Order Summary -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Summary</h5>
                        <div id="order-items">
                            <!-- Order items will be dynamically added here -->
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="subtotal">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span id="shipping">₱0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong id="total">₱0.00</strong>
                        </div>
                        <button type="submit" form="checkout-form" class="btn btn-primary w-100">
                            Place Order
                        </button>
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
    <script>
        // Initialize checkout page
        document.addEventListener('DOMContentLoaded', function() {
            updateOrderSummary();
            setupAddressSelection();
        });

        // Handle address selection
        function setupAddressSelection() {
            const useSavedAddressRadio = document.getElementById('use_saved_address');
            const useNewAddressRadio = document.getElementById('use_new_address');
            const savedAddressContainer = document.getElementById('saved_address_container');
            const newAddressContainer = document.getElementById('new_address_container');
            const savedAddressSelect = document.getElementById('saved_address_id');
            const shippingAddressTextarea = document.getElementById('shipping_address');
            const contactNumberInput = document.getElementById('contact_number');
            const selectedAddressDetails = document.getElementById('selected_address_details');

            // Skip if elements don't exist (no saved addresses)
            if (!useSavedAddressRadio || !savedAddressSelect) return;

            // Function to update the selected address details display
            function updateSelectedAddressDetails() {
                const selectedOption = savedAddressSelect.options[savedAddressSelect.selectedIndex];
                const address = selectedOption.getAttribute('data-address');
                const phone = selectedOption.getAttribute('data-phone');

                if (selectedAddressDetails) {
                    selectedAddressDetails.innerHTML = `
                        <p class="mb-1"><strong>${selectedOption.text}</strong></p>
                        <p class="mb-1">${address.replace(/\n/g, '<br>')}</p>
                        <p class="mb-0"><i class="bi bi-telephone me-2"></i>${phone}</p>
                    `;
                }

                // Also update the hidden fields for form submission
                if (contactNumberInput) {
                    contactNumberInput.value = phone;
                }
            }

            // Initial update
            if (savedAddressSelect) {
                updateSelectedAddressDetails();

                // Update when selection changes
                savedAddressSelect.addEventListener('change', updateSelectedAddressDetails);
            }

            // Toggle between saved and new address
            if (useSavedAddressRadio && useNewAddressRadio) {
                useSavedAddressRadio.addEventListener('change', function() {
                    if (this.checked) {
                        savedAddressContainer.style.display = 'block';
                        newAddressContainer.style.display = 'none';

                        // Make new address fields not required
                        if (shippingAddressTextarea) shippingAddressTextarea.removeAttribute('required');
                        if (contactNumberInput) contactNumberInput.removeAttribute('required');

                        // Update selected address details
                        updateSelectedAddressDetails();
                    }
                });

                useNewAddressRadio.addEventListener('change', function() {
                    if (this.checked) {
                        savedAddressContainer.style.display = 'none';
                        newAddressContainer.style.display = 'block';

                        // Make new address fields required
                        if (shippingAddressTextarea) shippingAddressTextarea.setAttribute('required', '');
                        if (contactNumberInput) contactNumberInput.setAttribute('required', '');

                        // Clear the values from saved address
                        if (contactNumberInput) contactNumberInput.value = '';
                    }
                });
            }
        }

        function updateOrderSummary() {
            const orderItems = document.getElementById('order-items');
            const cart = JSON.parse(localStorage.getItem('cart')) || [];

            if (cart.length === 0) {
                window.location.href = 'cart.php';
                return;
            }

            // Clear current items
            orderItems.innerHTML = '';

            // Fetch product details and update display
            let subtotal = 0;
            let processedItems = 0;
            const totalItems = cart.length;

            cart.forEach(item => {
                // Validate item data
                if (!item.id || !item.quantity) {
                    console.error('Invalid item in cart:', item);
                    processedItems++;
                    return;
                }

                fetch(`get_product.php?id=${encodeURIComponent(item.id)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Product fetch failed');
                        }
                        return response.json();
                    })
                    .then(product => {
                        if (!product || !product.price) {
                            console.error('Invalid product data:', product);
                            return;
                        }

                        const itemTotal = product.price * item.quantity;
                        subtotal += itemTotal;

                        const orderItem = document.createElement('div');
                        orderItem.className = 'd-flex justify-content-between mb-2';
                        orderItem.innerHTML = `
                            <span>
                                ${product.name} x ${item.quantity}
                                ${item.size ? `<small class="text-muted d-block">Size: ${item.size}</small>` : ''}
                            </span>
                            <span>₱${itemTotal.toFixed(2)}</span>
                        `;
                        orderItems.appendChild(orderItem);

                        // Update totals when all items are processed
                        processedItems++;
                        if (processedItems >= totalItems) {
                            updateTotals(subtotal);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching product:', error);
                        processedItems++;
                        if (processedItems >= totalItems) {
                            updateTotals(subtotal);
                        }
                    });
            });
        }

        function updateTotals(subtotal) {
            const shipping = subtotal > 0 ? 100 : 0; // Example shipping cost
            const total = subtotal + shipping;

            document.getElementById('subtotal').textContent = `₱${subtotal.toFixed(2)}`;
            document.getElementById('shipping').textContent = `₱${shipping.toFixed(2)}`;
            document.getElementById('total').textContent = `₱${total.toFixed(2)}`;
            document.getElementById('total_amount').value = total;
        }

        // Form submission handler
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            if (cart.length === 0) {
                e.preventDefault();
                alert('Your cart is empty!');
                return;
            }

            // Get address option
            const addressOption = document.querySelector('input[name="address_option"]:checked');
            const paymentMethod = document.getElementById('payment_method').value;

            if (!addressOption || !paymentMethod) {
                e.preventDefault();
                alert('Please select an address option and payment method.');
                return;
            }

            // Validate based on address option
            if (addressOption.value === 'saved') {
                const savedAddressId = document.getElementById('saved_address_id');
                if (!savedAddressId || !savedAddressId.value) {
                    e.preventDefault();
                    alert('Please select a saved address.');
                    return;
                }
            } else {
                // Validate new address fields
                const shippingAddress = document.getElementById('shipping_address').value.trim();
                const contactNumber = document.getElementById('contact_number').value.trim();

                if (!shippingAddress || !contactNumber) {
                    e.preventDefault();
                    alert('Please fill in all address fields.');
                    return;
                }
            }

            // Set cart items in hidden field
            document.getElementById('cart_items').value = JSON.stringify(cart);

            // Add loading state to submit button
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
            submitBtn.disabled = true;

            // Clear cart after successful submission
            localStorage.removeItem('cart');
        });
    </script>
</body>
</html>