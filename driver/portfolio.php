<?php
if (session_status() === PHP_SESSION_NONE) session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once '../config/db.php';
if (!isset($_SESSION['driver_id'])) { header("Location: index.php?msg=Please login first"); exit(); }

$driver_id = $_SESSION['driver_id'];
$driver    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM drivers WHERE id='$driver_id'"));
$reviews   = mysqli_query($conn,
    "SELECT r.*, u.full_name, u.id AS user_id
     FROM ratings r
     JOIN users u ON r.user_id = u.id
     WHERE r.driver_id='$driver_id'
     ORDER BY r.id DESC"
);
$review_count = mysqli_num_rows($reviews);

// Stat: completed rides
$completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM bookings WHERE driver_id='$driver_id' AND status='Completed'"))['c'] ?? 0;
$on_time   = 98; // placeholder — replace with real metric if stored

$initials  = strtoupper(substr($driver['first_name'], 0, 1) . substr($driver['last_name'], 0, 1));

include '../driver/includes/header.php';
?>

<div class="page-header">
    <h1>My Portfolio</h1>
    <p>Your public driver profile, star rating, and passenger feedback history.</p>
</div>

<!-- Portfolio header card -->
<div class="portfolio-header">
    <div class="portfolio-avatar"><?php echo $initials; ?></div>
    <div class="portfolio-info">
        <h2><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></h2>
        <div class="portfolio-rating">
            <span class="stars">
                <?php
                $rating = floatval($driver['rating'] ?? 5.0);
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= floor($rating))      echo '<i class="fas fa-star"></i>';
                    elseif ($i - $rating < 1)      echo '<i class="fas fa-star-half-stroke"></i>';
                    else                           echo '<i class="far fa-star"></i>';
                }
                ?>
            </span>
            <strong><?php echo number_format($rating, 1); ?></strong>
            <span class="text-dim" style="font-size:0.78rem;">/ 5.0 from <?php echo $review_count; ?> reviews</span>
        </div>
        <div class="portfolio-meta">
            <span><i class="fas fa-car-side"></i> <?php echo htmlspecialchars($driver['vehicle_type']); ?></span>
            <span><i class="fas fa-id-card"></i> <?php echo htmlspecialchars($driver['license_no']); ?></span>
            <span><i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($driver['city'] ?? 'Surat'); ?></span>
            <span><i class="fas fa-flag-checkered"></i> <?php echo $completed; ?> rides completed</span>
        </div>
    </div>

    <!-- Mini stats on the right -->
    <div style="margin-left:auto;display:flex;gap:var(--d-sp-lg);flex-shrink:0;">
        <div style="text-align:center;">
            <div class="mono text-green" style="font-size:1.5rem;"><?php echo $completed; ?></div>
            <div style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--d-text-dim);margin-top:2px;">Completed</div>
        </div>
        <div style="text-align:center;">
            <div class="mono" style="font-size:1.5rem;color:var(--d-warning);"><?php echo number_format($rating, 1); ?>★</div>
            <div style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--d-text-dim);margin-top:2px;">Rating</div>
        </div>
        <div style="text-align:center;">
            <div class="mono text-blue" style="font-size:1.5rem;color:var(--d-info);"><?php echo $on_time; ?>%</div>
            <div style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--d-text-dim);margin-top:2px;">On Time</div>
        </div>
    </div>
</div>

<!-- Reviews -->
<div class="d-card">
    <div class="d-card-header">
        <h3><i class="fas fa-comments"></i> Passenger Reviews</h3>
        <span class="text-dim" style="font-size:0.75rem;"><?php echo $review_count; ?> total</span>
    </div>

    <?php if ($review_count > 0): ?>
        <div>
        <?php
        mysqli_data_seek($reviews, 0);
        while ($row = mysqli_fetch_assoc($reviews)):
            $user_initial = strtoupper(substr($row['full_name'], 0, 1));
        ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="review-user">
                        <div class="review-avatar"><?php echo $user_initial; ?></div>
                        <div>
                            <div class="review-name"><?php echo htmlspecialchars($row['full_name']); ?></div>
                            <div class="review-stars">
                                <?php for ($i = 0; $i < intval($row['stars']); $i++) echo '<i class="fas fa-star"></i>'; ?>
                                <?php for ($i = intval($row['stars']); $i < 5; $i++) echo '<i class="far fa-star" style="color:var(--d-surface-3)"></i>'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="review-date">
                        <?php echo isset($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : '—'; ?>
                    </div>
                </div>
                <?php if (!empty($row['feedback'])): ?>
                    <div class="review-text">"<?php echo htmlspecialchars($row['feedback']); ?>"</div>
                <?php else: ?>
                    <div class="review-text text-dim">No written review.</div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align:center;padding:var(--d-sp-xl);color:var(--d-text-dim);">
            <i class="fas fa-star" style="font-size:2rem;display:block;margin-bottom:var(--d-sp-sm);color:var(--d-surface-3);"></i>
            No reviews yet. Complete rides to start building your reputation.
        </div>
    <?php endif; ?>
</div>

<?php include '../driver/includes/footer.php'; ?>