<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id    = $_SESSION['user_id'];
$alert      = "";

// ── Handle Profile Update ─────────────────────────────────
if (isset($_POST['update_profile'])) {
    $name  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    if (mysqli_query($conn, "UPDATE users SET full_name='$name', phone='$phone' WHERE id='$user_id'")) {
        $_SESSION['user_name'] = $name;
        $alert = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Profile updated successfully.</div>';
    }
}

// ── Handle Password Change ────────────────────────────────
if (isset($_POST['change_password'])) {
    $old_pass  = $_POST['old_password'];
    $new_pass  = $_POST['new_password'];
    $conf_pass = $_POST['confirm_password'];
    $user_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE id='$user_id'"));

    if ($old_pass !== $user_data['password']) {
        $alert = '<div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> Current password is incorrect.</div>';
    } elseif ($new_pass !== $conf_pass) {
        $alert = '<div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> New passwords do not match.</div>';
    } else {
        mysqli_query($conn, "UPDATE users SET password='$new_pass' WHERE id='$user_id'");
        $alert = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Password updated successfully.</div>';
    }
}

$user       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));
$initials   = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', trim($user['full_name'])))));
$page_title = "My Profile — RUS CAB";
include '../includes/header.php';
?>

<div class="page-main">
    <div class="container">

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="avatar"><?php echo htmlspecialchars(substr($initials, 0, 2)); ?></div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <a href="dashboard.php" class="btn btn-outline btn-sm">
                <i class="fas fa-list"></i> My Rides
            </a>
        </div>

        <?php if ($alert) echo $alert; ?>

        <div class="profile-grid">

            <!-- Personal Details -->
            <div class="profile-form-card">
                <h3><i class="fas fa-user-circle"></i> Personal Details</h3>

                <form action="" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name"
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                               readonly class="input-readonly">
                        <small class="form-hint">Email cannot be changed for security reasons.</small>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                               value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-full">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <!-- Password -->
            <div class="profile-form-card">
                <h3><i class="fas fa-shield-halved"></i> Login Security</h3>

                <form action="" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="old_password">Current Password</label>
                        <div class="input-password-wrapper">
                            <input type="password" id="old_password" name="old_password"
                                   placeholder="Enter current password" required>
                            <button type="button" class="toggle-password" aria-label="Show password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="input-password-wrapper">
                            <input type="password" id="new_password" name="new_password"
                                   placeholder="Minimum 6 characters" required>
                            <button type="button" class="toggle-password" aria-label="Show password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="input-password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password"
                                   placeholder="Repeat new password" required>
                            <button type="button" class="toggle-password" aria-label="Show password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-outline btn-full-outline">
                        <i class="fas fa-lock"></i> Update Password
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>