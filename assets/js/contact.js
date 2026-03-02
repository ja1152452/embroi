// Contact Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Animate elements when they come into view
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.contact-card, .contact-wrapper, .cta-container');

        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;

            if (elementPosition < windowHeight - 100) {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }
        });
    };

    // Initial check for elements in view
    animateOnScroll();

    // Check on scroll
    window.addEventListener('scroll', animateOnScroll);

    // Form input animation
    const formInputs = document.querySelectorAll('.contact-form .form-control');

    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('input-focused');
        });

        input.addEventListener('blur', function() {
            if (this.value === '') {
                this.parentElement.classList.remove('input-focused');
            }
        });
    });

    // Form submission animation
    const contactForm = document.querySelector('.contact-form');
    const submitBtn = document.querySelector('.contact-submit-btn');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending...';
            submitBtn.disabled = true;
            // Form will submit normally
        });
    }

    // Map interaction
    const mapContainer = document.querySelector('.map-container');
    const mapOverlay = document.querySelector('.map-overlay');

    if (mapContainer && mapOverlay) {
        // Mouse hover effect
        mapContainer.addEventListener('mouseenter', function() {
            mapOverlay.style.opacity = '0.7';
        });

        mapContainer.addEventListener('mouseleave', function() {
            mapOverlay.style.opacity = '1';
        });

        // Click to fully hide overlay
        mapOverlay.addEventListener('click', function() {
            this.style.opacity = '0';
            this.style.pointerEvents = 'none';

            // Add a reset button
            const resetBtn = document.createElement('button');
            resetBtn.className = 'btn btn-sm btn-light map-reset-btn';
            resetBtn.innerHTML = '<i class="bi bi-arrow-left me-1"></i> Back to contact';
            resetBtn.style.position = 'absolute';
            resetBtn.style.top = '10px';
            resetBtn.style.right = '10px';
            resetBtn.style.zIndex = '2';
            mapContainer.appendChild(resetBtn);

            resetBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                mapOverlay.style.opacity = '1';
                mapOverlay.style.pointerEvents = 'auto';
                this.remove();
            });
        });
    }

    // Social icons hover effect
    const socialIcons = document.querySelectorAll('.social-icon');

    socialIcons.forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });

        icon.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
