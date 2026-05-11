<?php
if (session_status() === PHP_SESSION_NONE) session_start();
ini_set('display_errors', 1); error_reporting(E_ALL);
require_once '../config/db.php';

if (isset($_SESSION['driver_id'])) { header("Location: dashboard.php"); exit(); }

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fname    = mysqli_real_escape_string($conn, trim($_POST['fname']));
    $lname    = mysqli_real_escape_string($conn, trim($_POST['lname']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $license  = mysqli_real_escape_string($conn, trim($_POST['license']));
    $vtype    = mysqli_real_escape_string($conn, $_POST['vehicle_type']);
    $pass     = mysqli_real_escape_string($conn, $_POST['password']);
    $cpass    = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($pass !== $cpass) {
        $error = "Passwords do not match.";
    } else {
        $exists = mysqli_query($conn, "SELECT id FROM drivers WHERE email='$email' OR license_no='$license'");
        if (mysqli_num_rows($exists) > 0) {
            $error = "This email or driving license is already registered.";
        } else {
            $sql = "INSERT INTO drivers (first_name, last_name, email, phone, license_no, vehicle_type, password)
                    VALUES ('$fname','$lname','$email','$phone','$license','$vtype','$pass')";
            if (mysqli_query($conn, $sql)) {
                $success = "Registration successful! You can now sign in.";
            } else {
                $error = "Database error: " . mysqli_error($conn);
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
    <title>Driver Registration — RUS CAB</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../driver/assets/css/style.css">
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
            Start Earning<br><span>Today.</span>
        </h1>
        <p class="auth-panel-sub">
            Complete your application in under 3 minutes.
            Verified drivers get their first ride assignment within 24 hours.
        </p>
        <div class="auth-panel-stats">
            <div class="auth-stat">
                <strong>₹850</strong>
                <span>Avg / Day</span>
            </div>
            <div class="auth-stat">
                <strong>Mon</strong>
                <span>Payouts</span>
            </div>
            <div class="auth-stat">
                <strong>0%</strong>
                <span>Joining Fee</span>
            </div>
        </div>
    </div>

    <!-- Right: Registration form -->
    <div class="driver-auth-form-area">
        <div class="driver-auth-box" style="max-width:520px;">

            <h2>Create Driver Account</h2>
            <p class="auth-tagline">Fill in your details below to apply as a RUS CAB partner driver.</p>

            <?php if (!empty($error)): ?>
                <div class="d-alert d-alert-danger">
                    <i class="fas fa-circle-exclamation"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="d-alert d-alert-success">
                    <i class="fas fa-circle-check"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <a href="index.php" class="d-auth-link" style="margin-left:0.5rem;">Sign in now →</a>
                </div>
            <?php else: ?>

            <form action="register.php" method="POST" class="d-auth-form">

                <div class="d-form-grid">
                    <div class="d-form-group">
                        <label>First Name</label>
                        <input type="text" name="fname" class="d-input" placeholder="ramesh" required
                               value="<?php echo isset($_POST['fname']) ? htmlspecialchars($_POST['fname']) : ''; ?>">
                    </div>
                    <div class="d-form-group">
                        <label>Last Name</label>
                        <input type="text" name="lname" class="d-input" placeholder="tiwari" required
                               value="<?php echo isset($_POST['lname']) ? htmlspecialchars($_POST['lname']) : ''; ?>">
                    </div>
                    <div class="d-form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="d-input" placeholder="rmtiwari@example.com" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="d-form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="d-input" placeholder="+91 98765 43210" required
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    <div class="d-form-group">
                        <label>Driving License No.</label>
                        <input type="text" name="license" class="d-input" placeholder="GJ-05-XXXXXXX" required
                               value="<?php echo isset($_POST['license']) ? htmlspecialchars($_POST['license']) : ''; ?>">
                    </div>
                    <div class="d-form-group">
                        <label>Vehicle Type</label>
                        <select name="vehicle_type" class="d-select" required>
                            <option value="">Select vehicle...</option>
                            <option value="Sedan (4 Seats)"   <?php echo (($_POST['vehicle_type'] ?? '') === 'Sedan (4 Seats)') ? 'selected' : ''; ?>>Sedan — 4 Seats</option>
                            <option value="SUV (6 Seats)"     <?php echo (($_POST['vehicle_type'] ?? '') === 'SUV (6 Seats)') ? 'selected' : ''; ?>>SUV — 6 Seats</option>
                            <option value="Luxury (4 Seats)"  <?php echo (($_POST['vehicle_type'] ?? '') === 'Luxury (4 Seats)') ? 'selected' : ''; ?>>Luxury — 4 Seats</option>
                            <option value="Mini Van (7 Seats)" <?php echo (($_POST['vehicle_type'] ?? '') === 'Mini Van (7 Seats)') ? 'selected' : ''; ?>>Mini Van — 7 Seats</option>
                        </select>
                    </div>
                    <div class="d-form-group">
                        <label>Password</label>
                        <div class="d-password-wrap">
                            <input type="password" id="reg_pass" name="password" class="d-input"
                                   placeholder="Minimum 6 characters" required>
                            <button type="button" class="d-eye-btn" data-target="reg_pass">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-form-group">
                        <label>Confirm Password</label>
                        <div class="d-password-wrap">
                            <input type="password" id="reg_cpass" name="confirm_password" class="d-input"
                                   placeholder="Repeat password" required>
                            <button type="button" class="d-eye-btn" data-target="reg_cpass">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="d-btn d-btn-primary d-btn-full" style="margin-top:var(--d-sp-md);">
                    <i class="fas fa-id-card"></i> Create Driver Account
                </button>
            </form>

            <?php endif; ?>

            <div class="d-auth-footer">
                <p>Already registered? <a href="index.php" class="d-auth-link">Sign in here</a></p>
            </div>
        </div>
    </div>
</div>

<script src="../driver/assets/js/driver.js"></script>
</body>
</html>