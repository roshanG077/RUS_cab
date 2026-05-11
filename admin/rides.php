<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit(); }

// Force cancel
if (isset($_POST['admin_cancel'])) {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    mysqli_query($conn, "UPDATE bookings SET status='Cancelled', cancelled_by='User', cancel_reason='Cancelled by Admin' WHERE id='$booking_id'");
    header("Location: rides.php?msg=Ride #$booking_id has been cancelled by Admin.");
    exit();
}

// Filter
$where_clause   = "";
$current_filter = "All";
if (!empty($_GET['status']) && $_GET['status'] !== 'All') {
    $status         = mysqli_real_escape_string($conn, $_GET['status']);
    $where_clause   = "WHERE b.status='$status'";
    $current_filter = $status;
}

$rides = mysqli_query($conn,
    "SELECT b.*, u.full_name AS passenger, d.first_name AS driver_first
     FROM bookings b
     LEFT JOIN users   u ON b.user_id   = u.id
     LEFT JOIN drivers d ON b.driver_id = d.id
     $where_clause
     ORDER BY b.id DESC"
);

$total_count = mysqli_num_rows($rides);

include 'header.php';
?>

<div class="a-page-header">
    <h1>Ride Activity Monitor</h1>
    <p>Search, filter and take action on all platform bookings.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="a-alert a-alert-info">
        <i class="fas fa-circle-info"></i>
        <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>

<!-- Filter bar -->
<form method="GET" action="">
    <div class="a-filter-bar">
        <label>Filter by Status:</label>
        <select name="status" class="a-select" style="width:auto;">
            <?php
            $filters = ['All' => 'All Rides', 'Pending' => 'Pending', 'Accepted' => 'Accepted',
                        'Running' => 'Running', 'Completed' => 'Completed', 'Cancelled' => 'Cancelled'];
            foreach ($filters as $val => $label):
            ?>
            <option value="<?php echo $val; ?>" <?php echo ($current_filter === $val) ? 'selected' : ''; ?>>
                <?php echo $label; ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="a-btn a-btn-primary a-btn-sm">
            <i class="fas fa-filter"></i> Apply
        </button>
        <?php if ($current_filter !== 'All'): ?>
            <a href="rides.php" class="a-filter-clear">
                <i class="fas fa-xmark"></i> Clear filter
            </a>
        <?php endif; ?>
        <span style="margin-left:auto;font-size:0.75rem;color:var(--a-text-muted);">
            <?php echo $total_count; ?> ride<?php echo $total_count !== 1 ? 's' : ''; ?> shown
        </span>
    </div>
</form>

<div class="a-panel">
    <div style="overflow-x:auto;">
        <table class="a-table">
            <thead>
                <tr>
                    <th>Ride</th>
                    <th>Passenger</th>
                    <th>Driver</th>
                    <th>Route</th>
                    <th>Fare & Payment</th>
                    <th>Status</th>
                    <th>Admin Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($total_count > 0):
                mysqli_data_seek($rides, 0);
                while ($row = mysqli_fetch_assoc($rides)):
                    $s = strtolower($row['status']);
                    $badge_map = ['pending'=>'a-badge-pending','accepted'=>'a-badge-accepted','running'=>'a-badge-running','completed'=>'a-badge-completed','cancelled'=>'a-badge-cancelled'];
            ?>
            <tr>
                <!-- ID + Date -->
                <td>
                    <strong style="font-family:'Montserrat',sans-serif;font-size:0.88rem;color:var(--a-text);">
                        #<?php echo $row['id']; ?>
                    </strong><br>
                    <span style="font-size:0.72rem;color:var(--a-text-muted);">
                        <?php echo isset($row['created_at']) ? date('d M, h:i A', strtotime($row['created_at'])) : '—'; ?>
                    </span>
                </td>

                <!-- Passenger -->
                <td style="font-size:0.83rem;">
                    <?php echo htmlspecialchars($row['passenger'] ?? 'Unknown'); ?>
                </td>

                <!-- Driver -->
                <td style="font-size:0.83rem;color:var(--a-text-muted);">
                    <?php echo htmlspecialchars($row['driver_first'] ?? 'Not assigned'); ?>
                </td>

                <!-- Route -->
                <td style="min-width:160px;">
                    <div style="font-size:0.75rem;color:var(--a-text-sub);">
                        <span style="color:var(--a-green);font-weight:600;">P </span>
                        <?php echo htmlspecialchars(substr($row['pickup_location'], 0, 30)); ?>
                    </div>
                    <div style="font-size:0.75rem;color:var(--a-text-sub);margin-top:2px;">
                        <span style="color:var(--a-red);font-weight:600;">D </span>
                        <?php echo htmlspecialchars(substr($row['dropoff_location'], 0, 30)); ?>
                    </div>
                </td>

                <!-- Fare -->
                <td>
                    <strong style="font-family:'Montserrat',sans-serif;font-size:0.95rem;color:var(--a-text);">
                        ₹<?php echo $row['fare']; ?>
                    </strong><br>
                    <span style="font-size:0.72rem;color:<?php echo ($row['payment_status'] === 'Paid') ? 'var(--a-green)' : 'var(--a-red)'; ?>;">
                        <?php echo $row['payment_method']; ?> · <?php echo $row['payment_status']; ?>
                    </span>
                </td>

                <!-- Status badge -->
                <td>
                    <span class="a-badge <?php echo $badge_map[$s] ?? 'a-badge-pending'; ?>">
                        <?php echo $row['status']; ?>
                    </span>
                </td>

                <!-- Admin action -->
                <td>
                    <?php if (in_array($row['status'], ['Pending', 'Accepted', 'Running'])): ?>
                        <form method="POST">
                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="admin_cancel"
                                    class="a-btn a-btn-danger a-btn-sm"
                                    onclick="return confirm('Force cancel ride #<?php echo $row['id']; ?>? This cannot be undone.')">
                                <i class="fas fa-circle-xmark"></i> Force Cancel
                            </button>
                        </form>
                    <?php else: ?>
                        <span style="font-size:0.75rem;color:var(--a-text-dim);">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile;
            else: ?>
            <tr>
                <td colspan="7">
                    <div class="a-empty">
                        <i class="fas fa-inbox"></i>
                        <strong>No rides found</strong>
                        <span>Try a different filter or check back later.</span>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>