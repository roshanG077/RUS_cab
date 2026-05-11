<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit(); }

// Block / Unblock
if (isset($_POST['toggle_status'])) {
    $user_id    = mysqli_real_escape_string($conn, $_POST['user_id']);
    $cur_status = (int) $_POST['current_status'];
    $new_status = ($cur_status === 1) ? 0 : 1;
    mysqli_query($conn, "UPDATE users SET is_active='$new_status' WHERE id='$user_id'");
    header("Location: users.php?msg=User status updated.");
    exit();
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

include 'header.php';
?>

<div class="a-page-header">
    <h1>Passengers Directory</h1>
    <p>View, search, and moderate registered passenger accounts.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="a-alert a-alert-success">
        <i class="fas fa-circle-check"></i>
        <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>

<div class="a-panel">
    <div class="a-panel-header">
        <h3><i class="fas fa-users"></i> All Passengers</h3>
        <span style="font-size:0.75rem;color:var(--a-text-muted);">
            <?php echo mysqli_num_rows($users); ?> total accounts
        </span>
    </div>
    <div style="overflow-x:auto;">
        <table class="a-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td style="color:var(--a-text-muted);font-size:0.8rem;">#<?php echo $user['id']; ?></td>

                    <td>
                        <div style="display:flex;align-items:center;gap:0.6rem;">
                            <div style="width:30px;height:30px;border-radius:50%;background:var(--a-blue-bg);color:var(--a-blue);display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;flex-shrink:0;">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <strong style="font-size:0.85rem;color:var(--a-text);">
                                <?php echo htmlspecialchars($user['full_name']); ?>
                            </strong>
                        </div>
                    </td>

                    <td>
                        <div style="font-size:0.82rem;"><?php echo htmlspecialchars($user['email']); ?></div>
                        <div style="font-size:0.75rem;color:var(--a-text-muted);"><?php echo htmlspecialchars($user['phone']); ?></div>
                    </td>

                    <td style="font-size:0.8rem;color:var(--a-text-muted);">
                        <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                    </td>

                    <td>
                        <?php if ($user['is_active'] == 1): ?>
                            <span class="a-badge a-badge-active"><i class="fas fa-circle" style="font-size:0.4rem;"></i> Active</span>
                        <?php else: ?>
                            <span class="a-badge a-badge-blocked"><i class="fas fa-ban" style="font-size:0.6rem;"></i> Blocked</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id"        value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="current_status" value="<?php echo $user['is_active']; ?>">
                            <?php if ($user['is_active'] == 1): ?>
                                <button type="submit" name="toggle_status"
                                        class="a-btn a-btn-danger a-btn-sm"
                                        onclick="return confirm('Block this user? They will not be able to book rides.')">
                                    <i class="fas fa-ban"></i> Block
                                </button>
                            <?php else: ?>
                                <button type="submit" name="toggle_status"
                                        class="a-btn a-btn-green a-btn-sm">
                                    <i class="fas fa-circle-check"></i> Unblock
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