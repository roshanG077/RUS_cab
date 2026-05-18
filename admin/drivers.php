<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit(); }

// Approve / Suspend
if (isset($_POST['action'])) {
    $driver_id = mysqli_real_escape_string($conn, $_POST['driver_id']);
    $action    = $_POST['action'];

    if ($action === 'approve') {
        mysqli_query($conn, "UPDATE drivers SET is_active=1, is_suspended=0 WHERE id='$driver_id'");
        $msg = "Driver approved and activated.";
    } elseif ($action === 'suspend') {
        mysqli_query($conn, "UPDATE drivers SET is_active=0, is_suspended=1 WHERE id='$driver_id'");
        $msg = "Driver account suspended.";
    }

    header("Location: drivers.php?msg=" . urlencode($msg));
    exit();
}

$drivers = mysqli_query($conn, "SELECT * FROM drivers ORDER BY id DESC");

include 'header.php';
?>

<div class="a-page-header">
    <h1>Driver Fleet Management</h1>
    <p>Approve new drivers, monitor performance, and manage account status.</p>
</div>

<?php if (isset($_GET['msg'])): 
    $msg = $_GET['msg'];
    $is_err = preg_match('/(suspended|error|failed|blocked)/i', $msg);
?>
    <div class="a-alert <?php echo $is_err ? 'a-alert-danger' : 'a-alert-success'; ?>">
        <i class="fas <?php echo $is_err ? 'fa-triangle-exclamation' : 'fa-circle-check'; ?>"></i>
        <?php echo htmlspecialchars($msg); ?>
    </div>
<?php endif; ?>

<div class="a-panel">
    <div class="a-panel-header">
        <h3><i class="fas fa-id-card"></i> All Drivers</h3>
        <span style="font-size:0.75rem;color:var(--a-text-muted);">
            <?php echo mysqli_num_rows($drivers); ?> registered
        </span>
    </div>
    <div style="overflow-x:auto;">
        <table class="a-table">
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>Vehicle & Plate</th>
                    <th>Wallet</th>
                    <th>Rating</th>
                    <th>Performance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($driver = mysqli_fetch_assoc($drivers)):
                $acc_rate = ($driver['total_requests_received'] > 0)
                    ? round(($driver['total_requests_accepted'] / $driver['total_requests_received']) * 100)
                    : 100;
                $can_rate = ($driver['total_requests_accepted'] > 0)
                    ? round(($driver['total_rides_cancelled']  / $driver['total_requests_accepted']) * 100)
                    : 0;
                $initials = strtoupper(substr($driver['first_name'], 0, 1) . substr($driver['last_name'], 0, 1));
            ?>
            <tr>
                <!-- Driver info -->
                <td>
                    <div style="display:flex;align-items:center;gap:0.65rem;">
                        <div style="width:34px;height:34px;border-radius:50%;background:var(--a-gray-bg);border:1px solid var(--a-border-dark);display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;color:var(--a-text-sub);flex-shrink:0;">
                            <?php echo $initials; ?>
                        </div>
                        <div>
                            <strong style="font-size:0.85rem;color:var(--a-text);display:block;">
                                <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                            </strong>
                            <span style="font-size:0.75rem;color:var(--a-text-muted);">
                                <i class="fas fa-phone" style="font-size:0.6rem;"></i>
                                <?php echo htmlspecialchars($driver['phone']); ?>
                            </span>
                        </div>
                    </div>
                </td>

                <!-- Vehicle -->
                <td>
                    <span style="background:var(--a-gray-bg);padding:0.15rem 0.5rem;border-radius:4px;font-size:0.75rem;font-weight:600;color:var(--a-text);">
                        <?php echo htmlspecialchars($driver['vehicle_type']); ?>
                    </span><br>
                    <span style="font-size:0.75rem;color:var(--a-text-muted);margin-top:2px;display:block;">
                        <?php echo htmlspecialchars($driver['license_no']); ?>
                    </span>
                </td>

                <!-- Wallet -->
                <td>
                    <span style="font-family:'Montserrat',sans-serif;font-weight:700;font-size:0.95rem;color:var(--a-green);">
                        ₹<?php echo number_format($driver['wallet_balance'], 2); ?>
                    </span>
                </td>

                <!-- Rating -->
                <td>
                    <div style="display:flex;align-items:center;gap:0.3rem;">
                        <i class="fas fa-star" style="color:var(--a-amber);font-size:0.8rem;"></i>
                        <strong style="font-family:'Montserrat',sans-serif;"><?php echo number_format($driver['rating'], 1); ?></strong>
                    </div>
                </td>

                <!-- Performance -->
                <td style="min-width:130px;">
                    <div class="a-metric" style="margin-bottom:0.3rem;">
                        <span style="font-size:0.68rem;color:var(--a-text-muted);width:26px;">Acc</span>
                        <div class="a-metric-bar">
                            <div class="a-metric-fill" style="width:<?php echo $acc_rate; ?>%;"></div>
                        </div>
                        <span style="font-size:0.72rem;color:var(--a-green);font-weight:600;"><?php echo $acc_rate; ?>%</span>
                    </div>
                    <div class="a-metric">
                        <span style="font-size:0.68rem;color:var(--a-text-muted);width:26px;">Can</span>
                        <div class="a-metric-bar">
                            <div class="a-metric-fill red" style="width:<?php echo min($can_rate, 100); ?>%;"></div>
                        </div>
                        <span style="font-size:0.72rem;color:<?php echo ($can_rate > 20) ? 'var(--a-red)' : 'var(--a-text-muted)'; ?>;font-weight:600;">
                            <?php echo $can_rate; ?>%
                        </span>
                    </div>
                </td>

                <!-- Status badge -->
                <td>
                    <?php if ($driver['is_suspended'] == 1): ?>
                        <span class="a-badge a-badge-suspended"><i class="fas fa-ban" style="font-size:0.5rem;"></i> Suspended</span>
                    <?php elseif ($driver['is_active'] == 0): ?>
                        <span class="a-badge a-badge-pending-review"><i class="fas fa-clock" style="font-size:0.5rem;"></i> Pending</span>
                    <?php else: ?>
                        <span class="a-badge a-badge-active"><i class="fas fa-circle" style="font-size:0.4rem;"></i> Active</span>
                    <?php endif; ?>
                </td>

                <!-- Actions -->
                <td>
                    <form method="POST" style="display:flex;gap:0.4rem;">
                        <input type="hidden" name="driver_id" value="<?php echo $driver['id']; ?>">

                        <?php if ($driver['is_suspended'] == 1 || $driver['is_active'] == 0): ?>
                            <button type="submit" name="action" value="approve"
                                    class="a-btn a-btn-green a-btn-sm"
                                    onclick="return confirm('Approve and activate this driver?')">
                                <i class="fas fa-circle-check"></i> Approve
                            </button>
                        <?php endif; ?>

                        <?php if ($driver['is_active'] == 1 && $driver['is_suspended'] == 0): ?>
                            <button type="submit" name="action" value="suspend"
                                    class="a-btn a-btn-danger a-btn-sm"
                                    onclick="return confirm('Suspend this driver? They will be unable to accept rides.')">
                                <i class="fas fa-ban"></i> Suspend
                            </button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>