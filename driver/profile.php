<?php
if (session_status() === PHP_SESSION_NONE) session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once '../config/db.php';

if (!isset($_SESSION['driver_id'])) { 
    header("Location: index.php?msg=Please login first"); 
    exit(); 
}

$driver_id = $_SESSION['driver_id'];
$alert = "";

// ─── Handle Form Submissions ──────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. PROFILE UPDATE HANDLER
    if (isset($_POST['update_profile'])) {
        $phone  = mysqli_real_escape_string($conn, $_POST['phone']);
        $email  = mysqli_real_escape_string($conn, $_POST['email']);
        $city   = mysqli_real_escape_string($conn, $_POST['city']);
        $color  = mysqli_real_escape_string($conn, $_POST['car_color'] ?? '');
        $plate  = mysqli_real_escape_string($conn, $_POST['license_no']);
        $bank   = mysqli_real_escape_string($conn, $_POST['bank_account']);

        // Photo upload with basic security check
        $photo = $_POST['old_photo'] ?? 'default.png';
        if (!empty($_FILES['profile_photo']['name']) && $_FILES['profile_photo']['error'] === 0) {
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            $file_ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed_exts)) {
                $target = "../assets/img/drivers/";
                if (!is_dir($target)) mkdir($target, 0777, true);
                
                $photo = time() . '_' . uniqid() . '.' . $file_ext;
                move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target . $photo);
            } else {
                $alert = '<div class="d-alert d-alert-warning"><i class="fas fa-triangle-exclamation"></i> Invalid image format. Allowed: JPG, PNG, WEBP.</div>';
            }
        }

        if (empty($alert)) {
            $sql = "UPDATE drivers SET 
                        phone='$phone', email='$email', city='$city', 
                        car_color='$color', license_no='$plate', 
                        bank_account='$bank', profile_photo='$photo' 
                    WHERE id='$driver_id'";

            if (mysqli_query($conn, $sql)) {
                $alert = '<div class="d-alert d-alert-success"><i class="fas fa-circle-check"></i> Profile updated successfully.</div>';
            } else {
                $alert = '<div class="d-alert d-alert-danger"><i class="fas fa-circle-exclamation"></i> Update failed: ' . mysqli_error($conn) . '</div>';
            }
        }
    }

    // 2. PASSWORD UPDATE HANDLER
    if (isset($_POST['change_password'])) {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];
        $cfm = $_POST['confirm_new'];
        
        $db_pass_query = mysqli_query($conn, "SELECT password FROM drivers WHERE id='$driver_id'");
        $db_pass = mysqli_fetch_assoc($db_pass_query)['password'];
        
        if ($old !== $db_pass) { // Note: If using password_hash(), use password_verify() here instead
            $alert = '<div class="d-alert d-alert-danger"><i class="fas fa-circle-exclamation"></i> Current password is incorrect.</div>';
        } elseif ($new !== $cfm) {
            $alert = '<div class="d-alert d-alert-danger"><i class="fas fa-circle-exclamation"></i> New passwords do not match.</div>';
        } elseif (strlen($new) < 6) {
            $alert = '<div class="d-alert d-alert-warning"><i class="fas fa-triangle-exclamation"></i> Password must be at least 6 characters.</div>';
        } else {
            $new_esc = mysqli_real_escape_string($conn, $new); // Note: Better to use password_hash()
            mysqli_query($conn, "UPDATE drivers SET password='$new_esc' WHERE id='$driver_id'");
            $alert = '<div class="d-alert d-alert-success"><i class="fas fa-shield-check"></i> Password updated successfully.</div>';
        }
    }
}

// ─── Fetch Fresh Data for Display ──────────────────────────────────
$driver     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM drivers WHERE id='$driver_id'"));
$completed  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM bookings WHERE driver_id='$driver_id' AND status='Completed'"))['c'] ?? 0;

$masked_bank = !empty($driver['bank_account']) ? 'XXXX XXXX ' . substr($driver['bank_account'], -4) : 'Not set';

$first_name = $driver['first_name'] ?? 'Driver';
$last_name  = $driver['last_name'] ?? '';
$initials   = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));

// Check if a valid profile photo exists
$photo_file = $driver['profile_photo'] ?? '';
$photo_path = "../assets/img/drivers/" . $photo_file;
$has_photo  = (!empty($photo_file) && $photo_file !== 'default.png' && file_exists($photo_path));

include '../driver/includes/header.php';
?>

<div class="page-header">
    <h1>Profile Settings</h1>
    <p>Update your professional details, vehicle info, and payout account.</p>
</div>

<?php echo $alert; ?>

<div class="profile-layout">

    <div class="profile-info-card">
        <div class="profile-avatar-large" style="overflow: hidden;">
            <?php if ($has_photo): ?>
                <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <?php echo htmlspecialchars($initials); ?>
            <?php endif; ?>
        </div>
        
        <h3><?php echo htmlspecialchars(trim($first_name . ' ' . $last_name)); ?></h3>
        <p><?php echo htmlspecialchars($driver['email']); ?></p>

        <div class="wallet-chip">
            <small>Wallet Balance</small>
            <strong>₹<?php echo number_format($driver['wallet_balance'] ?? 0, 2); ?></strong>
        </div>

        <div class="profile-stat-list">
            <div class="profile-stat-row">
                <span>Rating</span>
                <strong class="text-amber"><?php echo number_format($driver['rating'] ?? 5.0, 1); ?> ★</strong>
            </div>
            <div class="profile-stat-row">
                <span>Total Rides</span>
                <strong><?php echo $completed; ?></strong>
            </div>
            <div class="profile-stat-row">
                <span>Vehicle</span>
                <strong><?php echo htmlspecialchars($driver['vehicle_type'] ?? 'N/A'); ?></strong>
            </div>
            <div class="profile-stat-row">
                <span>Plate</span>
                <strong><?php echo htmlspecialchars($driver['license_no'] ?? 'N/A'); ?></strong>
            </div>
            <div class="profile-stat-row">
                <span>City</span>
                <strong><?php echo htmlspecialchars($driver['city'] ?? 'Surat'); ?></strong>
            </div>
            <div class="profile-stat-row">
                <span>Bank</span>
                <strong class="mono"><?php echo htmlspecialchars($masked_bank); ?></strong>
            </div>
            <div class="profile-stat-row">
                <span>Status</span>
                <strong class="<?php echo !empty($driver['is_active']) ? 'text-green' : 'text-danger'; ?>">
                    <?php echo !empty($driver['is_active']) ? 'Active' : 'Inactive'; ?>
                </strong>
            </div>
        </div>
    </div>

    <div>
        
        <div class="d-card">
            <div class="d-card-header">
                <h3><i class="fas fa-user-pen"></i> Update Professional Details</h3>
            </div>
            <div class="d-card-body">
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="old_photo" value="<?php echo htmlspecialchars($driver['profile_photo'] ?? 'default.png'); ?>">

                    <div class="d-form-grid">
                        <div class="d-form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" class="d-input" value="<?php echo htmlspecialchars($driver['phone'] ?? ''); ?>" required>
                        </div>
                        <div class="d-form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="d-input" value="<?php echo htmlspecialchars($driver['email'] ?? ''); ?>" required>
                        </div>
                        <div class="d-form-group">
                            <label>City / Service Area</label>
                            <input type="text" name="city" class="d-input" value="<?php echo htmlspecialchars($driver['city'] ?? ''); ?>" required>
                        </div>
                        <div class="d-form-group">
                            <label>Car Color</label>
                            <input type="text" name="car_color" class="d-input" placeholder="e.g. White" value="<?php echo htmlspecialchars($driver['car_color'] ?? ''); ?>">
                        </div>
                        <div class="d-form-group">
                            <label>License Plate Number</label>
                            <input type="text" name="license_no" class="d-input" value="<?php echo htmlspecialchars($driver['license_no'] ?? ''); ?>" required>
                        </div>
                        <div class="d-form-group">
                            <label>Profile Photo</label>
                            <input type="file" name="profile_photo" class="d-input" accept="image/*" style="padding:0.45rem 0.85rem; cursor:pointer;">
                        </div>
                        <div class="d-form-group span-2">
                            <label>Bank Account Number <span class="d-form-hint">(For weekly payouts)</span></label>
                            <input type="text" name="bank_account" class="d-input" placeholder="Enter full account number" value="<?php echo htmlspecialchars($driver['bank_account'] ?? ''); ?>">
                            <span class="d-form-hint" style="margin-top:0.3rem;">
                                Payouts are processed every Monday to your linked account.
                            </span>
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="d-btn d-btn-primary d-btn-full" style="margin-top:var(--d-sp-md);">
                        <i class="fas fa-floppy-disk"></i> Save Profile
                    </button>
                </form>
            </div>
        </div>

        <div class="d-card" style="margin-top:var(--d-sp-md);">
            <div class="d-card-header">
                <h3><i class="fas fa-shield-halved"></i> Change Password</h3>
            </div>
            <div class="d-card-body">
                <form action="profile.php" method="POST" class="d-form-grid">
                    <div class="d-form-group">
                        <label>Current Password</label>
                        <div class="d-password-wrap">
                            <input type="password" id="old_pw" name="old_password" class="d-input" placeholder="Enter current password" required>
                            <button type="button" class="d-eye-btn" data-target="old_pw"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="d-form-group">
                        <label>New Password</label>
                        <div class="d-password-wrap">
                            <input type="password" id="new_pw" name="new_password" class="d-input" placeholder="Minimum 6 characters" required>
                            <button type="button" class="d-eye-btn" data-target="new_pw"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="d-form-group span-2">
                        <label>Confirm New Password</label>
                        <div class="d-password-wrap">
                            <input type="password" id="cfm_pw" name="confirm_new" class="d-input" placeholder="Repeat new password" required>
                            <button type="button" class="d-eye-btn" data-target="cfm_pw"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="d-form-group span-2">
                        <button type="submit" name="change_password" class="d-btn d-btn-outline d-btn-full">
                            <i class="fas fa-lock"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>

<script src="../public/assets/js/driver.js"></script>

<?php include '../driver/includes/footer.php'; ?>