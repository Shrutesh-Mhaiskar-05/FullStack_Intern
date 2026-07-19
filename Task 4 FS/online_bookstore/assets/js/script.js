/**
 * Online Bookstore - Custom JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {

    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.alert-dismissible');
    flashMessages.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Quantity input validation in cart
    document.querySelectorAll('input[type="number"]').forEach(function(input) {
        input.addEventListener('change', function() {
            const min = parseInt(this.getAttribute('min')) || 1;
            const max = parseInt(this.getAttribute('max')) || 999;
            let val = parseInt(this.value);
            if (isNaN(val) || val < min) this.value = min;
            if (val > max) this.value = max;
        });
    });

    // Price format helper (display only, no conversion)
    document.querySelectorAll('.price-format').forEach(function(el) {
        const price = parseFloat(el.dataset.price);
        if (!isNaN(price)) {
            el.textContent = 'Rp ' + price.toLocaleString('id-ID');
        }
    });

    // Search form - trim whitespace
    const searchForm = document.querySelector('form[action="shop.php"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.value = searchInput.value.trim();
            }
        });
    }

    // Confirm delete helper (for non-modal deletes)
    window.confirmDelete = function(item) {
        return confirm('Are you sure you want to delete this ' + item + '? This action cannot be undone.');
    };

    // Tooltip initialization
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) {
        return new bootstrap.Tooltip(el);
    });

});
