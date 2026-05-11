<?php
// Safely start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Smart path detection ──────────────────────────────────────
// Determines which folder is currently active (public or driver)
// so asset paths and navigation links resolve correctly in both.
$current_folder = basename(dirname($_SERVER['PHP_SELF']));
$is_driver      = ($current_folder === 'driver');
$assets_path    = $is_driver ? '../public/assets' : 'assets';
$base_path      = $is_driver ? '../public/'       : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'RUS CAB - Premium Cab Booking'; ?></title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Global Stylesheet (resolves correctly for both folders) -->
    <link rel="stylesheet" href="<?php echo $assets_path; ?>/css/style.css">
</head>
<body>

<nav class="navbar" id="navbar">
    <div class="navbar-inner">

        <!-- Brand Logo -->
        <a href="<?php echo $base_path; ?>index.php" class="navbar-brand">
            <i class="fas fa-taxi"></i>
            RUS <span>CAB</span>
        </a>

        <!-- Desktop Navigation Links -->
        <ul class="navbar-links">
            <?php if ($is_driver): ?>
                <!-- Driver-specific navigation -->
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="earnings.php">Earnings</a></li>
                <li><a href="portfolio.php">Portfolio</a></li>
                <li><a href="support.php">Support</a></li>
            <?php else: ?>
                <!-- Public / customer navigation -->
                <li><a href="<?php echo $base_path; ?>index.php">Home</a></li>
                <li><a href="<?php echo $base_path; ?>book.php" class="nav-highlight">Book a Ride</a></li>
                <li><a href="<?php echo $base_path; ?>index.php#fleet">Our Fleet</a></li>
                <li><a href="<?php echo $base_path; ?>index.php#features">Services</a></li>
            <?php endif; ?>
        </ul>

        <!-- Desktop Auth Buttons -->
        <div class="navbar-actions">
            <?php if ($is_driver && isset($_SESSION['driver_id'])): ?>
                <!-- Logged-in driver -->
                <a href="profile.php" class="nav-user-pill">
                    <div class="nav-avatar"><i class="fas fa-id-badge"></i></div>
                    <span><?php echo isset($_SESSION['driver_name']) ? htmlspecialchars(explode(' ', $_SESSION['driver_name'])[0]) : 'Driver'; ?></span>
                </a>
                <a href="driver_logout.php" class="btn btn-outline btn-sm">Logout</a>

            <?php elseif (!$is_driver && isset($_SESSION['user_id'])): ?>
                <!-- Logged-in customer -->
                <a href="profile.php" class="nav-user-pill">
                    <div class="nav-avatar"><i class="fas fa-user"></i></div>
                    <span><?php echo isset($_SESSION['user_name']) ? htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) : 'User'; ?></span>
                </a>
                <a href="dashboard.php" class="btn btn-outline btn-sm">My Rides</a>
                <a href="logout.php" class="btn btn-outline btn-sm">Logout</a>

            <?php else: ?>
                <!-- Guest -->
                <a href="<?php echo $is_driver ? 'index.php' : 'login.php'; ?>" class="btn btn-outline">Sign In</a>
                <a href="<?php echo $is_driver ? 'register.php' : 'register.php'; ?>" class="btn btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>

        <!-- Mobile Hamburger -->
        <button class="hamburger" id="hamburger" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>
</nav>

<!-- Mobile Navigation Drawer -->
<div class="mobile-nav" id="mobileNav">
    <?php if ($is_driver): ?>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="earnings.php"><i class="fas fa-wallet"></i> Earnings</a>
        <a href="portfolio.php"><i class="fas fa-briefcase"></i> Portfolio</a>
        <a href="support.php"><i class="fas fa-headset"></i> Support</a>
        <?php if(isset($_SESSION['driver_id'])): ?>
            <a href="driver_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="index.php"><i class="fas fa-sign-in-alt"></i> Sign In</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="<?php echo $base_path; ?>index.php"><i class="fas fa-home"></i> Home</a>
        <a href="<?php echo $base_path; ?>book.php"><i class="fas fa-car"></i> Book a Ride</a>
        <a href="<?php echo $base_path; ?>index.php#fleet"><i class="fas fa-taxi"></i> Our Fleet</a>
        <a href="<?php echo $base_path; ?>index.php#features"><i class="fas fa-star"></i> Services</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php"><i class="fas fa-list"></i> My Rides</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Sign In</a>
            <a href="register.php"><i class="fas fa-user-plus"></i> Sign Up</a>
        <?php endif; ?>
    <?php endif; ?>
</div>