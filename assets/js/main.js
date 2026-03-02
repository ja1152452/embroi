// Cart functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart from localStorage
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    updateCartCount();

    // Product filter functionality
    initProductFilter();

    // Initialize product animations
    animateProducts();

    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;

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

            addToCart(productId, selectedSize);
        });
    });

    // Add item to cart
    function addToCart(productId, size = null) {
        // Check if user is logged in
        if (!isUserLoggedIn()) {
            // Show login modal instead of redirecting
            if (typeof showModal === 'function') {
                showModal('loginModal');
            } else {
                // Fallback if modal function is not available
                showNotification('Please log in to add items to your cart', 'warning');
            }
            return;
        }

        // Get quantity if available
        const quantityInput = document.getElementById('quantity');
        const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

        // Add to cart array
        if (size) {
            // If size is selected, check if the same product with same size exists
            const existingItem = cart.find(item => item.id === productId && item.size === size);
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    id: productId,
                    size: size,
                    quantity: quantity
                });
            }
        } else {
            // No size selected
            const existingItem = cart.find(item => item.id === productId && !item.size);
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    id: productId,
                    quantity: quantity
                });
            }
        }

        // Save to localStorage
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();

        // Show success message
        const sizeText = size ? ` (Size: ${size})` : '';
        showNotification(`Product added to cart!${sizeText}`, 'success');
    }

    // Update cart count in navbar
    function updateCartCount() {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            cartCount.style.display = totalItems > 0 ? 'inline' : 'none';
        }
    }

    // Check if user is logged in
    function isUserLoggedIn() {
        return document.body.classList.contains('logged-in');
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

    // Quantity input handlers
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const newQuantity = parseInt(this.value);

            if (newQuantity > 0) {
                updateCartItemQuantity(productId, newQuantity);
            } else {
                this.value = 1;
            }
        });
    });

    // Update cart item quantity
    function updateCartItemQuantity(productId, quantity) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.quantity = quantity;
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            updateCartTotal();
        }
    }

    // Remove item from cart
    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        updateCartTotal();

        // Remove item from DOM if on cart page
        const cartItem = document.querySelector(`[data-cart-item-id="${productId}"]`);
        if (cartItem) {
            cartItem.remove();
        }
    }

    // Update cart total
    function updateCartTotal() {
        const totalElement = document.querySelector('.cart-total');
        if (totalElement) {
            // Calculate total from cart items
            const total = cart.reduce((sum, item) => {
                const price = parseFloat(item.price);
                return sum + (price * item.quantity);
            }, 0);

            totalElement.textContent = `₱${total.toFixed(2)}`;
        }
    }

    // Initialize cart page
    if (document.querySelector('.cart-page')) {
        updateCartTotal();
    }

    // Product filter functionality
    function initProductFilter() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const productItems = document.querySelectorAll('.product-item');

        if (filterButtons.length === 0 || productItems.length === 0) return;

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));

                // Add active class to clicked button
                this.classList.add('active');

                // Get filter value
                const filterValue = this.getAttribute('data-filter');

                // Filter products
                productItems.forEach(item => {
                    if (filterValue === 'all' || item.classList.contains(filterValue)) {
                        item.style.display = 'block';
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, 50);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }

    // Animate products on page load
    function animateProducts() {
        const products = document.querySelectorAll('.product-card');

        if (products.length === 0) return;

        products.forEach((product, index) => {
            setTimeout(() => {
                product.style.opacity = '1';
                product.style.transform = 'translateY(0)';
            }, 100 * index);
        });
    }


});