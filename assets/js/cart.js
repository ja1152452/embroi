// Cart functionality
class ShoppingCart {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('cart')) || [];
        this.panel = document.querySelector('.cart-panel');
        this.cartItems = document.querySelector('.cart-items');
        this.cartSummary = document.querySelector('.cart-summary');
        this.init();
    }

    init() {
        // Toggle cart panel
        document.querySelector('.cart-toggle').addEventListener('click', () => {
            this.panel.classList.toggle('active');
            this.updateCart();
        });

        // Close cart panel
        document.querySelector('.cart-panel-close').addEventListener('click', () => {
            this.panel.classList.remove('active');
        });

        // Close cart panel when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.panel.contains(e.target) && !e.target.classList.contains('cart-toggle')) {
                this.panel.classList.remove('active');
            }
        });

        // Update cart count in header
        this.updateCartCount();
    }

    addToCart(product) {
        const existingItem = this.cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image,
                quantity: 1
            });
        }

        this.saveCart();
        this.updateCart();
        this.updateCartCount();
        this.showToast('Product added to cart', 'success');
    }

    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.saveCart();
        this.updateCart();
        this.updateCartCount();
        this.showToast('Product removed from cart', 'warning');
    }

    updateQuantity(productId, quantity) {
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            item.quantity = parseInt(quantity);
            if (item.quantity <= 0) {
                this.removeFromCart(productId);
            } else {
                this.saveCart();
                this.updateCart();
                this.updateCartCount();
            }
        }
    }

    updateCart() {
        if (!this.cartItems) return;

        this.cartItems.innerHTML = this.cart.map(item => `
            <div class="cart-item">
                <img src="${item.image}" alt="${item.name}">
                <div class="cart-item-info">
                    <h6 class="cart-item-title">${item.name}</h6>
                    <p class="cart-item-price">$${item.price.toFixed(2)}</p>
                    <div class="cart-item-quantity">
                        <button onclick="cart.updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                        <input type="number" value="${item.quantity}" min="1" 
                            onchange="cart.updateQuantity(${item.id}, this.value)">
                        <button onclick="cart.updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                    </div>
                </div>
                <button class="cart-item-remove" onclick="cart.removeFromCart(${item.id})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');

        this.updateCartSummary();
    }

    updateCartSummary() {
        if (!this.cartSummary) return;

        const subtotal = this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        const shipping = subtotal > 0 ? 10 : 0;
        const total = subtotal + shipping;

        this.cartSummary.innerHTML = `
            <div class="cart-summary-row">
                <span>Subtotal</span>
                <span>$${subtotal.toFixed(2)}</span>
            </div>
            <div class="cart-summary-row">
                <span>Shipping</span>
                <span>$${shipping.toFixed(2)}</span>
            </div>
            <div class="cart-summary-row cart-summary-total">
                <span>Total</span>
                <span>$${total.toFixed(2)}</span>
            </div>
            <button class="btn btn-primary w-100 mt-3" onclick="window.location.href='checkout.php'">
                Proceed to Checkout
            </button>
        `;
    }

    updateCartCount() {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            const count = this.cart.reduce((total, item) => total + item.quantity, 0);
            cartCount.textContent = count;
            cartCount.style.display = count > 0 ? 'block' : 'none';
        }
    }

    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.cart));
    }

    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            </div>
            <div class="toast-message">${message}</div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        const container = document.querySelector('.toast-container') || (() => {
            const div = document.createElement('div');
            div.className = 'toast-container';
            document.body.appendChild(div);
            return div;
        })();

        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
}

// Initialize cart
const cart = new ShoppingCart();

// Add to cart buttons
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', (e) => {
        const product = {
            id: e.target.dataset.id,
            name: e.target.dataset.name,
            price: parseFloat(e.target.dataset.price),
            image: e.target.dataset.image
        };
        cart.addToCart(product);
    });
}); 