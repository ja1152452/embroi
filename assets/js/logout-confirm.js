// Logout confirmation functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get the logout button
    const logoutBtn = document.getElementById('logoutBtn');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Create and show the confirmation modal
            showLogoutConfirmation();
        });
    }
});

// Show logout confirmation modal
function showLogoutConfirmation() {
    // Check if modal already exists
    if (document.getElementById('logoutConfirmModal')) {
        // Show existing modal
        const modal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
        modal.show();
        return;
    }

    // Create modal HTML
    const modalHTML = `
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutConfirmModalLabel">Logout Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to log out?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="${window.location.pathname.includes('/admin/') ? '../logout.php?confirm=yes' : 'logout.php?confirm=yes'}" class="btn btn-danger">Yes, Log Out</a>
                </div>
            </div>
        </div>
    </div>
    `;

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
    modal.show();
}
