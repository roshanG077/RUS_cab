<?php
// 1. Safe Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. Database Connection
require_once '../config/db.php';

// 4. Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

// 5. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql    = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email address or password. Please try again.";
    }
}

$page_title = "Sign In — RUS CAB";
include '../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-brand">
            <h1>RUS <span>CAB</span></h1>
            <p>Sign in to book your next ride</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       placeholder="you@example.com" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-password-wrapper">
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password" required>
                    <button type="button" class="toggle-password" aria-label="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="auth-meta">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="forgot_password.php" class="auth-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div class="auth-divider">or</div>

        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php" class="auth-link">Create one here</a></p>
            <p class="mt-sm"><a href="../driver/index.php" class="auth-link-muted">Sign in as a Driver instead</a></p>
        </div>

    </div>
</div>

