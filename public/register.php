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
$success = "";

// 5. Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
        if ($check_email && mysqli_num_rows($check_email) > 0) {
            $error = "Email is already registered. Please login.";
        } else {
            // Note: In a real project, always use password_hash() for security!
            $sql = "INSERT INTO users (full_name, email, phone, password) VALUES ('$fname', '$email', '$phone', '$password')";
            if (mysqli_query($conn, $sql)) {
                $success = "Account created successfully! You can now login.";
            } else {
                $error = "Database Error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RUS CAB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <nav class="navbar">
        <div class="navbar-inner">
            <div class="navbar-brand">
                <a href="../public/index.php"><i class="fas fa-taxi"></i> RUS <span>CAB</span></a>
            </div>
        </div>
    </nav>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-brand">
                <h1>RUS <span>CAB</span></h1>
                <p>Register to start booking rides</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger mb-md">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="alert alert-success mb-md">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fname" placeholder="John Doe" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" placeholder="+91 ..." required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="name@example.com" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full mt-md">Create Account</button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php" class="auth-link">Log in here</a>
            </div>
        </div>
    </div>

</body>
</html>