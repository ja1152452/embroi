// Product Gallery functionality
class ProductGallery {
    constructor() {
        this.mainImage = document.querySelector('.gallery-main img');
        this.thumbnails = document.querySelectorAll('.gallery-thumb');
        this.currentIndex = 0;
        this.init();
    }

    init() {
        if (!this.mainImage || !this.thumbnails.length) return;

        // Add click event to thumbnails
        this.thumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', () => {
                this.showImage(index);
            });
        });

        // Add keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                this.showPrevious();
            } else if (e.key === 'ArrowRight') {
                this.showNext();
            }
        });

        // Add touch swipe support
        let touchStartX = 0;
        let touchEndX = 0;

        this.mainImage.parentElement.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });

        this.mainImage.parentElement.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe(touchStartX, touchEndX);
        });

        // Show first image
        this.showImage(0);
    }

    showImage(index) {
        if (index < 0) index = this.thumbnails.length - 1;
        if (index >= this.thumbnails.length) index = 0;

        this.currentIndex = index;
        const newSrc = this.thumbnails[index].querySelector('img').src;
        
        // Fade out current image
        this.mainImage.style.opacity = '0';
        
        // Update image source and fade in
        setTimeout(() => {
            this.mainImage.src = newSrc;
            this.mainImage.style.opacity = '1';
        }, 300);

        // Update active thumbnail
        this.thumbnails.forEach((thumb, i) => {
            thumb.classList.toggle('active', i === index);
        });
    }

    showPrevious() {
        this.showImage(this.currentIndex - 1);
    }

    showNext() {
        this.showImage(this.currentIndex + 1);
    }

    handleSwipe(startX, endX) {
        const swipeThreshold = 50;
        const diff = startX - endX;

        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                this.showNext();
            } else {
                this.showPrevious();
            }
        }
    }
}

// Initialize gallery
document.addEventListener('DOMContentLoaded', () => {
    const gallery = new ProductGallery();
}); 