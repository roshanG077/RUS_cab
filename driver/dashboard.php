<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/db.php';
if (!isset($_SESSION['driver_id'])) { header("Location: index.php"); exit(); }

$driver_id = $_SESSION['driver_id'];

// Suspension check
$check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_suspended FROM drivers WHERE id='$driver_id'"));
if ($check['is_suspended'] == 1) {
    session_destroy();
    header("Location: index.php?msg=Your account has been suspended due to a high cancellation rate.");
    exit();
}

// Fetch rides
$rides = mysqli_query($conn,
    "SELECT * FROM bookings
     WHERE status='Pending' OR driver_id='$driver_id'
     ORDER BY FIELD(status,'Running','Accepted','Pending','Completed','Cancelled'), id DESC"
);

// Stats
$today      = date('Y-m-d');
$this_month = date('Y-m');
$wallet     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT wallet_balance FROM drivers WHERE id='$driver_id'"))['wallet_balance'] ?? 0;
$today_earn = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(fare) AS s FROM bookings WHERE driver_id='$driver_id' AND status='Completed' AND DATE(created_at)='$today'"))['s'] ?? 0;
$active_c   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM bookings WHERE driver_id='$driver_id' AND status IN('Accepted','Running')"))['c'] ?? 0;
$completed  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM bookings WHERE driver_id='$driver_id' AND status='Completed'"))['c'] ?? 0;

include '../driver/includes/header.php';
?>

<div class="page-header">
    <h1>Live Dashboard</h1>
    <p>Monitor incoming ride requests and manage your active trips in real time.</p>
</div>

<div class="d-stats-grid">
    <div class="d-stat-card green">
        <div class="d-stat-label">Wallet Balance</div>
        <div class="d-stat-value green">₹<?php echo number_format($wallet, 2); ?></div>
        <div class="d-stat-sub">Available for withdrawal</div>
    </div>
    <div class="d-stat-card amber">
        <div class="d-stat-label">Today's Earnings</div>
        <div class="d-stat-value amber">₹<?php echo number_format($today_earn, 2); ?></div>
        <div class="d-stat-sub">From completed rides today</div>
    </div>
    <div class="d-stat-card blue">
        <div class="d-stat-label">Active Rides</div>
        <div class="d-stat-value blue"><?php echo $active_c; ?></div>
        <div class="d-stat-sub">Accepted or running now</div>
    </div>
    <div class="d-stat-card purple">
        <div class="d-stat-label">Total Completed</div>
        <div class="d-stat-value purple"><?php echo $completed; ?></div>
        <div class="d-stat-sub">All-time finished rides</div>
    </div>
</div>

<?php if (isset($_GET['msg'])): 
    $msg = $_GET['msg'];
    $is_err = preg_match('/(invalid|error|fail|susp)/i', $msg);
?>
    <div class="d-alert <?php echo $is_err ? 'd-alert-danger' : 'd-alert-success'; ?>">
        <i class="fas <?php echo $is_err ? 'fa-circle-exclamation' : 'fa-circle-check'; ?>"></i>
        <?php echo htmlspecialchars($msg); ?>
    </div>
<?php endif; ?>

<div class="d-card" style="margin-bottom:var(--d-sp-lg);">
    <div class="d-card-header">
        <h3><i class="fas fa-route"></i> Ride Requests</h3>
        <span class="text-dim" style="font-size:0.72rem;">
            <i class="fas fa-circle-notch fa-spin" style="font-size:0.6rem;"></i>
            Auto-refreshes every 30s
        </span>
    </div>

    <div style="overflow-x:auto;">
        <table class="d-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Route</th>
                    <th>Fare</th>
                    <th>Vehicle</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($rides)): ?>
            <tr>
                <td><span class="mono text-dim">#<?php echo $row['id']; ?></span></td>

                <td>
                    <div class="d-route">
                        <div class="d-route-point pickup">
                            <i class="fas fa-circle-dot"></i>
                            <?php echo htmlspecialchars(implode(', ', array_slice(explode(',', $row['pickup_location']), 0, 2))); ?>
                        </div>
                        <div class="d-route-point dropoff">
                            <i class="fas fa-location-dot"></i>
                            <?php echo htmlspecialchars(implode(', ', array_slice(explode(',', $row['dropoff_location']), 0, 2))); ?>
                        </div>
                    </div>
                </td>

                <td>
                    <span class="mono text-green">₹<?php echo $row['fare']; ?></span><br>
                    <span class="text-dim" style="font-size:0.72rem;"><?php echo $row['distance_km']; ?> km</span>
                </td>

                <td style="font-size:0.8rem;" class="text-muted"><?php echo $row['car_type']; ?></td>

                <td>
                    <?php
                    $s = strtolower($row['status']);
                    $badge_map = ['pending'=>'d-badge-pending','accepted'=>'d-badge-accepted','running'=>'d-badge-running','completed'=>'d-badge-completed','cancelled'=>'d-badge-cancelled'];
                    ?>
                    <span class="d-badge <?php echo $badge_map[$s] ?? 'd-badge-pending'; ?>">
                        <?php echo $row['status']; ?>
                    </span>
                </td>

                <td>
                    <?php if ($row['status'] === 'Pending'): ?>
                        <form action="update_ride.php" method="POST">
                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="action" value="accept" class="d-btn d-btn-primary">
                                <i class="fas fa-check"></i> Accept
                            </button>
                        </form>

                    <?php elseif ($row['status'] === 'Accepted'): ?>
                        <form action="verify_otp.php" method="POST">
                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                            <div class="d-otp-row">
                                <input type="number" name="entered_otp" class="d-otp-input"
                                       placeholder="OTP" required maxlength="6">
                                <button type="submit" class="d-btn d-btn-primary">
                                    <i class="fas fa-play"></i> Start
                                </button>
                            </div>
                        </form>
                        <form action="driver_cancel.php" method="POST" style="margin-top:0.5rem;">
                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                            <select name="reason" class="d-reason-select" required>
                                <option value="">Select reason...</option>
                                <option value="Incorrect Location">Incorrect Location</option>
                                <option value="Safety Issue">Safety Issue</option>
                                <option value="Car Breakdown">Car Breakdown</option>
                                <option value="Passenger No-Show">Passenger No-Show</option>
                            </select>
                            <button type="submit" class="d-btn d-btn-danger" style="margin-top:0.35rem;width:100%;">
                                <i class="fas fa-xmark"></i> Cancel Ride
                            </button>
                        </form>

                    <?php elseif ($row['status'] === 'Running'): ?>
                        <form action="complete_ride.php" method="POST">
                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="d-btn d-btn-warning d-btn-full">
                                <i class="fas fa-flag-checkered"></i> Finish Ride
                            </button>
                        </form>

                    <?php else: ?>
                        <span class="text-dim" style="font-size:0.75rem;">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
setTimeout(() => location.reload(), 30000);
</script>

<?php include '../driver/includes/footer.php'; ?>