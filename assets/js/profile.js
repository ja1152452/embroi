// Profile Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Get all tab navigation items
    const navItems = document.querySelectorAll('.profile-nav-item');

    // Get all tab panes
    const tabPanes = document.querySelectorAll('.tab-pane');

    // Add click event to each navigation item
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            // Get the target tab ID from the href attribute
            const targetId = this.getAttribute('href').substring(1);

            // Remove active class from all navigation items
            navItems.forEach(navItem => {
                navItem.classList.remove('active');
            });

            // Add active class to clicked navigation item
            this.classList.add('active');

            // Hide all tab panes
            tabPanes.forEach(pane => {
                pane.classList.remove('show', 'active');
            });

            // Show the target tab pane
            const targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
                console.log('Activated tab:', targetId);

                // Load addresses when the addresses tab is activated
                if (targetId === 'addresses') {
                    loadAddresses();
                }
            } else {
                console.error('Tab pane not found:', targetId);
            }

            // Update URL hash for direct linking (optional)
            window.location.hash = targetId;
        });
    });

    // Check if there's a hash in the URL and activate the corresponding tab
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const targetNavItem = document.querySelector(`.profile-nav-item[href="#${hash}"]`);
        if (targetNavItem) {
            targetNavItem.click();
        }
    }

    // Add a visual indicator for active tab
    function updateActiveTab() {
        // Get the active tab
        const activeTab = document.querySelector('.tab-pane.show.active');
        if (activeTab) {
            const activeTabId = activeTab.id;
            const activeNavItem = document.querySelector(`.profile-nav-item[href="#${activeTabId}"]`);

            // Remove active class from all navigation items
            navItems.forEach(navItem => {
                navItem.classList.remove('active');
            });

            // Add active class to the active navigation item
            if (activeNavItem) {
                activeNavItem.classList.add('active');
            }

            // Load addresses when the addresses tab is activated
            if (activeTabId === 'addresses') {
                loadAddresses();
            }
        }
    }

    // Call the function on page load
    updateActiveTab();

    // Password Toggle Visibility
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');

    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);

            // Toggle password visibility
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="bi bi-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="bi bi-eye"></i>';
            }
        });
    });

    // Profile Avatar Edit
    const avatarEdit = document.querySelector('.profile-avatar-edit');

    if (avatarEdit) {
        avatarEdit.addEventListener('click', function() {
            // Create a file input element
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*';
            fileInput.style.display = 'none';

            // Append to body and trigger click
            document.body.appendChild(fileInput);
            fileInput.click();

            // Handle file selection
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        // Create image element
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-100 h-100';
                        img.style.objectFit = 'cover';

                        // Replace avatar content with image
                        const avatar = document.querySelector('.profile-avatar');
                        avatar.innerHTML = '';
                        avatar.appendChild(img);

                        // Show notification
                        showNotification('Profile picture updated', 'success');

                        // TODO: Upload image to server
                        // This would typically involve an AJAX request to upload the image
                    };

                    reader.readAsDataURL(this.files[0]);
                }

                // Remove the input element
                document.body.removeChild(fileInput);
            });
        });
    }

    // Load Addresses
    function loadAddresses() {
        const addressesContainer = document.getElementById('addresses-container');
        const noAddresses = document.getElementById('no-addresses');

        if (!addressesContainer) return;

        // Show loading state
        addressesContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading addresses...</p>
            </div>
        `;

        // Fetch addresses from server
        fetch('get_addresses.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.addresses && data.addresses.length > 0) {
                        // Render addresses
                        let addressesHTML = '<div class="row">';

                        data.addresses.forEach(address => {
                            addressesHTML += `
                                <div class="col-lg-6 mb-3">
                                    <div class="card h-100 ${address.is_default ? 'border-primary' : ''}">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                ${address.address_name}
                                                ${address.is_default ? '<span class="badge bg-primary ms-2">Default</span>' : ''}
                                            </h6>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item edit-address" href="#" data-address-id="${address.id}">
                                                        <i class="bi bi-pencil me-2"></i>Edit
                                                    </a></li>
                                                    ${!address.is_default ? `<li><a class="dropdown-item set-default-address" href="#" data-address-id="${address.id}">
                                                        <i class="bi bi-star me-2"></i>Set as Default
                                                    </a></li>` : ''}
                                                    <li><a class="dropdown-item text-danger delete-address" href="#" data-address-id="${address.id}">
                                                        <i class="bi bi-trash me-2"></i>Delete
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-1"><strong>${address.full_name}</strong></p>
                                            <p class="mb-1">${address.address_line1}</p>
                                            ${address.address_line2 ? `<p class="mb-1">${address.address_line2}</p>` : ''}
                                            <p class="mb-1">${address.city}, ${address.postal_code}</p>
                                            <p class="mb-0"><i class="bi bi-telephone me-2"></i>${address.phone}</p>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        addressesHTML += '</div>';
                        addressesContainer.innerHTML = addressesHTML;
                        noAddresses.style.display = 'none';
                    } else {
                        // No addresses found
                        addressesContainer.innerHTML = '';
                        noAddresses.style.display = 'block';
                    }
                } else {
                    // Error loading addresses
                    addressesContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            Error loading addresses. Please try again.
                        </div>
                    `;
                    noAddresses.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading addresses:', error);
                addressesContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        Error loading addresses. Please try again.
                    </div>
                `;
                noAddresses.style.display = 'none';
            });
    }

    // Save Address
    const saveAddressBtn = document.getElementById('saveAddressBtn');
    const addressForm = document.getElementById('addressForm');
    const addressFormError = document.getElementById('address-form-error');
    const addAddressModal = document.getElementById('addAddressModal');

    if (saveAddressBtn && addressForm) {
        saveAddressBtn.addEventListener('click', function() {
            // Validate form
            if (!addressForm.checkValidity()) {
                addressForm.reportValidity();
                return;
            }

            // Show loading state
            saveAddressBtn.disabled = true;
            saveAddressBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Saving...';

            // Get form data
            const formData = new FormData(addressForm);

            // Send data to server
            fetch('save_address.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reset form
                    addressForm.reset();

                    // Hide modal
                    const modal = bootstrap.Modal.getInstance(addAddressModal);
                    modal.hide();

                    // Show success notification
                    showNotification('Address saved successfully', 'success');

                    // Reload addresses
                    loadAddresses();
                } else {
                    // Show error message
                    addressFormError.textContent = data.message || 'Error saving address. Please try again.';
                    addressFormError.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error saving address:', error);
                addressFormError.textContent = 'Error saving address. Please try again.';
                addressFormError.style.display = 'block';
            })
            .finally(() => {
                // Reset button state
                saveAddressBtn.disabled = false;
                saveAddressBtn.innerHTML = 'Save Address';
            });
        });
    }

    // Edit Address
    const editAddressModal = document.getElementById('editAddressModal');
    const editAddressForm = document.getElementById('editAddressForm');
    const editAddressFormError = document.getElementById('edit-address-form-error');
    const updateAddressBtn = document.getElementById('updateAddressBtn');

    // Handle edit address button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-address') || e.target.closest('.edit-address')) {
            e.preventDefault();

            const link = e.target.classList.contains('edit-address') ? e.target : e.target.closest('.edit-address');
            const addressId = link.getAttribute('data-address-id');

            if (addressId && editAddressModal) {
                // Show loading state in modal
                if (editAddressForm) {
                    editAddressForm.innerHTML = `
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading address details...</p>
                        </div>
                    `;
                }

                // Show modal
                const modal = new bootstrap.Modal(editAddressModal);
                modal.show();

                // Fetch address details
                fetch(`get_addresses.php`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.addresses) {
                            // Find the address with the matching ID
                            const address = data.addresses.find(addr => addr.id == addressId);

                            if (address && editAddressForm) {
                                // Reset the form
                                editAddressForm.innerHTML = `
                                    <input type="hidden" id="edit_address_id" name="address_id" value="${address.id}">
                                    <div class="mb-3">
                                        <label for="edit_address_name" class="form-label">Address Name*</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                            <input type="text" class="form-control" id="edit_address_name" name="address_name" value="${address.address_name}" placeholder="Home, Office, etc." required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_full_name" class="form-label">Full Name*</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="edit_full_name" name="full_name" value="${address.full_name}" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_phone" class="form-label">Phone Number*</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                            <input type="tel" class="form-control" id="edit_phone" name="phone" value="${address.phone}" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_address_line1" class="form-label">Address Line 1*</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-house"></i></span>
                                            <input type="text" class="form-control" id="edit_address_line1" name="address_line1" value="${address.address_line1}" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_address_line2" class="form-label">Address Line 2 (Optional)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-building"></i></span>
                                            <input type="text" class="form-control" id="edit_address_line2" name="address_line2" value="${address.address_line2 || ''}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_city" class="form-label">City*</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                                <input type="text" class="form-control" id="edit_city" name="city" value="${address.city}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="edit_postal_code" class="form-label">Postal Code*</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-mailbox"></i></span>
                                                <input type="text" class="form-control" id="edit_postal_code" name="postal_code" value="${address.postal_code}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="edit-address-form-error" class="alert alert-danger mt-3" style="display: none;"></div>
                                `;
                            } else {
                                if (editAddressFormError) {
                                    editAddressFormError.textContent = 'Address not found.';
                                    editAddressFormError.style.display = 'block';
                                }
                            }
                        } else {
                            if (editAddressFormError) {
                                editAddressFormError.textContent = data.message || 'Error loading address details.';
                                editAddressFormError.style.display = 'block';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error loading address details:', error);
                        if (editAddressFormError) {
                            editAddressFormError.textContent = 'Error loading address details. Please try again.';
                            editAddressFormError.style.display = 'block';
                        }
                    });
            }
        }
    });

    // Update Address
    if (updateAddressBtn) {
        updateAddressBtn.addEventListener('click', function() {
            const form = document.getElementById('editAddressForm');

            // Validate form
            if (!form || !form.checkValidity()) {
                if (form) form.reportValidity();
                return;
            }

            // Show loading state
            updateAddressBtn.disabled = true;
            updateAddressBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Updating...';

            // Get form data
            const formData = new FormData(form);

            // Send data to server
            fetch('update_address.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide modal
                    const modal = bootstrap.Modal.getInstance(editAddressModal);
                    modal.hide();

                    // Show success notification
                    showNotification('Address updated successfully', 'success');

                    // Reload addresses
                    loadAddresses();
                } else {
                    // Show error message
                    const errorElement = document.getElementById('edit-address-form-error');
                    if (errorElement) {
                        errorElement.textContent = data.message || 'Error updating address. Please try again.';
                        errorElement.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Error updating address:', error);
                const errorElement = document.getElementById('edit-address-form-error');
                if (errorElement) {
                    errorElement.textContent = 'Error updating address. Please try again.';
                    errorElement.style.display = 'block';
                }
            })
            .finally(() => {
                // Reset button state
                updateAddressBtn.disabled = false;
                updateAddressBtn.innerHTML = 'Update Address';
            });
        });
    }

    // Delete Address
    const deleteAddressModal = document.getElementById('deleteAddressModal');
    const confirmDeleteAddressBtn = document.getElementById('confirmDeleteAddressBtn');

    // Handle delete address button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-address') || e.target.closest('.delete-address')) {
            e.preventDefault();

            const link = e.target.classList.contains('delete-address') ? e.target : e.target.closest('.delete-address');
            const addressId = link.getAttribute('data-address-id');

            if (addressId && deleteAddressModal) {
                // Set the address ID in the hidden field
                const deleteAddressIdField = document.getElementById('delete_address_id');
                if (deleteAddressIdField) {
                    deleteAddressIdField.value = addressId;
                }

                // Show modal
                const modal = new bootstrap.Modal(deleteAddressModal);
                modal.show();
            }
        }
    });

    // Confirm Delete Address
    if (confirmDeleteAddressBtn) {
        confirmDeleteAddressBtn.addEventListener('click', function() {
            const addressId = document.getElementById('delete_address_id').value;

            if (!addressId) return;

            // Show loading state
            confirmDeleteAddressBtn.disabled = true;
            confirmDeleteAddressBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Deleting...';

            // Create form data
            const formData = new FormData();
            formData.append('address_id', addressId);

            // Send data to server
            fetch('delete_address.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide modal
                    const modal = bootstrap.Modal.getInstance(deleteAddressModal);
                    modal.hide();

                    // Show success notification
                    showNotification('Address deleted successfully', 'success');

                    // Reload addresses
                    loadAddresses();
                } else {
                    // Show error notification
                    showNotification(data.message || 'Error deleting address', 'danger');
                }
            })
            .catch(error => {
                console.error('Error deleting address:', error);
                showNotification('Error deleting address. Please try again.', 'danger');
            })
            .finally(() => {
                // Reset button state
                confirmDeleteAddressBtn.disabled = false;
                confirmDeleteAddressBtn.innerHTML = 'Delete Address';
            });
        });
    }

    // Set Default Address
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('set-default-address') || e.target.closest('.set-default-address')) {
            e.preventDefault();

            const link = e.target.classList.contains('set-default-address') ? e.target : e.target.closest('.set-default-address');
            const addressId = link.getAttribute('data-address-id');

            if (!addressId) return;

            // Create form data
            const formData = new FormData();
            formData.append('address_id', addressId);

            // Show loading notification
            showNotification('Setting as default address...', 'info');

            // Send data to server
            fetch('set_default_address.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success notification
                    showNotification('Default address updated successfully', 'success');

                    // Reload addresses
                    loadAddresses();
                } else {
                    // Show error notification
                    showNotification(data.message || 'Error setting default address', 'danger');
                }
            })
            .catch(error => {
                console.error('Error setting default address:', error);
                showNotification('Error setting default address. Please try again.', 'danger');
            });
        }
    });

    // View Order Details
    const orderDetailsModal = document.getElementById('orderDetailsModal');
    const orderDetailsContent = document.getElementById('order-details-content');
    const orderDetailsLoading = document.getElementById('order-details-loading');
    const orderDetailsError = document.getElementById('order-details-error');

    // Add event listener to view order buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-order-btn') || e.target.closest('.view-order-btn')) {
            const button = e.target.classList.contains('view-order-btn') ? e.target : e.target.closest('.view-order-btn');
            const orderId = button.getAttribute('data-order-id');

            if (orderId) {
                // Show modal
                const modal = new bootstrap.Modal(orderDetailsModal);
                modal.show();

                // Show loading state
                orderDetailsContent.style.display = 'none';
                orderDetailsLoading.style.display = 'block';
                orderDetailsError.style.display = 'none';

                // Fetch order details
                fetch(`get_order_details.php?id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Populate order details
                            document.getElementById('order-id').textContent = '#' + data.order.id;
                            document.getElementById('order-date').textContent = new Date(data.order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

                            // Set status with badge
                            const statusBadgeClass =
                                data.order.status === 'pending' ? 'bg-warning' :
                                data.order.status === 'processing' ? 'bg-info' :
                                data.order.status === 'shipped' ? 'bg-primary' :
                                data.order.status === 'delivered' ? 'bg-success' : 'bg-danger';

                            document.getElementById('order-status').innerHTML = `<span class="badge ${statusBadgeClass}">${data.order.status.charAt(0).toUpperCase() + data.order.status.slice(1)}</span>`;

                            // Payment method
                            document.getElementById('order-payment').textContent = data.order.payment_method.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                            // Shipping address
                            const addressContainer = document.getElementById('order-address');
                            if (data.shipping_address) {
                                addressContainer.innerHTML = `
                                    <p class="mb-1"><strong>${data.shipping_address.full_name}</strong></p>
                                    <p class="mb-1">${data.shipping_address.address_line1}</p>
                                    ${data.shipping_address.address_line2 ? `<p class="mb-1">${data.shipping_address.address_line2}</p>` : ''}
                                    <p class="mb-1">${data.shipping_address.city}, ${data.shipping_address.postal_code}</p>
                                    <p class="mb-0"><i class="bi bi-telephone me-2"></i>${data.shipping_address.phone}</p>
                                `;
                            } else {
                                addressContainer.innerHTML = '<p class="text-muted">No shipping address available</p>';
                            }

                            // Order items
                            const itemsContainer = document.getElementById('order-items');
                            let itemsHTML = '';
                            let subtotal = 0;

                            data.items.forEach(item => {
                                const itemTotal = item.price * item.quantity;
                                subtotal += itemTotal;

                                itemsHTML += `
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="${item.image}" alt="${item.name}" class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0">${item.name}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>₱${parseFloat(item.price).toFixed(2)}</td>
                                        <td>${item.quantity}</td>
                                        <td>${item.size || 'N/A'}</td>
                                        <td class="text-end">₱${itemTotal.toFixed(2)}</td>
                                    </tr>
                                `;
                            });

                            itemsContainer.innerHTML = itemsHTML;

                            // Order totals
                            const shipping = 100; // Example shipping cost
                            const total = subtotal + shipping;

                            document.getElementById('order-subtotal').textContent = `₱${subtotal.toFixed(2)}`;
                            document.getElementById('order-shipping').textContent = `₱${shipping.toFixed(2)}`;
                            document.getElementById('order-total').textContent = `₱${total.toFixed(2)}`;

                            // Show content
                            orderDetailsLoading.style.display = 'none';
                            orderDetailsContent.style.display = 'block';
                        } else {
                            // Show error
                            orderDetailsLoading.style.display = 'none';
                            orderDetailsError.textContent = data.message || 'Error loading order details. Please try again.';
                            orderDetailsError.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading order details:', error);
                        orderDetailsLoading.style.display = 'none';
                        orderDetailsError.textContent = 'Error loading order details. Please try again.';
                        orderDetailsError.style.display = 'block';
                    });
            }
        }
    });

    // Show notification
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} notification`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '1050';
        notification.style.minWidth = '300px';
        notification.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
        notification.style.animation = 'fadeIn 0.3s ease-out forwards';

        // Create icon based on notification type
        let icon = 'info-circle';
        if (type === 'success') icon = 'check-circle';
        if (type === 'danger') icon = 'exclamation-circle';
        if (type === 'warning') icon = 'exclamation-triangle';

        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-${icon} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Add to document
        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Add keyframe animation for fadeIn
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    `;
    document.head.appendChild(style);

    // Add CSS for address cards
    const addressStyle = document.createElement('style');
    addressStyle.textContent = `
        .address-card {
            transition: all 0.3s ease;
            height: 100%;
        }

        .address-card:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        .address-card.default {
            border-color: var(--primary-color);
        }

        .address-card .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .address-card.default .card-header {
            background-color: rgba(108, 74, 55, 0.1);
        }
    `;
    document.head.appendChild(addressStyle);
});
