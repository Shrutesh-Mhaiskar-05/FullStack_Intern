    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5><i class="bi bi-book-fill me-2"></i>BookStore</h5>
                    <p class="text-muted small">Your one-stop online bookstore. Discover, read, and enjoy books from every genre.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="shop.php" class="text-muted text-decoration-none">Shop</a></li>
                        <li><a href="cart.php" class="text-muted text-decoration-none">Cart</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Contact</h5>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="bi bi-envelope me-2"></i>support@bookstore.com</li>
                        <li><i class="bi bi-telephone me-2"></i>+1 (555) 123-4567</li>
                        <li><i class="bi bi-geo-alt me-2"></i>123 Book St, Reading City</li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center text-muted small">
                &copy; <?php echo date('Y'); ?> Online Bookstore. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Toast Notification Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="cartToast" class="toast align-items-center text-white bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="cartToastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
    function showToast(message, type) {
        const toast = document.getElementById('cartToast');
        const msgEl = document.getElementById('cartToastMessage');
        msgEl.textContent = message;
        toast.className = 'toast align-items-center text-white border-0 show';
        if (type === 'error') toast.className = 'toast align-items-center text-white bg-danger border-0 show';
        if (type === 'warning') toast.className = 'toast align-items-center text-white bg-warning border-0 show';
        setTimeout(() => { toast.className = toast.className.replace('show', ''); }, 3000);
    }

    function updateCartBadge(count) {
        const cartLink = document.querySelector('.navbar a[href="cart.php"]');
        if (!cartLink) return;
        let badge = cartLink.querySelector('.badge');
        if (count > 0) {
            if (badge) { badge.textContent = count; }
            else {
                badge = document.createElement('span');
                badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                badge.textContent = count;
                cartLink.style.position = 'relative';
                cartLink.appendChild(badge);
            }
        } else { if (badge) badge.remove(); }
    }
    </script>
</body>
</html>
