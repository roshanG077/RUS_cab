<?php
$page_title = "RUS CAB — Premium City Rides in Surat";
include '../includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════════
     HERO SECTION
     Split layout: left text + booking card | right full-bleed image
═══════════════════════════════════════════════════════════════ -->
<section class="hero-v2" id="home">

    <!-- Right: Full-bleed car image panel -->
    <div class="hero-img-panel">
        <img
            src="https://images.unsplash.com/photo-1485291571150-772bcfc10da5?auto=format&fit=crop&w=1400&q=85"
            alt="Premium cab on the road"
            class="hero-main-img">
        <div class="hero-img-overlay"></div>

        <!-- Floating stats badge on the image -->
        <div class="hero-badge-wrap">
            <div class="hero-badge">
                <div class="hero-badge-item">
                    <strong>4.9★</strong>
                    <span>Avg Rating</span>
                </div>
                <div class="hero-badge-sep"></div>
                <div class="hero-badge-item">
                    <strong>12K+</strong>
                    <span>Happy Riders</span>
                </div>
                <div class="hero-badge-sep"></div>
                <div class="hero-badge-item">
                    <strong>24/7</strong>
                    <span>Available</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Left: Content -->
    <div class="hero-content">
        <div class="hero-content-inner">

            <div class="hero-eyebrow">
                <i class="fas fa-location-dot"></i> Serving Surat, Gujarat
            </div>

            <h1 class="hero-headline">
                Your Ride,<br>
                <span>On Your Terms.</span>
            </h1>

            <p class="hero-sub">
                Professional cab service with verified drivers, real-time tracking,
                and zero surge pricing. Book in under 60 seconds.
            </p>

            <!-- Booking Card integrated naturally below headline -->
            <div class="booking-card">
                <div class="booking-card-tabs">
                    <button class="booking-tab active" data-tab="now">
                        <i class="fas fa-bolt"></i> Ride Now
                    </button>
                    <button class="booking-tab" data-tab="schedule">
                        <i class="fas fa-calendar"></i> Schedule
                    </button>
                </div>

                <form action="book.php" method="POST" class="booking-form">
                    <div class="booking-inputs">
                        <div class="booking-field">
                            <div class="booking-field-icon green">
                                <i class="fas fa-circle-dot"></i>
                            </div>
                            <input type="text" name="pickup" placeholder="Pickup location" required>
                        </div>
                        <div class="booking-route-line"></div>
                        <div class="booking-field">
                            <div class="booking-field-icon red">
                                <i class="fas fa-location-dot"></i>
                            </div>
                            <input type="text" name="dropoff" placeholder="Drop-off location" required>
                        </div>
                    </div>

                    <div class="booking-row-two" id="scheduleFields" style="display:none;">
                        <div class="booking-datetime">
                            <input type="date" name="date" min="<?= date('Y-m-d'); ?>">
                            <input type="time" name="time">
                        </div>
                    </div>

                    <div class="cab-type-select">
                        <label class="cab-type-option active" data-value="sedan">
                            <input type="radio" name="car_type" value="sedan" checked>
                            <i class="fas fa-car"></i>
                            <span>Sedan</span>
                            <small>₹12/km</small>
                        </label>
                        <label class="cab-type-option" data-value="suv">
                            <input type="radio" name="car_type" value="suv">
                            <i class="fas fa-truck-pickup"></i>
                            <span>SUV</span>
                            <small>₹18/km</small>
                        </label>
                        <label class="cab-type-option" data-value="luxury">
                            <input type="radio" name="car_type" value="luxury">
                            <i class="fas fa-star"></i>
                            <span>Luxury</span>
                            <small>₹45/km</small>
                        </label>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="submit" class="btn btn-full">
                            Confirm Ride <i class="fas fa-arrow-right"></i>
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-full">
                            Sign In to Book <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Trust row -->
            <div class="hero-trust">
                <div class="trust-item">
                    <strong>12,000+</strong>
                    <span>Rides Completed</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-item">
                    <strong>98%</strong>
                    <span>On-Time Rate</span>
                </div>
                <div class="trust-divider"></div>
                <div class="trust-item">
                    <strong>500+</strong>
                    <span>Verified Drivers</span>
                </div>
            </div>

        </div>
    </div>

</section>

<!-- ══════════════════════════════════════════════════════════
     HOW IT WORKS — 3 step process
═══════════════════════════════════════════════════════════════ -->
<section class="how-section">
    <div class="container">
        <div class="section-title reveal">
            <h2>Booked in <span>3 Steps</span></h2>
            <p>No app download required. Just open, pick, and ride.</p>
        </div>

        <div class="how-grid">
            <div class="how-item reveal">
                <div class="how-number">01</div>
                <div class="how-icon"><i class="fas fa-map-pin"></i></div>
                <h4>Enter Your Route</h4>
                <p>Type your pickup and drop-off location. We cover all areas across Surat city.</p>
            </div>
            <div class="how-connector reveal"></div>
            <div class="how-item reveal">
                <div class="how-number">02</div>
                <div class="how-icon"><i class="fas fa-car-side"></i></div>
                <h4>Choose Your Cab</h4>
                <p>Pick from Sedan, SUV or Luxury — each with transparent per-km pricing.</p>
            </div>
            <div class="how-connector reveal"></div>
            <div class="how-item reveal">
                <div class="how-number">03</div>
                <div class="how-icon"><i class="fas fa-circle-check"></i></div>
                <h4>Confirm & Ride</h4>
                <p>Your driver is assigned instantly. Track in real-time and pay on arrival.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     FLEET SECTION
═══════════════════════════════════════════════════════════════ -->
<section class="fleet-grid-section" id="fleet">
    <div class="container">
        <div class="section-title reveal">
            <h2>Choose Your <span>Comfort</span></h2>
            <p>Three vehicle categories — each with verified drivers and full insurance.</p>
        </div>

        <div class="fleet-wrapper stagger">

            <!-- Economy Sedan -->
            <div class="cab-item reveal">
                <div class="cab-badge">Most Popular</div>
                <div class="cab-img-wrap">
                    <img
                        src="https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=700&q=80"
                        alt="Economy Sedan">
                    <div class="cab-img-tint"></div>
                    <div class="cab-price-overlay">₹12<span>/km</span></div>
                </div>
                <div class="cab-info">
                    <div class="cab-info-top">
                        <h4>Economy Sedan</h4>
                        <div class="cab-rating"><i class="fas fa-star"></i> 4.8</div>
                    </div>
                    <p>Ideal for daily commutes. Seats 4 comfortably with luggage space.</p>
                    <div class="cab-features">
                        <span><i class="fas fa-user-group"></i> 4 seats</span>
                        <span><i class="fas fa-snowflake"></i> AC</span>
                        <span><i class="fas fa-wifi"></i> Wi-Fi</span>
                    </div>
                    <a href="book.php?type=sedan" class="btn btn-outline btn-sm">Book Sedan</a>
                </div>
            </div>

            <!-- Family SUV -->
            <div class="cab-item reveal">
                <div class="cab-img-wrap">
                    <img
                        src="https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=700&q=80"
                        alt="Family SUV">
                    <div class="cab-img-tint"></div>
                    <div class="cab-price-overlay">₹18<span>/km</span></div>
                </div>
                <div class="cab-info">
                    <div class="cab-info-top">
                        <h4>Family SUV</h4>
                        <div class="cab-rating"><i class="fas fa-star"></i> 4.9</div>
                    </div>
                    <p>Spacious cabin for families or groups. Up to 6 passengers.</p>
                    <div class="cab-features">
                        <span><i class="fas fa-user-group"></i> 6 seats</span>
                        <span><i class="fas fa-snowflake"></i> AC</span>
                        <span><i class="fas fa-suitcase"></i> Large boot</span>
                    </div>
                    <a href="book.php?type=suv" class="btn btn-outline btn-sm">Book SUV</a>
                </div>
            </div>

            <!-- Luxury Elite -->
            <div class="cab-item reveal">
                <div class="cab-img-wrap">
                    <img
                        src="https://images.unsplash.com/photo-1563720223185-11003d516935?auto=format&fit=crop&w=700&q=80"
                        alt="Luxury Elite">
                    <div class="cab-img-tint"></div>
                    <div class="cab-price-overlay">₹45<span>/km</span></div>
                </div>
                <div class="cab-info">
                    <div class="cab-info-top">
                        <h4>Luxury Elite</h4>
                        <div class="cab-rating"><i class="fas fa-star"></i> 5.0</div>
                    </div>
                    <p>Premium vehicles for corporate travel, airport runs or special occasions.</p>
                    <div class="cab-features">
                        <span><i class="fas fa-user-group"></i> 4 seats</span>
                        <span><i class="fas fa-briefcase"></i> Business</span>
                        <span><i class="fas fa-shield-halved"></i> Insured</span>
                    </div>
                    <a href="book.php?type=luxury" class="btn btn-outline btn-sm">Book Luxury</a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     WHY CHOOSE US — features strip
═══════════════════════════════════════════════════════════════ -->
<section class="features-section" id="features">
    <div class="container">
        <div class="section-title reveal">
            <h2>Why Riders <span>Choose Us</span></h2>
            <p>We built RUS CAB around one principle — your time matters.</p>
        </div>

        <div class="features-grid stagger">
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fas fa-shield-halved"></i></div>
                <h4>Verified Drivers</h4>
                <p>Every driver passes background checks, license verification and a safety training programme.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                <h4>Instant Booking</h4>
                <p>Confirm your ride in under 60 seconds. No app required — works directly in your browser.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fas fa-tag"></i></div>
                <h4>Transparent Pricing</h4>
                <p>Fixed per-km rates, no surge pricing, no hidden fees. See the full fare before you confirm.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="fas fa-clock"></i></div>
                <h4>24/7 Available</h4>
                <p>Early morning airport drop or a late-night pickup — we're available every hour of every day.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     SOCIAL PROOF — testimonials
═══════════════════════════════════════════════════════════════ -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-title reveal">
            <h2>What Riders <span>Say</span></h2>
            <p>Over 12,000 rides completed across Surat with a 4.9-star average.</p>
        </div>

        <div class="testimonials-grid stagger">
            <div class="testimonial-card reveal">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i>
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p>"Driver arrived exactly on time. Car was spotless and the AC was perfect. Best cab service I've used in Surat — will always book RUS CAB."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">RK</div>
                    <div>
                        <strong>Rahul Kapoor</strong>
                        <span>Sedan • Surat City</span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card reveal">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i>
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p>"Booked the SUV for a family trip. Six of us fit comfortably and the driver was polite and professional. Pricing was exactly what was shown."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">PM</div>
                    <div>
                        <strong>Priya Mehta</strong>
                        <span>SUV • Family Ride</span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card reveal">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i>
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p>"Used the Luxury cab for a client pickup from the airport. Driver was in uniform, car was immaculate. My client was genuinely impressed."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">AS</div>
                    <div>
                        <strong>Ankit Shah</strong>
                        <span>Luxury • Corporate</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     DRIVER CTA BANNER
═══════════════════════════════════════════════════════════════ -->
<section class="driver-banner">
    <div class="container driver-banner-inner">
        <div class="driver-banner-text reveal">
            <div class="hero-eyebrow"><i class="fas fa-id-badge"></i> For Drivers</div>
            <h2>Earn on Your Schedule.<br><span>Drive with RUS CAB.</span></h2>
            <p>Join 500+ drivers earning steady income across Surat. Flexible hours, instant payouts, full support.</p>
        </div>
        <div class="driver-banner-action reveal">
            <a href="../driver/register.php" class="btn btn-primary">
                <i class="fas fa-car-side"></i> Register as Driver
            </a>
            <a href="../driver/index.php" class="btn btn-outline-light">
                Driver Login
            </a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>