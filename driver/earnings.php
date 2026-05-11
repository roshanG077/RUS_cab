<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/db.php';
if (!isset($_SESSION['driver_id'])) { header("Location: index.php"); exit(); }

$driver_id  = $_SESSION['driver_id'];
$today      = date('Y-m-d');
$this_month = date('Y-m');

$wallet      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT wallet_balance FROM drivers WHERE id='$driver_id'"))['wallet_balance'] ?? 0;
$today_earn  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(fare) AS s FROM bookings WHERE driver_id='$driver_id' AND status='Completed' AND DATE(created_at)='$today'"))['s'] ?? 0;
$month_earn  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(fare) AS s FROM bookings WHERE driver_id='$driver_id' AND status='Completed' AND DATE_FORMAT(created_at,'%Y-%m')='$this_month'"))['s'] ?? 0;
$life_data   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS rides, SUM(fare) AS total FROM bookings WHERE driver_id='$driver_id' AND status='Completed'"));
$total_rides = $life_data['rides'] ?? 0;
$total_earn  = $life_data['total'] ?? 0;

$history = mysqli_query($conn, "SELECT * FROM bookings WHERE driver_id='$driver_id' AND status='Completed' ORDER BY id DESC");

include '../driver/includes/header.php';
?>

<div class="page-header">
    <h1>Earnings & Wallet</h1>
    <p>Track your income, monitor daily performance, and request payouts.</p>
</div>

<!-- Wallet hero -->
<div class="wallet-hero">
    <div class="wallet-label">Current Wallet Balance</div>
    <div class="wallet-amount">₹<?php echo number_format($wallet, 2); ?></div>
    <div class="wallet-sub">Payouts processed every Monday directly to your bank account</div>
    <button class="d-btn d-btn-primary" style="margin-top:var(--d-sp-md);"
            onclick="alert('Withdrawal request sent to admin. Processing in 1–2 business days.')">
        <i class="fas fa-arrow-up-from-bracket"></i> Request Withdrawal
    </button>
</div>

<!-- Stats grid -->
<div class="d-stats-grid">
    <div class="d-stat-card amber">
        <div class="d-stat-label">Today</div>
        <div class="d-stat-value amber">₹<?php echo number_format($today_earn, 2); ?></div>
        <div class="d-stat-sub"><?php echo date('d M Y'); ?></div>
    </div>
    <div class="d-stat-card blue">
        <div class="d-stat-label">This Month</div>
        <div class="d-stat-value blue">₹<?php echo number_format($month_earn, 2); ?></div>
        <div class="d-stat-sub"><?php echo date('F Y'); ?></div>
    </div>
    <div class="d-stat-card green">
        <div class="d-stat-label">Lifetime Earnings</div>
        <div class="d-stat-value green">₹<?php echo number_format($total_earn, 2); ?></div>
        <div class="d-stat-sub">All completed rides</div>
    </div>
    <div class="d-stat-card purple">
        <div class="d-stat-label">Total Rides</div>
        <div class="d-stat-value purple"><?php echo $total_rides; ?></div>
        <div class="d-stat-sub">Completed all time</div>
    </div>
</div>

<!-- Transaction history -->
<div class="d-card">
    <div class="d-card-header">
        <h3><i class="fas fa-clock-rotate-left"></i> Trip-wise History</h3>
        <span class="text-dim" style="font-size:0.75rem;"><?php echo $total_rides; ?> completed rides</span>
    </div>

    <div style="overflow-x:auto;">
        <table class="d-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Ride ID</th>
                    <th>Route</th>
                    <th>Distance</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Settlement</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($history) > 0): ?>
                <?php while ($ride = mysqli_fetch_assoc($history)): ?>
                <tr>
                    <td>
                        <span style="font-size:0.82rem;color:var(--d-text);">
                            <?php echo date('d M Y', strtotime($ride['pickup_date'] ?? 'now')); ?>
                        </span><br>
                        <span class="text-dim" style="font-size:0.72rem;">
                            <?php echo isset($ride['pickup_time']) ? date('h:i A', strtotime($ride['pickup_time'])) : '—'; ?>
                        </span>
                    </td>
                    <td><span class="mono text-dim">#<?php echo $ride['id']; ?></span></td>
                    <td>
                        <div class="d-route">
                            <div class="d-route-point pickup">
                                <i class="fas fa-circle-dot"></i>
                                <?php echo htmlspecialchars(substr($ride['pickup_location'], 0, 28)); ?>...
                            </div>
                            <div class="d-route-point dropoff">
                                <i class="fas fa-location-dot"></i>
                                <?php echo htmlspecialchars(substr($ride['dropoff_location'], 0, 28)); ?>...
                            </div>
                        </div>
                    </td>
                    <td><span class="mono text-muted"><?php echo $ride['distance_km']; ?> km</span></td>
                    <td><span class="mono text-green" style="font-size:0.95rem;">₹<?php echo $ride['fare']; ?></span></td>
                    <td>
                        <span class="d-badge d-badge-accepted" style="text-transform:none;letter-spacing:0;">
                            <?php echo $ride['payment_method']; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($ride['payment_method'] === 'Cash'): ?>
                            <span class="d-badge d-badge-pending">Cash in hand</span>
                        <?php else: ?>
                            <span class="d-badge d-badge-running">Added to wallet</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:var(--d-sp-xl);color:var(--d-text-dim);">
                        <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                        No completed rides yet. Accept your first ride to start earning.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../driver/includes/footer.php'; ?>