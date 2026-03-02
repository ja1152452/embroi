<?php
session_start();
require_once "config/database.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Get cart items from database
$cart_items = [];
$total = 0;

if(isset($_SESSION['user_id'])) {
    // Get cart items from localStorage via JavaScript
    // The actual cart data will be processed on the client side
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Aling Hera's Embroidery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">
    <?php include 'navigation.php'; ?>

    <!-- Cart Header -->
    <section class="cart-header">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="cart-title">Shopping Cart</h1>
                    <p class="cart-subtitle mt-2">Review your items and proceed to checkout</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cart Content -->
    <section class="cart-container">
        <div class="container">
            <!-- Empty Cart Message -->
            <div id="cart-empty" class="cart-empty" style="display: none;">
                <i class="bi bi-cart-x"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="bi bi-bag me-2"></i>Continue Shopping
                </a>
            </div>

            <!-- Cart Items -->
            <div id="cart-items">
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Cart Items List -->
                        <div class="cart-items-card mb-4">
                            <div class="cart-items-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5><i class="bi bi-cart3 me-2"></i>Your Items</h5>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all" checked>
                                        <label class="form-check-label" for="select-all">Select All</label>
                                    </div>
                                </div>
                            </div>
                            <div id="cart-items-list">
                                <!-- Cart items will be dynamically added here -->
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Order Summary -->
                        <div class="order-summary-card">
                            <div class="order-summary-header">
                                <h5><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                            </div>
                            <div class="order-summary-body">
                                <div class="summary-item">
                                    <span>Subtotal</span>
                                    <span id="subtotal">₱0.00</span>
                                </div>
                                <div class="summary-item">
                                    <span>Shipping</span>
                                    <span id="shipping">₱0.00</span>
                                </div>
                                <div class="summary-item total">
                                    <span>Total</span>
                                    <span id="total">₱0.00</span>
                                </div>
                                <button id="checkout-btn" class="btn btn-primary checkout-btn w-100" disabled>
                                    <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                                </button>
                                <div class="text-center mt-3">
                                    <a href="index.php" class="text-decoration-none">
                                        <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                                    </a>
                                </div>
                            </div>
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
        // Initialize cart page
        document.addEventListener('DOMContentLoaded', function() {
            updateCartDisplay();

            // Add event listener for select all checkbox
            const selectAllCheckbox = document.getElementById('select-all');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                    updateSelectedItems();

                    // Show notification
                    if (isChecked) {
                        showNotification('All items selected', 'info');
                    } else {
                        showNotification('All items deselected', 'info');
                    }
                });
            }
        });

        function updateCartDisplay() {
            const cartItemsList = document.getElementById('cart-items-list');
            const cartEmpty = document.getElementById('cart-empty');
            const cartItems = document.getElementById('cart-items');
            const checkoutBtn = document.getElementById('checkout-btn');

            // Get cart from localStorage
            const cart = JSON.parse(localStorage.getItem('cart')) || [];

            if (cart.length === 0) {
                cartEmpty.style.display = 'block';
                cartItems.style.display = 'none';
                checkoutBtn.disabled = true;
                return;
            }

            cartEmpty.style.display = 'none';
            cartItems.style.display = 'block';
            checkoutBtn.disabled = false;

            // Clear current items
            cartItemsList.innerHTML = '';

            // Fetch product details and update display
            let subtotal = 0;
            let processedItems = 0;
            const totalItems = cart.length;

            cart.forEach(item => {
                // Validate item data
                if (!item.id) {
                    console.error('Invalid item in cart:', item);
                    processedItems++;
                    if (processedItems >= totalItems && subtotal === 0) {
                        cartEmpty.style.display = 'block';
                        cartItems.style.display = 'none';
                    }
                    return;
                }

                fetch(`get_product.php?id=${encodeURIComponent(item.id)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Product fetch failed: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(product => {
                        if (!product || !product.price) {
                            console.error('Invalid product data:', product);
                            processedItems++;
                            if (processedItems >= totalItems && subtotal === 0) {
                                cartEmpty.style.display = 'block';
                                cartItems.style.display = 'none';
                            }
                            return;
                        }

                        const itemTotal = product.price * item.quantity;
                        subtotal += itemTotal;

                        const cartItem = document.createElement('div');
                        cartItem.className = 'cart-item';
                        cartItem.dataset.cartItemId = item.id;
                        cartItem.dataset.price = product.price;
                        cartItem.dataset.quantity = item.quantity;
                        cartItem.innerHTML = `
                            <div class="row align-items-center">
                                <div class="col-md-1 col-2">
                                    <div class="form-check">
                                        <input class="form-check-input item-checkbox" type="checkbox" value="${item.id}"
                                            id="check-${item.id}" checked data-price="${product.price}" data-quantity="${item.quantity}"
                                            onchange="updateSelectedItems()">
                                        <label class="form-check-label" for="check-${item.id}"></label>
                                    </div>
                                </div>
                                <div class="col-md-2 col-3">
                                    <img src="${product.image}" alt="${product.name}" class="cart-item-image img-fluid">
                                </div>
                                <div class="col-md-3 col-7">
                                    <div class="cart-item-details">
                                        <h6>${product.name}</h6>
                                        <div class="cart-item-price">₱${parseFloat(product.price).toFixed(2)} per item</div>
                                        ${item.size ? `<div class="cart-item-size">Size: ${item.size}</div>` : ''}
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mt-3 mt-md-0">
                                    <div class="input-group quantity-control">
                                        <button class="btn" type="button" onclick="updateQuantity('${item.id}', -1)">-</button>
                                        <input type="number" class="form-control quantity-input" value="${item.quantity}" min="1" max="${product.stock}" onchange="updateQuantity('${item.id}', this.value)">
                                        <button class="btn" type="button" onclick="updateQuantity('${item.id}', 1)">+</button>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 mt-3 mt-md-0 text-end">
                                    <div class="fw-bold fs-5">₱${itemTotal.toFixed(2)}</div>
                                </div>
                                <div class="col-md-1 col-2 mt-3 mt-md-0 text-end">
                                    <button class="remove-item-btn" onclick="removeFromCart('${item.id}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        cartItemsList.appendChild(cartItem);

                        processedItems++;
                        // Update totals when all items are processed
                        if (processedItems >= totalItems) {
                            updateTotals(subtotal);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching product:', error);
                        processedItems++;
                        if (processedItems >= totalItems && subtotal === 0) {
                            cartEmpty.style.display = 'block';
                            cartItems.style.display = 'none';
                        }
                    });
            });
        }

        function updateQuantity(productId, change) {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const item = cart.find(item => item.id === productId);

            if (!item) return;

            // Store original quantity for comparison
            const originalQuantity = item.quantity;

            if (typeof change === 'number') {
                // Increment or decrement
                item.quantity = Math.max(1, item.quantity + change);
            } else {
                // Direct value change
                const newQuantity = parseInt(change) || 1;
                item.quantity = Math.max(1, newQuantity);
            }

            // Ensure quantity is a valid number
            if (isNaN(item.quantity) || item.quantity < 1) {
                item.quantity = 1;
            }

            // If quantity didn't change, don't update
            if (originalQuantity === item.quantity) return;

            // Find the cart item element
            const cartItem = document.querySelector(`[data-cart-item-id="${productId}"]`);
            if (cartItem) {
                // Update the data-quantity attribute
                cartItem.dataset.quantity = item.quantity;

                // Update the checkbox data-quantity attribute
                const checkbox = document.querySelector(`#check-${productId}`);
                if (checkbox) {
                    checkbox.dataset.quantity = item.quantity;
                }

                // Add pulse animation to the price
                const priceElement = cartItem.querySelector('.fw-bold');
                if (priceElement) {
                    priceElement.classList.add('price-update-pulse');
                    setTimeout(() => {
                        priceElement.classList.remove('price-update-pulse');
                    }, 500);
                }
            }

            localStorage.setItem('cart', JSON.stringify(cart));

            // Update the UI
            updateCartDisplay();
            updateCartCount();

            // Show appropriate notification
            if (originalQuantity < item.quantity) {
                showNotification('Quantity increased', 'info');
            } else {
                showNotification('Quantity decreased', 'info');
            }
        }

        // Show notification
        function showNotification(message, type = 'info') {
            // Remove any existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());

            // Create icon based on notification type
            let icon = 'info-circle';
            if (type === 'success') icon = 'check-circle';
            if (type === 'danger') icon = 'exclamation-circle';
            if (type === 'warning') icon = 'exclamation-triangle';

            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} notification`;
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi bi-${icon} me-2"></i>
                    <span>${message}</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            document.body.appendChild(notification);

            // Add click event to close button
            const closeBtn = notification.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    notification.remove();
                });
            }

            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.classList.add('fade-out');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        function removeFromCart(productId) {
            // Find the cart item element
            const cartItem = document.querySelector(`[data-cart-item-id="${productId}"]`);

            if (cartItem) {
                // Add animation class
                cartItem.classList.add('removing');

                // Wait for animation to complete
                setTimeout(() => {
                    // Remove from localStorage
                    let cart = JSON.parse(localStorage.getItem('cart')) || [];
                    cart = cart.filter(item => item.id !== productId);
                    localStorage.setItem('cart', JSON.stringify(cart));

                    // Update UI
                    updateCartCount();
                    updateCartDisplay();

                    // Show notification
                    showNotification('Item removed from cart', 'success');
                }, 300);
            } else {
                // If element not found, just remove from localStorage
                let cart = JSON.parse(localStorage.getItem('cart')) || [];
                cart = cart.filter(item => item.id !== productId);
                localStorage.setItem('cart', JSON.stringify(cart));

                updateCartCount();
                updateCartDisplay();
                showNotification('Item removed from cart', 'success');
            }
        }

        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                cartCount.textContent = totalItems;
                cartCount.style.display = totalItems > 0 ? 'inline' : 'none';
            }
        }

        function updateTotals(subtotal) {
            updateSelectedItems(); // This will calculate based on selected items
        }

        // Function to update totals based on selected items
        function updateSelectedItems() {
            const checkboxes = document.querySelectorAll('.item-checkbox:checked');
            let selectedSubtotal = 0;

            // Calculate subtotal based on selected items
            checkboxes.forEach(checkbox => {
                const price = parseFloat(checkbox.dataset.price);
                const quantity = parseInt(checkbox.dataset.quantity);
                selectedSubtotal += price * quantity;
            });

            const shipping = selectedSubtotal > 0 ? 100 : 0; // Example shipping cost
            const total = selectedSubtotal + shipping;

            // Update the display
            document.getElementById('subtotal').textContent = `₱${selectedSubtotal.toFixed(2)}`;
            document.getElementById('shipping').textContent = `₱${shipping.toFixed(2)}`;
            document.getElementById('total').textContent = `₱${total.toFixed(2)}`;

            // Update checkout button state
            const checkoutBtn = document.getElementById('checkout-btn');
            checkoutBtn.disabled = selectedSubtotal <= 0;

            // Update item styling based on selection
            document.querySelectorAll('.cart-item').forEach(item => {
                const itemId = item.dataset.cartItemId;
                const isChecked = document.querySelector(`.item-checkbox[value="${itemId}"]`)?.checked;

                if (isChecked) {
                    item.classList.add('selected');
                    item.classList.remove('not-selected');
                } else {
                    item.classList.remove('selected');
                    item.classList.add('not-selected');
                }
            });

            // Update selected items count
            const selectedCount = checkboxes.length;
            const totalItems = document.querySelectorAll('.item-checkbox').length;

            // Update the "Select All" checkbox state
            const selectAllCheckbox = document.getElementById('select-all');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = selectedCount > 0 && selectedCount === totalItems;
                selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalItems;
            }

            // Update the checkout button text
            if (selectedCount > 0) {
                checkoutBtn.innerHTML = `<i class="bi bi-credit-card me-2"></i>Checkout (${selectedCount} item${selectedCount > 1 ? 's' : ''})`;
            } else {
                checkoutBtn.innerHTML = `<i class="bi bi-credit-card me-2"></i>Proceed to Checkout`;
            }
        }

        // Checkout button handler
        document.getElementById('checkout-btn').addEventListener('click', function() {
            // Get selected items
            const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked')).map(checkbox => checkbox.value);

            if (selectedItems.length === 0) {
                showNotification('Please select at least one item to checkout', 'warning');
                return;
            }

            // Add loading state
            const btn = this;
            const originalText = btn.innerHTML;

            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
            btn.disabled = true;

            // Create a temporary cart with only selected items
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const selectedCart = cart.filter(item => selectedItems.includes(item.id));

            // Store selected items in sessionStorage for checkout page
            sessionStorage.setItem('checkoutItems', JSON.stringify(selectedCart));

            // Simulate processing (you can remove this in production)
            setTimeout(() => {
                window.location.href = 'checkout.php';
            }, 800);
        });
    </script>
</body>
</html>