<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Book a Ride — RUS CAB";
include '../includes/header.php';
?>

<!-- Leaflet Map CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

<div class="book-layout">

    <!-- ── Sidebar Form ── -->
    <aside class="book-sidebar">
        <div class="book-sidebar-header">
            <h3><i class="fas fa-taxi"></i> Confirm Your Ride</h3>
            <p>Click the map to set pickup, then drop-off.</p>
        </div>

        <form action="process_booking.php" method="POST" id="bookingForm">

            <!-- Pickup -->
            <div class="form-group">
                <label>
                    <span class="map-dot green"></span> Pickup Location
                </label>
                <input type="text" name="pickup_location" id="p_disp"
                       placeholder="Click map to set pickup" readonly required>
                <span class="map-loader" id="p_load">
                    <i class="fas fa-spinner fa-spin"></i> Fetching address...
                </span>
            </div>

            <!-- Dropoff -->
            <div class="form-group">
                <label>
                    <span class="map-dot red"></span> Drop-off Location
                </label>
                <input type="text" name="dropoff_location" id="d_disp"
                       placeholder="Click map to set drop-off" readonly required>
                <span class="map-loader" id="d_load">
                    <i class="fas fa-spinner fa-spin"></i> Fetching address...
                </span>
            </div>

            <!-- Vehicle Type -->
            <div class="form-group">
                <label><i class="fas fa-car"></i> Vehicle Type</label>
                <select name="car_type" id="car_type">
                    <option value="Sedan"   data-rate="15">Sedan — ₹15/km</option>
                    <option value="SUV"     data-rate="22">SUV — ₹22/km</option>
                    <option value="Luxury"  data-rate="40">Luxury — ₹40/km</option>
                </select>
            </div>

            <!-- Hidden fare fields -->
            <input type="hidden" name="distance_km" id="h_dist">
            <input type="hidden" name="fare"        id="h_fare">

            <!-- Payment Method -->
            <div class="form-group">
                <label><i class="fas fa-credit-card"></i> Payment Method</label>
                <select name="payment_method" id="pay_method" required>
                    <option value="Cash">Cash — Pay Driver</option>
                    <option value="Card">Credit / Debit Card</option>
                    <option value="UPI">UPI / QR Code</option>
                </select>
            </div>

            <!-- Fare Summary Card (shown after route) -->
            <div class="fare-card" id="price_card">
                <div class="fare-card-row">
                    <span><i class="fas fa-route"></i> Distance</span>
                    <strong id="dist_txt">—</strong>
                </div>
                <div class="fare-card-row">
                    <span><i class="fas fa-tag"></i> Estimated Fare</span>
                    <strong class="fare-amount" id="fare_txt">—</strong>
                </div>
                <div class="fare-card-row">
                    <span><i class="fas fa-car-side"></i> Service</span>
                    <strong id="car_label">—</strong>
                </div>
                <p class="fare-note">Minimum fare ₹40. Final fare may vary slightly.</p>
            </div>

            <button type="submit" name="confirm_booking" class="btn btn-full">
                <i class="fas fa-check-circle"></i> Confirm Booking
            </button>

            <p class="book-reset-hint">
                <i class="fas fa-info-circle"></i>
                Click the map a third time to reset markers and start over.
            </p>

        </form>
    </aside>

    <!-- ── Map Panel ── -->
    <div id="map" class="book-map"></div>

</div>

<!-- ── Payment Modal ── -->
<div class="modal-overlay" id="paymentModal">
    <div class="modal-card">
        <button class="modal-close" onclick="closeModal()" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>

        <!-- UPI -->
        <div id="upi_ui" class="modal-body">
            <div class="modal-icon"><i class="fas fa-qrcode"></i></div>
            <h3>Scan & Pay via UPI</h3>
            <p class="modal-sub">Use any UPI app to scan the QR code below</p>
            <div class="qr-wrap">
                <img src="" id="qr_img" alt="UPI QR Code">
            </div>
            <div class="modal-amount">
                Total: <span>₹<strong id="modal_fare_upi"></strong></span>
            </div>
        </div>

        <!-- Card -->
        <div id="card_ui" class="modal-body">
            <div class="modal-icon"><i class="fas fa-credit-card"></i></div>
            <h3>Card Payment</h3>
            <p class="modal-sub">Enter your card details below</p>
            <div class="card-fields">
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" placeholder="1234 5678 9012 3456" maxlength="19">
                </div>
                <div class="card-fields-row">
                    <div class="form-group">
                        <label>Expiry</label>
                        <input type="text" placeholder="MM / YY" maxlength="7">
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="password" placeholder="•••" maxlength="3">
                    </div>
                </div>
            </div>
            <div class="modal-amount">
                Total: <span>₹<strong id="modal_fare_card"></strong></span>
            </div>
        </div>

        <button class="btn btn-full btn-success" onclick="finalizeBooking()">
            <i class="fas fa-lock"></i> Verify & Pay
        </button>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script src="assets/js/book.js"></script>

<?php include '../includes/footer.php'; ?>