<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit(); }

// Resolve ticket
if (isset($_POST['mark_resolved'])) {
    $complaint_id = mysqli_real_escape_string($conn, $_POST['complaint_id']);
    mysqli_query($conn, "UPDATE complaints SET status='Resolved' WHERE id='$complaint_id'");
    header("Location: complaints.php?msg=Ticket #$complaint_id marked as resolved.");
    exit();
}

// Fetch complaints — open first, then resolved
$complaints = mysqli_query($conn,
    "SELECT c.*,
            u.full_name AS user_name,  u.phone AS user_phone,
            d.first_name AS driver_name, d.phone AS driver_phone
     FROM complaints c
     LEFT JOIN users   u ON c.user_id   = u.id
     LEFT JOIN drivers d ON c.driver_id = d.id
     ORDER BY FIELD(c.status, 'Open', 'Resolved'), c.id DESC"
);

$total      = mysqli_num_rows($complaints);
$open_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM complaints WHERE status='Open'"))['c'] ?? 0;

include 'header.php';
?>

<div class="a-page-header">
    <h1>Customer Support Desk</h1>
    <p>Review and resolve complaints submitted by passengers and drivers.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="a-alert a-alert-success">
        <i class="fas fa-circle-check"></i>
        <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>

<?php if ($open_count > 0): ?>
    <div class="a-alert a-alert-danger" style="margin-bottom:var(--a-sp-md);">
        <i class="fas fa-triangle-exclamation"></i>
        <strong><?php echo $open_count; ?> open ticket<?php echo $open_count > 1 ? 's' : ''; ?></strong>
        awaiting resolution.
    </div>
<?php endif; ?>

<div class="a-panel">
    <div class="a-panel-header">
        <h3><i class="fas fa-headset"></i> All Tickets</h3>
        <div style="display:flex;align-items:center;gap:0.6rem;">
            <?php if ($open_count > 0): ?>
                <span class="a-badge a-badge-open"><?php echo $open_count; ?> open</span>
            <?php endif; ?>
            <span style="font-size:0.75rem;color:var(--a-text-muted);"><?php echo $total; ?> total</span>
        </div>
    </div>

    <?php if ($total > 0): ?>
    <div style="overflow-x:auto;">
        <table class="a-table">
            <thead>
                <tr>
                    <th style="width:10%;">Ticket</th>
                    <th style="width:20%;">Reported By</th>
                    <th style="width:42%;">Issue Details</th>
                    <th style="width:12%;">Status</th>
                    <th style="width:16%;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($complaints)): ?>
            <tr style="vertical-align:top;">

                <!-- Ticket ID + date -->
                <td>
                    <strong style="font-family:'Montserrat',sans-serif;font-size:0.88rem;color:var(--a-text);">
                        #<?php echo $row['id']; ?>
                    </strong><br>
                    <span style="font-size:0.72rem;color:var(--a-text-muted);">
                        <?php echo isset($row['created_at']) ? date('d M, Y', strtotime($row['created_at'])) : '—'; ?>
                    </span>
                </td>

                <!-- Reporter -->
                <td>
                    <?php if (!empty($row['user_id'])): ?>
                        <div style="display:flex;align-items:flex-start;gap:0.45rem;">
                            <div style="width:26px;height:26px;border-radius:50%;background:var(--a-blue-bg);color:var(--a-blue);display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:700;flex-shrink:0;margin-top:2px;">
                                U
                            </div>
                            <div>
                                <strong style="font-size:0.82rem;color:var(--a-blue);display:block;">
                                    <?php echo htmlspecialchars($row['user_name']); ?>
                                </strong>
                                <span style="font-size:0.72rem;color:var(--a-text-muted);">
                                    <?php echo htmlspecialchars($row['user_phone']); ?>
                                </span>
                            </div>
                        </div>
                    <?php elseif (!empty($row['driver_id'])): ?>
                        <div style="display:flex;align-items:flex-start;gap:0.45rem;">
                            <div style="width:26px;height:26px;border-radius:50%;background:var(--a-amber-bg);color:var(--a-amber-dark);display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:700;flex-shrink:0;margin-top:2px;">
                                D
                            </div>
                            <div>
                                <strong style="font-size:0.82rem;color:var(--a-amber-dark);display:block;">
                                    <?php echo htmlspecialchars($row['driver_name']); ?>
                                </strong>
                                <span style="font-size:0.72rem;color:var(--a-text-muted);">
                                    <?php echo htmlspecialchars($row['driver_phone']); ?>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <span style="font-size:0.82rem;color:var(--a-text-muted);">Guest / Unknown</span>
                    <?php endif; ?>
                </td>

                <!-- Issue details -->
                <td>
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem;flex-wrap:wrap;">
                        <strong style="font-size:0.83rem;color:var(--a-text);">
                            <?php echo htmlspecialchars($row['subject']); ?>
                        </strong>
                        <?php if (!empty($row['booking_id'])): ?>
                            <a href="rides.php"
                               style="font-size:0.65rem;background:var(--a-gray-bg);border:1px solid var(--a-border-dark);padding:0.1rem 0.45rem;border-radius:4px;color:var(--a-text-sub);font-weight:600;">
                                Ride #<?php echo $row['booking_id']; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="a-msg-box">
                        <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                    </div>
                </td>

                <!-- Status -->
                <td>
                    <span class="a-badge <?php echo $row['status'] === 'Open' ? 'a-badge-open' : 'a-badge-resolved'; ?>">
                        <?php echo $row['status']; ?>
                    </span>
                </td>

                <!-- Action -->
                <td>
                    <?php if ($row['status'] === 'Open'): ?>
                        <form method="POST">
                            <input type="hidden" name="complaint_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="mark_resolved"
                                    class="a-btn a-btn-green a-btn-sm"
                                    onclick="return confirm('Mark ticket #<?php echo $row['id']; ?> as resolved?')">
                                <i class="fas fa-check"></i> Resolve
                            </button>
                        </form>
                    <?php else: ?>
                        <span style="font-size:0.75rem;color:var(--a-green);font-weight:600;">
                            <i class="fas fa-check-double"></i> Handled
                        </span>
                    <?php endif; ?>
                </td>

            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
        <div class="a-empty">
            <i class="fas fa-inbox"></i>
            <strong>No complaints yet</strong>
            <span>Everything is running smoothly.</span>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>