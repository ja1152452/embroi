// Authentication Modals Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals
    initAuthModals();

    // Handle form submissions
    setupFormSubmissions();
});

// Toggle password visibility for login form
function toggleLoginPassword() {
    const passwordField = document.getElementById('loginPassword');
    const toggleIcon = document.getElementById('loginToggleIcon');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Toggle password visibility for register form
function toggleRegisterPassword() {
    const passwordField = document.getElementById('registerPassword');
    const toggleIcon = document.getElementById('registerToggleIcon');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Toggle confirm password visibility for register form
function toggleRegisterConfirmPassword() {
    const passwordField = document.getElementById('registerConfirmPassword');
    const toggleIcon = document.getElementById('registerConfirmToggleIcon');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Initialize authentication modals
function initAuthModals() {
    // Create modal containers if they don't exist
    if (!document.getElementById('loginModal')) {
        createLoginModal();
    }

    if (!document.getElementById('registerModal')) {
        createRegisterModal();
    }

    // Add event listeners to nav links
    const loginLinks = document.querySelectorAll('a[href="login.php"]');
    const registerLinks = document.querySelectorAll('a[href="register.php"]');
    const cartLoginBtn = document.getElementById('cartLoginBtn');

    loginLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            showModal('loginModal');
        });
    });

    registerLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            showModal('registerModal');
        });
    });

    // Add event listener for cart login button
    if (cartLoginBtn) {
        cartLoginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showModal('loginModal');
            // Show notification that login is required
            showNotification('Please log in to access your cart', 'info');
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        const loginModal = document.getElementById('loginModal');
        const registerModal = document.getElementById('registerModal');

        if (e.target === loginModal) {
            hideModal('loginModal');
        }

        if (e.target === registerModal) {
            hideModal('registerModal');
        }
    });
}

// Create login modal HTML
function createLoginModal() {
    const modalHTML = `
    <div id="loginModal" class="auth-modal">
        <div class="auth-modal-content">
            <span class="auth-modal-close">&times;</span>
            <div class="auth-logo">
                <img src="assets/images/logo.png" alt="Shop Logo" onerror="this.src='assets/images/logo.png'">
                <h1 class="auth-title">Aling Hera's Online Shop</h1>
            </div>
            <div id="loginError" class="alert alert-danger" style="display: none;"></div>
            <form id="loginForm" method="post">
                <h2 class="auth-subtitle">Email address</h2>
                <div class="form-group">
                    <input type="email" name="email" id="loginEmail" class="form-control" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <h2 class="auth-subtitle">Password</h2>
                    <div class="password-field">
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="toggleLoginPassword()">
                            <i class="bi bi-eye" id="loginToggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
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
                <p>Don't have an account? <a href="#" id="switchToRegister">Create one now</a></p>
            </div>
        </div>
    </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Add event listeners
    document.querySelector('.auth-modal-close').addEventListener('click', function() {
        hideModal('loginModal');
    });

    document.getElementById('switchToRegister').addEventListener('click', function(e) {
        e.preventDefault();
        hideModal('loginModal');
        showModal('registerModal');
    });
}

// Create register modal HTML
function createRegisterModal() {
    const modalHTML = `
    <div id="registerModal" class="auth-modal">
        <div class="auth-modal-content">
            <span class="auth-modal-close">&times;</span>
            <div class="auth-logo">
                <img src="assets/images/logo.png" alt="Shop Logo" onerror="this.src='assets/images/logo.png'">
                <h1 class="auth-title">Aling Hera's Online Shop</h1>
            </div>
            <div id="registerError" class="alert alert-danger" style="display: none;"></div>
            <div id="registerSuccess" class="alert alert-success" style="display: none;"></div>
            <form id="registerForm" method="post">
                <h2 class="auth-subtitle">Username</h2>
                <div class="form-group">
                    <input type="text" name="username" id="registerUsername" class="form-control" placeholder="Enter your username" required>
                </div>

                <h2 class="auth-subtitle">Email address</h2>
                <div class="form-group">
                    <input type="email" name="email" id="registerEmail" class="form-control" placeholder="Enter your email" required>
                </div>

                <h2 class="auth-subtitle">Password</h2>
                <div class="form-group">
                    <div class="password-field">
                        <input type="password" name="password" id="registerPassword" class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="toggleRegisterPassword()">
                            <i class="bi bi-eye" id="registerToggleIcon"></i>
                        </button>
                    </div>
                </div>

                <h2 class="auth-subtitle">Confirm Password</h2>
                <div class="form-group">
                    <div class="password-field">
                        <input type="password" name="confirm_password" id="registerConfirmPassword" class="form-control" placeholder="Confirm your password" required>
                        <button type="button" class="password-toggle" onclick="toggleRegisterConfirmPassword()">
                            <i class="bi bi-eye" id="registerConfirmToggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="auth-btn">Register</button>
            </form>
            <div class="auth-footer">
                <p>Already have an account? <a href="#" id="switchToLogin">Login here</a></p>
            </div>
        </div>
    </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Add event listeners
    document.querySelectorAll('.auth-modal-close')[1].addEventListener('click', function() {
        hideModal('registerModal');
    });

    document.getElementById('switchToLogin').addEventListener('click', function(e) {
        e.preventDefault();
        hideModal('registerModal');
        showModal('loginModal');
    });
}

// Show modal
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

// Hide modal
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';

        // Clear form fields and errors
        if (modalId === 'loginModal') {
            document.getElementById('loginForm').reset();
            document.getElementById('loginError').style.display = 'none';
        } else if (modalId === 'registerModal') {
            document.getElementById('registerForm').reset();
            document.getElementById('registerError').style.display = 'none';
            document.getElementById('registerSuccess').style.display = 'none';
        }
    }
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

// Setup form submissions
function setupFormSubmissions() {
    // Login form submission
    document.addEventListener('submit', function(e) {
        if (e.target && e.target.id === 'loginForm') {
            e.preventDefault();

            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            // Create form data
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('ajax', 'true');

            // Send AJAX request
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    hideModal('loginModal');

                    // Create a success notification
                    const notification = document.createElement('div');
                    notification.className = 'alert alert-success alert-dismissible fade show notification-top';
                    notification.setAttribute('role', 'alert');
                    notification.innerHTML = `
                        <strong>Success!</strong> ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.body.appendChild(notification);

                    // Redirect to the appropriate page after a short delay
                    setTimeout(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    }, 1000);
                } else {
                    // Show error message
                    const errorElement = document.getElementById('loginError');
                    errorElement.textContent = data.message;
                    errorElement.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        if (e.target && e.target.id === 'registerForm') {
            e.preventDefault();

            const username = document.getElementById('registerUsername').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('registerConfirmPassword').value;

            // Create form data
            const formData = new FormData();
            formData.append('username', username);
            formData.append('email', email);
            formData.append('password', password);
            formData.append('confirm_password', confirmPassword);
            formData.append('ajax', 'true');

            // Send AJAX request
            fetch('register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successElement = document.getElementById('registerSuccess');
                    successElement.textContent = data.message;
                    successElement.style.display = 'block';

                    // Hide error message if visible
                    document.getElementById('registerError').style.display = 'none';

                    // Clear form
                    document.getElementById('registerForm').reset();

                    // Switch to login after 2 seconds
                    setTimeout(() => {
                        hideModal('registerModal');
                        showModal('loginModal');
                    }, 2000);
                } else {
                    // Show error message
                    const errorElement = document.getElementById('registerError');
                    errorElement.textContent = data.message;
                    errorElement.style.display = 'block';

                    // Hide success message if visible
                    document.getElementById('registerSuccess').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
}
