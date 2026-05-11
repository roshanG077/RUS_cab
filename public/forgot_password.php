<?php
require_once '../config/db.php';
$error   = "";
$success = "";

if (isset($_POST['reset_now'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $new_pass = $_POST['new_password'];

    $res = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' AND phone='$phone'");
    if (mysqli_num_rows($res) > 0) {
        mysqli_query($conn, "UPDATE users SET password='$new_pass' WHERE email='$email'");
        $success = "Password reset successfully.";
    } else {
        $error = "Verification failed. Email and phone number don't match our records.";
    }
}

$page_title = "Reset Password — RUS CAB";
include '../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-brand">
            <h1>RUS <span>CAB</span></h1>
            <p>Reset your account password</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
                <a href="login.php" class="auth-link">Sign in now</a>
            </div>
        <?php else: ?>

            <form method="POST" class="auth-form">

                <p class="auth-form-hint">
                    <i class="fas fa-info-circle"></i>
                    Enter your registered email and phone number to verify your identity.
                </p>

                <div class="form-group">
                    <label for="email">Registered Email</label>
                    <input type="email" id="email" name="email"
                           placeholder="you@example.com" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Registered Phone Number</label>
                    <input type="tel" id="phone" name="phone"
                           placeholder="+91 98765 43210" required
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <div class="form-divider-label">New Password</div>

                <div class="form-group">
                    <label for="new_password">Create New Password</label>
                    <div class="input-password-wrapper">
                        <input type="password" id="new_password" name="new_password"
                               placeholder="Minimum 6 characters" required>
                        <button type="button" class="toggle-password" aria-label="Show password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="reset_now" class="btn btn-full">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </form>

        <?php endif; ?>

        <div class="auth-footer">
            <p><a href="login.php" class="auth-link-muted">
                <i class="fas fa-arrow-left"></i> Back to Sign In
            </a></p>
        </div>

    </div>
</div>
