<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit(); }

// Commission update
if (isset($_POST['update_commission'])) {
    $new_rate = mysqli_real_escape_string($conn, $_POST['commission_rate']);
    mysqli_query($conn, "UPDATE settings SET commission_rate='$new_rate'");
    header("Location: earnings.php?msg=Commission rate updated to $new_rate%.");
    exit();
}

// Mark driver paid
if (isset($_POST['mark_paid'])) {
    $driver_id = mysqli_real_escape_string($conn, $_POST['driver_id']);
    mysqli_query($conn, "UPDATE drivers SET wallet_balance=0.00 WHERE id='$driver_id'");
    header("Location: earnings.php?msg=Driver payout marked as completed.");
    exit();
}

// Data
$setting_q    = mysqli_query($conn, "SELECT commission_rate FROM settings LIMIT 1");
$commission_rate = ($setting_q && mysqli_num_rows($setting_q) > 0)
    ? mysqli_fetch_assoc($setting_q)['commission_rate']
    : 15;

$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(fare) AS t FROM bookings WHERE status='Completed'"))['t'] ?? 0;
$company_cut   = ($total_revenue * $commission_rate) / 100;
$driver_cut    = $total_revenue - $company_cut;

$payouts = mysqli_query($conn, "SELECT id, first_name, last_name, bank_account, wallet_balance FROM drivers WHERE wallet_balance>0 ORDER BY wallet_balance DESC");
$payout_count = mysqli_num_rows($payouts);

include 'header.php';
?>

<div class="a-page-header">
    <h1>Financial Control Center</h1>
    <p>Set platform commission, view the revenue split, and process driver payouts.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="a-alert a-alert-success">
        <i class="fas fa-circle-check"></i>
        <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>

<div class="a-grid-2r">

    <!-- Left column: settings + revenue split -->
    <div style="display:flex;flex-direction:column;gap:var(--a-sp-md);">

        <!-- Commission settings -->
        <div class="a-panel">
            <div class="a-panel-header">
                <h4><i class="fas fa-sliders"></i> Commission Settings</h4>
            </div>
            <div class="a-panel-body">
                <form method="POST">
                    <div class="a-form-group">
                        <label>Platform Fee Percentage (%)</label>
                        <input type="number" step="0.01" min="0" max="100"
                               name="commission_rate" class="a-input"
                               value="<?php echo $commission_rate; ?>" required>
                        <span class="a-form-hint">Applied to all completed ride fares when calculating company profit.</span>
                    </div>
                    <button type="submit" name="update_commission" class="a-btn a-btn-primary a-btn-full">
                        <i class="fas fa-floppy-disk"></i> Save Commission Rate
                    </button>
                </form>
            </div>
        </div>

        <!-- Revenue split -->
        <div class="a-panel">
            <div class="a-panel-header">
                <h4><i class="fas fa-chart-pie"></i> Revenue Split</h4>
                <span style="font-size:0.72rem;color:var(--a-text-muted);">Based on completed rides</span>
            </div>
            <div class="a-panel-body">
                <div class="a-stat-box teal">
                    <small>Total Platform Revenue</small>
                    <strong>₹<?php echo number_format($total_revenue, 2); ?></strong>
                </div>
                <div class="a-stat-box green">
                    <small>Company Net Profit (<?php echo $commission_rate; ?>%)</small>
                    <strong>₹<?php echo number_format($company_cut, 2); ?></strong>
                </div>
                <div class="a-stat-box amber">
                    <small>Total Driver Earnings (<?php echo 100 - $commission_rate; ?>%)</small>
                    <strong>₹<?php echo number_format($driver_cut, 2); ?></strong>
                </div>
            </div>
        </div>

    </div>

    <!-- Right column: pending payouts -->
    <div class="a-panel">
        <div class="a-panel-header">
            <h4><i class="fas fa-money-check-dollar"></i> Pending Driver Payouts</h4>
            <?php if ($payout_count > 0): ?>
                <span class="a-badge a-badge-open"><?php echo $payout_count; ?> pending</span>
            <?php endif; ?>
        </div>
        <div>
            <div style="padding:var(--a-sp-sm) var(--a-sp-md);border-bottom:1px solid var(--a-border);font-size:0.8rem;color:var(--a-text-muted);">
                Drivers with a positive wallet balance awaiting bank transfer.
            </div>

            <?php if ($payout_count > 0): ?>
                <div style="overflow-x:auto;">
                    <table class="a-table">
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Bank Account</th>
                                <th>Amount Owed</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($pay = mysqli_fetch_assoc($payouts)): ?>
                        <tr>
                            <td>
                                <div class="a-driver-cell">
                                    <strong>#<?php echo $pay['id']; ?> — <?php echo htmlspecialchars($pay['first_name'] . ' ' . $pay['last_name']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <span style="font-size:0.8rem;color:var(--a-text-muted);font-family:'Montserrat',sans-serif;">
                                    <?php echo !empty($pay['bank_account'])
                                        ? 'XXXX XXXX ' . substr($pay['bank_account'], -4)
                                        : '<em style="color:var(--a-red);">Not provided</em>'; ?>
                                </span>
                            </td>
                            <td>
                                <strong style="font-family:'Montserrat',sans-serif;font-size:1rem;color:var(--a-red);">
                                    ₹<?php echo number_format($pay['wallet_balance'], 2); ?>
                                </strong>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="driver_id" value="<?php echo $pay['id']; ?>">
                                    <button type="submit" name="mark_paid"
                                            class="a-btn a-btn-green a-btn-sm"
                                            onclick="return confirm('Confirm transfer of ₹<?php echo $pay['wallet_balance']; ?> to this driver?')">
                                        <i class="fas fa-circle-check"></i> Mark Paid
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="a-settled">
                    <i class="fas fa-circle-check"></i>
                    <strong>All payouts settled</strong>
                    <span style="font-size:0.82rem;">No drivers currently have a pending wallet balance.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>