<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$booking_id = intval($_GET['id']);
$res        = mysqli_query($conn,
    "SELECT b.*, d.first_name
     FROM bookings b
     JOIN drivers d ON b.driver_id = d.id
     WHERE b.id = '$booking_id' AND b.user_id = '{$_SESSION['user_id']}'"
);
$ride = mysqli_fetch_assoc($res);

if (!$ride) { header("Location: dashboard.php"); exit(); }

$page_title = "Rate Your Driver — RUS CAB";
include '../includes/header.php';
?>

<div class="page-main">
    <div class="container">
        <div class="rate-wrapper">
            <div class="rate-card">

                <div class="rate-card-header">
                    <div class="rate-avatar">
                        <?php echo strtoupper(substr($ride['first_name'], 0, 1)); ?>
                    </div>
                    <h2>How was your ride<br>with <span><?php echo htmlspecialchars($ride['first_name']); ?></span>?</h2>
                    <p>Your feedback helps us maintain service quality.</p>
                </div>

                <form action="submit_rating.php" method="POST" class="rate-form">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    <input type="hidden" name="driver_id"  value="<?php echo $ride['driver_id']; ?>">
                    <input type="hidden" name="stars"      id="star_input" value="5">

                    <!-- Star selector -->
                    <div class="star-selector" id="star-container">
                        <button type="button" class="star-btn active" data-value="1"><i class="fas fa-star"></i></button>
                        <button type="button" class="star-btn active" data-value="2"><i class="fas fa-star"></i></button>
                        <button type="button" class="star-btn active" data-value="3"><i class="fas fa-star"></i></button>
                        <button type="button" class="star-btn active" data-value="4"><i class="fas fa-star"></i></button>
                        <button type="button" class="star-btn active" data-value="5"><i class="fas fa-star"></i></button>
                    </div>
                    <p class="star-label" id="star_label">Excellent</p>

                    <!-- Quick tags -->
                    <div class="rate-tags">
                        <button type="button" class="rate-tag">On Time</button>
                        <button type="button" class="rate-tag">Clean Car</button>
                        <button type="button" class="rate-tag">Safe Driver</button>
                        <button type="button" class="rate-tag">Polite</button>
                        <button type="button" class="rate-tag">Great Route</button>
                    </div>

                    <div class="form-group">
                        <label for="feedback">Leave a review <span class="optional">(optional)</span></label>
                        <textarea id="feedback" name="feedback"
                                  placeholder="Share your experience in a few words..."
                                  rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-full">
                        <i class="fas fa-paper-plane"></i> Submit Review
                    </button>
                    <a href="dashboard.php" class="btn-skip">Skip for now</a>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="assets/js/rate.js"></script>
<?php include '../includes/footer.php'; ?>