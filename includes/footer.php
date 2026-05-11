<footer class="footer" id="contact">

        <!-- ── Top Statement Bar ─────────────────────────────── -->
        <div class="footer-top">
            <div class="container footer-top-inner">
                <div class="footer-tagline">
                    <span>Ride with</span>
                    <strong>confidence.</strong>
                </div>
                <a href="<?php echo isset($base_path) ? $base_path : ''; ?>book.php" class="btn btn-accent footer-cta">
                    <i class="fas fa-taxi"></i> Book a Ride Now
                </a>
            </div>
        </div>

        <!-- ── Main Grid ─────────────────────────────────────── -->
        <div class="footer-body">
            <div class="container footer-grid">

                <!-- Brand + Contact -->
                <div class="footer-brand-col">
                    <a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php" class="footer-logo">
                        <i class="fas fa-taxi"></i> RUS <span>CAB</span>
                    </a>
                    <p>Professional cab services across Surat. Trusted by thousands of riders every day for safe, punctual, and comfortable journeys.</p>

                    <div class="footer-contact-list">
                        <a href="tel:+919876543210" class="footer-contact-item">
                            <div class="footer-contact-icon"><i class="fas fa-phone"></i></div>
                            <span>+91 98765 43210</span>
                        </a>
                        <a href="mailto:support@ruscab.com" class="footer-contact-item">
                            <div class="footer-contact-icon"><i class="fas fa-envelope"></i></div>
                            <span>support@ruscab.com</span>
                        </a>
                        <div class="footer-contact-item">
                            <div class="footer-contact-icon"><i class="fas fa-location-dot"></i></div>
                            <span>Surat, Gujarat, India</span>
                        </div>
                    </div>
                </div>

                <!-- Services -->
                <div class="footer-nav-col">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>book.php">Book a Ride</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php#fleet">Economy Sedan</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php#fleet">Family SUV</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php#fleet">Luxury Elite</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php#features">Airport Transfer</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div class="footer-nav-col">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php#about">About Us</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php#features">Why Choose Us</a></li>
                        <li><a href="../driver/register.php">Drive With Us</a></li>
                        <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>support.php">Support</a></li>
                    </ul>
                </div>

                <!-- Account (dynamic based on session) -->
                <div class="footer-nav-col">
                    <h4>Account</h4>
                    <ul>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>dashboard.php">My Dashboard</a></li>
                            <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>profile.php">My Profile</a></li>
                            <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>logout.php">Sign Out</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>login.php">Sign In</a></li>
                            <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>register.php">Create Account</a></li>
                            <li><a href="<?php echo isset($base_path) ? $base_path : ''; ?>forgot_password.php">Reset Password</a></li>
                        <?php endif; ?>
                        <li><a href="../driver/index.php">Driver Portal</a></li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- ── Bottom Bar ─────────────────────────────────────── -->
        <div class="footer-bottom">
            <div class="container footer-bottom-inner">
                <p>&copy; <?php echo date('Y'); ?> RUS CAB. All rights reserved. Designed by <strong>Roshan</strong>.</p>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>

    </footer>

    <script src="<?php echo isset($assets_path) ? $assets_path : 'assets'; ?>/js/main.js"></script>
</body>
</html>