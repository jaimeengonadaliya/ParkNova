    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-glow"></div>
        <div class="container position-relative" style="z-index: 1;">
            <div class="row g-4">
                <!-- Column 1: Brand -->
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <div class="footer-brand mb-3">
                        <h3 class="fw-bold d-flex align-items-center gap-2">
                            <i class="fa-solid fa-square-parking"></i>
                            <span class="text-gradient">ParkNova</span>
                        </h3>
                    </div>
                    <p class="text-secondary mb-4 pe-lg-5">
                        Experience the future of urban mobility. Find and reserve the perfect parking spot in seconds with our smart tracking technology.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-icon" title="X (Twitter)"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" class="social-icon" title="Instagram"><i class="fa-brands fa-square-instagram"></i></a>
                        <a href="#" class="social-icon" title="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
                        <a href="#" class="social-icon" title="Facebook"><i class="fa-brands fa-square-facebook"></i></a>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="footer-column-title">Company</h5>
                    <ul class="footer-links-list">
                        <li><a href="<?= $base_url ?>/index.php" class="footer-link">Home</a></li>
                        <li><a href="<?= $base_url ?>/about.php" class="footer-link">About Us</a></li>
                        <li><a href="<?= $base_url ?>/services.php" class="footer-link">Services</a></li>
                        <li><a href="<?= $base_url ?>/how_it_works.php" class="footer-link">How it Works</a></li>
                    </ul>
                </div>

                <!-- Column 3: Support -->
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="footer-column-title">Support</h5>
                    <ul class="footer-links-list">
                        <li><a href="<?= $base_url ?>/support.php" class="footer-link">Help Center</a></li>
                        <li><a href="<?= $base_url ?>/contact.php" class="footer-link">Contact Us</a></li>
                        <li><a href="<?= $base_url ?>/privacy.php" class="footer-link">Privacy Policy</a></li>
                        <li><a href="<?= $base_url ?>/terms.php" class="footer-link">Terms of Service</a></li>
                    </ul>
                </div>

                <!-- Column 4: Stay Connected -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-column-title">Stay Connected</h5>
                    <p class="small text-secondary mb-4">Get the latest updates on slot availability and new parking zones directly in your inbox.</p>
                    <div class="newsletter-box">
                        <input type="email" class="newsletter-input" placeholder="Your Email">
                        <button class="newsletter-btn" type="button">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                    <div class="mt-4 d-flex align-items-center gap-3 text-secondary">
                        <i class="fa-solid fa-envelope text-primary"></i>
                        <a href="mailto:jaimeengondaliya@gmail.com" class="text-secondary text-decoration-none small fw-medium transition-all hover-primary">jaimeengondaliya@gmail.com</a>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="text-center text-md-start">
                        <p class="mb-0 text-secondary small fw-medium">
                            &copy; <?= date('Y') ?> ParkNova – Smart Parking Management System | Designed & Developed by Jaimeen Gondaliya
                        </p>
                    </div>
                    <div class="d-flex gap-4">
                        <a href="#" class="text-secondary text-decoration-none small hover-primary transition">Sitemap</a>
                        <a href="#" class="text-secondary text-decoration-none small hover-primary transition">Cookies</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= $base_url ?>/js_main.js"></script>
</body>
</html>



