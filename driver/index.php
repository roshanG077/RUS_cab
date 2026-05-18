<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/db.php';

if (isset($_SESSION['driver_id'])) { header("Location: dashboard.php"); exit(); }

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $result = mysqli_query($conn, "SELECT * FROM drivers WHERE email='$email' AND password='$password'");
        if ($result && mysqli_num_rows($result) > 0) {
            $driver = mysqli_fetch_assoc($result);
            $_SESSION['driver_id']   = $driver['id'];
            $_SESSION['driver_name'] = $driver['first_name'] . ' ' . $driver['last_name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Sign In — RUS CAB</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../driver/assets/css/style.css?v=2.1">
</head>
<body>

<div class="driver-auth-screen">

    <!-- Left panel -->
    <div class="driver-auth-panel">
        <div class="auth-panel-logo">
            <div class="auth-panel-logo-icon"><i class="fas fa-taxi"></i></div>
            <div class="auth-panel-logo-text">RUS <span>CAB</span></div>
        </div>
        <h1 class="auth-panel-headline">
            Drive Smart.<br><span>Earn More.</span>
        </h1>
        <p class="auth-panel-sub">
            Join 500+ verified drivers earning steady income across Surat.
            Your schedule, your rules — powered by RUS CAB.
        </p>
        <div class="auth-panel-stats">
            <div class="auth-stat">
                <strong>500+</strong>
                <span>Drivers</span>
            </div>
            <div class="auth-stat">
                <strong>₹850</strong>
                <span>Avg / Day</span>
            </div>
            <div class="auth-stat">
                <strong>24/7</strong>
                <span>Support</span>
            </div>
        </div>
    </div>

    <!-- Right: form -->
    <div class="driver-auth-form-area">
        <div class="driver-auth-box">

            <h2>Welcome back</h2>
            <p class="auth-tagline">Sign in to your driver account to continue.</p>

            <?php if (isset($_GET['msg'])): ?>
                <div class="d-alert d-alert-warning">
                    <i class="fas fa-triangle-exclamation"></i>
                    <?php echo htmlspecialchars($_GET['msg']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="d-alert d-alert-danger">
                    <i class="fas fa-circle-exclamation"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST" class="d-auth-form">
                <div class="d-form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="d-input"
                           placeholder="driver@ruscab.com" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="d-form-group">
                    <label for="password">Password</label>
                    <div class="d-password-wrap">
                        <input type="password" id="password" name="password" class="d-input"
                               placeholder="••••••••" required>
                        <button type="button" class="d-eye-btn" data-target="password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="d-btn d-btn-primary d-btn-full">
                    <i class="fas fa-arrow-right-to-bracket"></i> Sign In to Portal
                </button>
            </form>

            <div class="d-auth-footer">
                <p>New driver? <a href="register.php" class="d-auth-link">Apply to drive</a></p>
                <p>
                    <a href="../public/index.php" class="d-back-link">
                        <i class="fas fa-arrow-left"></i> Back to public site
                    </a>
                </p>
            </div>

        </div>
    </div>
</div>

<script src="../driver/assets/js/driver.js"></script>
</body>
</html>