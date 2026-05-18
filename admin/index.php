<?php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['admin_id'])) { header("Location: dashboard.php"); exit(); }

// ── Auto-seed: ensure admin account exists in DB ──────────
$count_res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM admins");
if ($count_res) {
    $cnt = mysqli_fetch_assoc($count_res)['cnt'];
    if ($cnt == 0) {
        mysqli_query($conn, "INSERT INTO admins (username, email, password) 
                             VALUES ('Admin', 'admin@ruscab.com', 'admin123')");
    }
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    $sql    = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials. Use: Admin / admin123";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - RUS CAB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-login-screen">

        <!-- Left: Branding Panel -->
        <div class="admin-login-panel">
            <div class="login-panel-logo">
                <div class="login-panel-icon"><i class="fas fa-taxi"></i></div>
                <div class="login-panel-text">RUS <span>CAB</span></div>
            </div>
            <h1 class="login-panel-headline">
                Administrative<span>Console.</span>
            </h1>
            <p class="login-panel-sub">
                Monitor real-time rides, approve driver fleet, moderate registered accounts, and manage commission rates.
            </p>
            <div class="login-panel-stats">
                <div class="login-stat">
                    <strong>24/7</strong>
                    <span>Support</span>
                </div>
                <div class="login-stat">
                    <strong>Admin</strong>
                    <span>Access</span>
                </div>
                <div class="login-stat">
                    <strong>Secured</strong>
                    <span>SSL</span>
                </div>
            </div>
        </div>

        <!-- Right: Login Form Area -->
        <div class="admin-login-form-area">
            <div class="admin-login-box">

                <h2>Sign In to Console</h2>
                <p class="login-tagline">Enter your credentials below to access the admin portal.</p>

                <?php if (!empty($error)): ?>
                    <div class="a-alert a-alert-danger" style="margin-bottom:var(--a-sp-md);">
                        <i class="fas fa-circle-exclamation"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="a-form">
                    <div class="a-form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="a-input" placeholder="Admin" required value="Admin">
                    </div>
                    <div class="a-form-group">
                        <label>Password</label>
                        <div class="a-pw-wrap">
                            <input type="password" id="admin_pw" name="password" class="a-input" placeholder="admin123" required>
                            <button type="button" class="a-eye-btn" data-target="admin_pw"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <button type="submit" class="a-btn a-btn-primary a-btn-full" style="margin-top:var(--a-sp-md);">
                        <i class="fas fa-arrow-right-to-bracket"></i> Login to Console
                    </button>
                </form>

            </div>
        </div>

    </div>

    <!-- Interactions -->
    <script src="assets/js/admin.js"></script>
</body>
</html>