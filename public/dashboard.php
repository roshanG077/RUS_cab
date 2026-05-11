<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id  = $_SESSION['user_id'];
$sql      = "SELECT b.*,
                    d.first_name AS d_name,
                    d.phone      AS d_phone,
                    d.license_no AS d_plate,
                    d.vehicle_type AS d_car
             FROM bookings b
             LEFT JOIN drivers d ON b.driver_id = d.id
             WHERE b.user_id = '$user_id'
             ORDER BY b.id DESC";
$result   = mysqli_query($conn, $sql);

$page_title = "My Rides — RUS CAB";
include '../includes/header.php';
?>

<div class="page-main">
    <div class="container">

        <div class="dashboard-header">
            <div>
                <h1>My Rides</h1>
                <p>Welcome back, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>.
                   Here is your full ride history.</p>
            </div>
            <a href="book.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Ride
            </a>
        </div>

        <!-- ── Stats Strip ── -->
        <?php
        $total_sql  = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM bookings WHERE user_id='$user_id'");
        $total      = mysqli_fetch_assoc($total_sql)['cnt'];
        $done_sql   = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM bookings WHERE user_id='$user_id' AND status='Completed'");
        $done       = mysqli_fetch_assoc($done_sql)['cnt'];
        $spend_sql  = mysqli_query($conn, "SELECT SUM(fare) AS total FROM bookings WHERE user_id='$user_id' AND payment_status='Paid'");
        $spent      = mysqli_fetch_assoc($spend_sql)['total'] ?? 0;
        ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <p>Total Rides</p>
                    <h3><?php echo $total; ?></h3>
                </div>
                <div class="stat-icon"><i class="fas fa-taxi"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <p>Completed</p>
                    <h3><?php echo $done; ?></h3>
                </div>
                <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <p>Total Spent</p>
                    <h3>₹<?php echo number_format($spent); ?></h3>
                </div>
                <div class="stat-icon"><i class="fas fa-wallet"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <p>Active Rides</p>
                    <h3><?php echo $total - $done; ?></h3>
                </div>
                <div class="stat-icon"><i class="fas fa-spinner"></i></div>
            </div>
        </div>

        <!-- ── Ride History Table ── -->
        <div class="data-table-wrapper">
            <div class="data-table-header">
                <h3><i class="fas fa-list"></i> Ride History</h3>
                <a href="book.php" class="btn btn-sm">
                    <i class="fas fa-plus"></i> Book New
                </a>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Route & Driver</th>
                        <th>OTP</th>
                        <th>Fare & Distance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                mysqli_data_seek($result, 0);
                while ($row = mysqli_fetch_assoc($result)):
                    $status   = $row['status'];
                    $p_status = $row['payment_status'] ?? 'Unpaid';
                    $p_method = $row['payment_method'] ?? 'Cash';
                    $dist     = $row['distance_km'] ?? '0';
                ?>
                <tr>
                    <!-- Route + Driver -->
                    <td>
                        <div class="ride-route">
                            <span class="route-point pickup">
                                <i class="fas fa-circle-dot"></i>
                                <?php echo htmlspecialchars($row['pickup_location']); ?>
                            </span>
                            <span class="route-point dropoff">
                                <i class="fas fa-location-dot"></i>
                                <?php echo htmlspecialchars($row['dropoff_location']); ?>
                            </span>
                        </div>

                        <?php if ($row['driver_id']): ?>
                            <div class="driver-chip">
                                <div class="driver-chip-avatar">
                                    <?php echo strtoupper(substr($row['d_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($row['d_name']); ?></strong>
                                    <span><?php echo htmlspecialchars($row['d_car']); ?> &middot; <?php echo htmlspecialchars($row['d_plate']); ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="driver-searching">
                                <i class="fas fa-circle-notch fa-spin"></i> Searching for driver...
                            </div>
                        <?php endif; ?>
                    </td>

                    <!-- OTP -->
                    <td>
                        <?php if (in_array($status, ['Pending', 'Accepted'])): ?>
                            <div class="otp-badge">
                                <?php echo $row['otp']; ?>
                            </div>
                            <span class="otp-hint">Show to driver</span>
                        <?php else: ?>
                            <span class="text-muted"><i class="fas fa-lock"></i> Expired</span>
                        <?php endif; ?>
                    </td>

                    <!-- Fare -->
                    <td>
                        <div class="fare-display">
                            <strong>₹<?php echo number_format($row['fare']); ?></strong>
                            <span><?php echo $dist; ?> km &middot; <?php echo $p_method; ?></span>
                        </div>
                        <?php if ($p_status === 'Paid'): ?>
                            <span class="badge badge-active"><i class="fas fa-check"></i> Paid</span>
                        <?php else: ?>
                            <span class="badge badge-pending">Unpaid</span>
                        <?php endif; ?>
                    </td>

                    <!-- Status -->
                    <td>
                        <?php
                        $badge_class = match($status) {
                            'Completed' => 'badge-complete',
                            'Running'   => 'badge-active',
                            'Cancelled' => 'badge-cancelled',
                            default     => 'badge-pending'
                        };
                        ?>
                        <span class="badge <?php echo $badge_class; ?>">
                            <?php echo $status; ?>
                        </span>
                    </td>

                    <!-- Actions -->
                    <td>
                        <div class="ride-actions">
                            <?php if (in_array($status, ['Pending', 'Accepted'])): ?>
                                <a href="cancel_ride.php?id=<?php echo $row['id']; ?>&type=free"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Cancel this ride for free?')">
                                    <i class="fas fa-times"></i> Cancel
                                </a>

                            <?php elseif ($status === 'Running'): ?>
                                <a href="cancel_ride.php?id=<?php echo $row['id']; ?>&type=paid"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Trip is live. Cancelling now costs ₹50. Proceed?')">
                                    <i class="fas fa-exclamation-triangle"></i> Cancel (₹50)
                                </a>
                            <?php endif; ?>

                            <?php if ($status === 'Running' && $p_method === 'UPI' && $p_status !== 'Paid'): ?>
                                <a href="process_payment.php?id=<?php echo $row['id']; ?>"
                                   class="btn btn-sm btn-accent">
                                    <i class="fas fa-qrcode"></i> Pay Now
                                </a>
                            <?php endif; ?>

                            <?php if ($status === 'Completed' && $row['is_rated'] == 0): ?>
                                <a href="rate_driver.php?id=<?php echo $row['id']; ?>"
                                   class="btn btn-sm">
                                    <i class="fas fa-star"></i> Rate
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>