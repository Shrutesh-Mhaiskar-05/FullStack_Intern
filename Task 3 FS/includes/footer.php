
<?php if (!empty($authLayout)): ?>
            </div>
        </div>
    </div>
<?php else: ?>
        </main>
    </div>
<?php endif; ?>

    <div id="toastContainer" class="toast-container"></div>
    <div id="loadingSpinner" class="spinner-overlay">
        <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $rootPath ?>assets/js/app.js"></script>
</body>
</html>
