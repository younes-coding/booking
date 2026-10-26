// ============================================
// BOOKING FORM - DYNAMIC CALCULATIONS
// ============================================

class ProductBooking {
    constructor() {
        // DOM Elements
        this.form = document.getElementById('bookingForm');
        this.quantityInput = document.getElementById('quantity');
        this.increaseBtn = document.getElementById('increaseQty');
        this.decreaseBtn = document.getElementById('decreaseQty');
        this.startDateInput = document.getElementById('startDate');
        this.endDateInput = document.getElementById('endDate');
        this.totalPriceDisplay = document.getElementById('totalPrice');
        this.priceNoteDisplay = document.getElementById('priceNote');
        this.productPrice = parseFloat(document.getElementById('productPrice').textContent);
        
        // Initialize
        this.init();
    }

    init() {
        // Set minimum date to today
        this.setMinimumDate();
        
        // Event Listeners
        this.increaseBtn.addEventListener('click', () => this.changeQuantity(1));
        this.decreaseBtn.addEventListener('click', () => this.changeQuantity(-1));
        this.quantityInput.addEventListener('input', (e) => this.handleQuantityInput(e));
        this.quantityInput.addEventListener('change', () => this.updateTotal());
        this.startDateInput.addEventListener('change', () => this.updateTotal());
        this.endDateInput.addEventListener('change', () => this.updateTotal());
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Initial calculation
        this.updateTotal();
    }

    /**
     * Set minimum date to today
     */
    setMinimumDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const minDate = `${year}-${month}-${day}`;
        
        this.startDateInput.min = minDate;
        this.endDateInput.min = minDate;
    }

    /**
     * Change quantity with increment/decrement buttons
     */
    changeQuantity(amount) {
        const currentQty = parseInt(this.quantityInput.value) || 1;
        const minQty = parseInt(this.quantityInput.min) || 1;
        const maxQty = parseInt(this.quantityInput.max) || 1000;
        
        let newQty = currentQty + amount;
        newQty = Math.max(minQty, Math.min(maxQty, newQty));
        
        this.quantityInput.value = newQty;
        this.updateTotal();
    }

    /**
     * Handle manual quantity input
     */
    handleQuantityInput(event) {
        let value = parseInt(event.target.value) || 1;
        const minQty = parseInt(this.quantityInput.min) || 1;
        const maxQty = parseInt(this.quantityInput.max) || 1000;
        
        // Allow input but we'll validate on blur/change
        value = Math.max(minQty, Math.min(maxQty, value));
        this.quantityInput.value = value;
    }

    /**
     * Calculate number of days between two dates
     */
    getDaysCount() {
        const startDateStr = this.startDateInput.value;
        const endDateStr = this.endDateInput.value;
        
        // If both dates are not set, return 1 day
        if (!startDateStr || !endDateStr) {
            return 1;
        }
        
        const startDate = new Date(startDateStr);
        const endDate = new Date(endDateStr);
        
        // If end date is before start date, return 1 day
        if (endDate < startDate) {
            return 1;
        }
        
        // Calculate days (inclusive of start and end date)
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        return diffDays;
    }

    /**
     * Update total price based on quantity and days
     */
    updateTotal() {
        const quantity = parseInt(this.quantityInput.value) || 1;
        const days = this.getDaysCount();
        const total = this.productPrice * quantity * days;
        
        // Update display
        this.totalPriceDisplay.textContent = total.toFixed(2);
        
        // Update price note
        const unitPrice = this.productPrice;
        this.priceNoteDisplay.textContent = 
            `${quantity} × ${unitPrice.toFixed(2)} × ${days} يوم = ${total.toFixed(2)} ريال`;
        
        // Store calculated value in hidden field (optional, for backend)
        const totalInput = document.querySelector('input[name="total_price"]');
        if (totalInput) {
            totalInput.value = total.toFixed(2);
        }
    }

    /**
     * Handle form submission
     */
    handleSubmit(event) {
        event.preventDefault();
        
        // Validate dates
        if (!this.validateDates()) {
            return;
        }
        
        // Validate quantity
        if (!this.validateQuantity()) {
            return;
        }
        
        // Prepare form data
        const formData = new FormData(this.form);
        const bookingData = {
            product_id: formData.get('product_id'),
            product_name: formData.get('product_name'),
            start_date: formData.get('start_date'),
            end_date: formData.get('end_date'),
            quantity: parseInt(formData.get('quantity')),
            unit_price: parseFloat(formData.get('unit_price')),
            total_price: parseFloat(this.totalPriceDisplay.textContent),
            days: this.getDaysCount()
        };
        
        // Log data (for debugging)
        console.log('Booking Data:', bookingData);
        
        // Send to server (uncomment when backend is ready)
        this.submitToServer(bookingData);
        
        // Show success message
        this.showSuccessMessage();
    }

    /**
     * Validate date selection
     */
    validateDates() {
        const startDateStr = this.startDateInput.value;
        const endDateStr = this.endDateInput.value;
        
        // Check if both dates are selected
        if (!startDateStr) {
            this.showErrorMessage('يرجى اختيار تاريخ البداية');
            return false;
        }
        
        if (!endDateStr) {
            this.showErrorMessage('يرجى اختيار تاريخ النهاية');
            return false;
        }
        
        const startDate = new Date(startDateStr);
        const endDate = new Date(endDateStr);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Check if dates are in the future
        if (startDate < today) {
            this.showErrorMessage('يجب اختيار تاريخ في المستقبل');
            return false;
        }
        
        // Check if end date is after start date
        if (endDate < startDate) {
            this.showErrorMessage('تاريخ النهاية يجب أن يكون بعد تاريخ البداية');
            return false;
        }
        
        return true;
    }

    /**
     * Validate quantity
     */
    validateQuantity() {
        const quantity = parseInt(this.quantityInput.value) || 0;
        const minQty = parseInt(this.quantityInput.min) || 1;
        const maxQty = parseInt(this.quantityInput.max) || 1000;
        
        if (quantity < minQty || quantity > maxQty) {
            this.showErrorMessage(`الكمية يجب أن تكون بين ${minQty} و ${maxQty}`);
            return false;
        }
        
        return true;
    }

    /**
     * Submit booking to server
     */
    submitToServer(bookingData) {
        // Prepare POST data
        const formData = new URLSearchParams();
        formData.append('product_id', bookingData.product_id);
        formData.append('product_name', bookingData.product_name);
        formData.append('start_date', bookingData.start_date);
        formData.append('end_date', bookingData.end_date);
        formData.append('quantity', bookingData.quantity);
        formData.append('unit_price', bookingData.unit_price);
        formData.append('total_price', bookingData.total_price);
        formData.append('days', bookingData.days);
        
        // Uncomment the code below when your backend is ready
        /*
        fetch(this.form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showSuccessMessage('تم حفظ الحجز بنجاح! سيتم التواصل معك قريباً.');
                // Reset form
                this.form.reset();
                this.updateTotal();
            } else {
                this.showErrorMessage(data.message || 'حدث خطأ أثناء حفظ الحجز');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showErrorMessage('حدث خطأ في الاتصال بالخادم');
        });
        */
    }

    /**
     * Show success message
     */
    showSuccessMessage(message = 'شكراً! تم استقبال طلب الحجز الخاص بك. سيتم التواصل معك قريباً.') {
        alert(message);
        // Optional: Clear the booking form or redirect
        // this.form.reset();
        // this.updateTotal();
    }

    /**
     * Show error message
     */
    showErrorMessage(message) {
        alert('⚠️ ' + message);
    }
}

// ============================================
// PRODUCT PAGE UTILITIES
// ============================================

class ProductPage {
    constructor() {
        this.init();
    }

    init() {
        // Smooth scroll for anchor links
        this.setupSmoothScroll();
        
        // Add active state to navigation
        this.setupNavigation();
        
        // Image lazy loading (if needed)
        this.setupImageLazyLoad();
    }

    /**
     * Setup smooth scroll behavior
     */
    setupSmoothScroll() {
        const links = document.querySelectorAll('a[href^="#"]');
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    }

    /**
     * Setup navigation active state
     */
    setupNavigation() {
        const currentPage = window.location.pathname;
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    }

    /**
     * Setup image lazy loading
     */
    setupImageLazyLoad() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }
}

// ============================================
// INITIALIZE ON DOM READY
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Initialize booking form
    new ProductBooking();
    
    // Initialize page utilities
    new ProductPage();
    
    console.log('✓ Product page initialized successfully');
});

// ============================================
// UTILITY FUNCTIONS FOR BACKEND INTEGRATION
// ============================================

/**
 * Format currency (optional utility function)
 * Usage: formatCurrency(100.5, 'USD')
 */
function formatCurrency(amount, currency = 'USD') {
    const formatter = new Intl.NumberFormat('ar-DZ', {
        style: 'currency',
        currency: currency
    });
    return formatter.format(amount);
}

/**
 * Format date in Arabic
 * Usage: formatDateArabic('2025-04-15')
 */
function formatDateArabic(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        timeZone: 'Africa/Algiers'
    };
    return date.toLocaleDateString('ar-DZ', options);
}

/**
 * Get booking summary as string (for preview/confirmation)
 */
function getBookingSummary() {
    const booking = {
        product: document.getElementById('productName').value,
        startDate: document.getElementById('startDate').value,
        endDate: document.getElementById('endDate').value,
        quantity: document.getElementById('quantity').value,
        totalPrice: document.getElementById('totalPrice').textContent
    };
    
    return `
المنتج: ${booking.product}
تاريخ البداية: ${formatDateArabic(booking.startDate)}
تاريخ النهاية: ${formatDateArabic(booking.endDate)}
الكمية: ${booking.quantity}
الإجمالي: ${booking.totalPrice}
    `.trim();
}