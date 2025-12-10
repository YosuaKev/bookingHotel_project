// theme.js - Enhanced with modern interactions
document.addEventListener('DOMContentLoaded', function() {
    // Room Search Functionality
    const roomSearch = document.getElementById('room_search');
    if (roomSearch) {
        roomSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const roomCards = document.querySelectorAll('.room');
            
            roomCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                    card.classList.add('fade-in');
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Enhanced Booking Form
    const bookingForm = document.getElementById('book_now_form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const arrival = this.querySelector('input[name="arrival"]').value;
            const departure = this.querySelector('input[name="departure"]').value;
            
            if (!arrival || !departure) {
                showNotification('Please select both arrival and departure dates', 'error');
                return;
            }
            
            if (new Date(arrival) >= new Date(departure)) {
                showNotification('Departure date must be after arrival date', 'error');
                return;
            }
            
            // Redirect to booking page with dates
            window.location.href = `booking.html?arrival=${arrival}&departure=${departure}`;
        });
    }
    
    // Contact Form Enhancement
    const contactForm = document.getElementById('request');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simple validation
            const inputs = this.querySelectorAll('.contactus, .textarea');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = 'var(--accent)';
                    isValid = false;
                } else {
                    input.style.borderColor = 'var(--secondary)';
                }
            });
            
            if (isValid) {
                showNotification('Message sent successfully! We\'ll get back to you soon.', 'success');
                this.reset();
            } else {
                showNotification('Please fill in all required fields', 'error');
            }
        });
    }
    
    // Image Gallery Modal Enhancement
    const galleryImages = document.querySelectorAll('.gallery_img img');
    galleryImages.forEach(img => {
        img.addEventListener('click', function() {
            const modalHTML = `
                <div class="modal-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:2000;display:flex;align-items:center;justify-content:center;">
                    <div style="max-width:90%;max-height:90%;">
                        <img src="${this.src}" style="max-width:100%;max-height:100%;border-radius:8px;">
                        <button class="close-modal" style="position:absolute;top:20px;right:20px;background:#fff;border:none;width:40px;height:40px;border-radius:50%;font-size:20px;cursor:pointer;">×</button>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            const modal = document.querySelector('.modal-overlay');
            const closeBtn = document.querySelector('.close-modal');
            
            closeBtn.addEventListener('click', () => modal.remove());
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });
        });
    });
    
    // Smooth Scroll for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Notification System
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            z-index: 3000;
            animation: slideIn 0.3s ease;
            font-weight: 500;
        `;
        
        if (type === 'success') {
            notification.style.backgroundColor = 'var(--success-color)';
        } else if (type === 'error') {
            notification.style.backgroundColor = 'var(--accent)';
        } else {
            notification.style.backgroundColor = 'var(--secondary)';
        }
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Lazy Loading Images
    const images = document.querySelectorAll('img');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src || img.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => {
        if (img.dataset.src) {
            imageObserver.observe(img);
        }
    });
    
    // Sticky Header
    const header = document.querySelector('header');
    let lastScroll = 0;
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            header.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
            header.style.background = 'rgba(255,255,255,0.95)';
            header.style.backdropFilter = 'blur(10px)';
        } else {
            header.style.boxShadow = 'none';
            header.style.background = 'var(--white)';
            header.style.backdropFilter = 'none';
        }
        
        lastScroll = currentScroll;
    });
});