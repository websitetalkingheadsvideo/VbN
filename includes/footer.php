    </main> <!-- End main-wrapper from header.php -->
    
    <footer class="valley-footer">
        <div class="footer-container container d-flex flex-column align-items-center gap-3">
            <div class="footer-content">
                <p class="footer-title">
                    <?php
                    // Use path prefix determined in header.php
                    $current_dir = dirname($_SERVER['PHP_SELF']);
                    $in_subfolder = ($current_dir !== '/' && $current_dir !== '' && basename($current_dir) !== basename($_SERVER['DOCUMENT_ROOT']));
                    $path_prefix = $in_subfolder ? '../' : '';
                    ?>
                    <a href="<?php echo $path_prefix; ?>index.php">Valley by Night</a>
                </p>
                <p class="footer-tagline">A Vampire Tale</p>
            </div>
            <div class="footer-bottom">
                <p class="copyright">
                    &copy; <?php echo date('Y'); ?> Valley by Night. All Rights Reserved.
                </p>
                <p class="disclaimer">
                    Based on Vampire: The Masquerade &copy; White Wolf Publishing
                </p>
            </div>
        </div>
    </footer>
</div> <!-- End page-wrapper -->
<!-- Bootstrap 5.3.2 JS Bundle - Load before closing body tag -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous" defer></script>
</body>
</html>

