<?php
/* driver/header.php
   Shared shell for all authenticated driver pages.
   Requires: $_SESSION['driver_id'], $_SESSION['driver_name']
   Provides: $current_page, $driver_initials
*/
$current_page = basename($_SERVER['PHP_SELF']);
$driver_name  = $_SESSION['driver_name'] ?? 'Driver';
$name_parts   = explode(' ', trim($driver_name));
$driver_initials = strtoupper(
    ($name_parts[0][0] ?? '') . ($name_parts[1][0] ?? '')
);

$page_titles = [
    'dashboard.php'  => 'Live Dashboard',
    'earnings.php'   => 'Earnings & Wallet',
    'portfolio.php'  => 'My Portfolio',
    'profile.php'    => 'Profile Settings',
    'support.php'    => 'Support Center',
];

$topbar_title = $page_titles[$current_page] ?? 'Driver Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $topbar_title; ?> — RUS CAB Driver</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../driver/assets/css/style.css">
</head>
<body>
<div class="driver-app">

    <!-- ── Sidebar ── -->
    <aside class="driver-sidebar">

        <!-- Brand -->
        <div class="driver-sidebar-brand">
            <div class="brand-icon"><i class="fas fa-taxi"></i></div>
            <div>
                <div class="brand-text">RUS <span style="color:var(--d-green)">CAB</span></div>
                <div class="brand-sub">Driver Portal</div>
            </div>
        </div>

        <!-- Driver pill -->
        <div class="sidebar-driver-pill">
            <div class="sidebar-driver-avatar"><?php echo $driver_initials; ?></div>
            <div class="sidebar-driver-info">
                <strong><?php echo htmlspecialchars($driver_name); ?></strong>
                <span>Online</span>
            </div>
            <div class="online-dot"></div>
        </div>

        <!-- Main nav -->
        <div class="sidebar-section-label">Main</div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"
               class="sidebar-link <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-grip-vertical"></i> Dashboard
            </a>
            <a href="earnings.php"
               class="sidebar-link <?php echo ($current_page === 'earnings.php') ? 'active' : ''; ?>">
                <i class="fas fa-wallet"></i> Earnings
            </a>
            <a href="portfolio.php"
               class="sidebar-link <?php echo ($current_page === 'portfolio.php') ? 'active' : ''; ?>">
                <i class="fas fa-id-badge"></i> Portfolio
            </a>
        </nav>

        <div class="sidebar-section-label">Account</div>
        <nav class="sidebar-nav">
            <a href="profile.php"
               class="sidebar-link <?php echo ($current_page === 'profile.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i> Profile
            </a>
            <a href="support.php"
               class="sidebar-link <?php echo ($current_page === 'support.php') ? 'active' : ''; ?>">
                <i class="fas fa-headset"></i> Support
            </a>
        </nav>

        <!-- Logout -->
        <div class="sidebar-bottom">
            <nav class="sidebar-nav">
                <a href="../driver/driver_logout.php" class="sidebar-link danger">
                    <i class="fas fa-arrow-right-from-bracket"></i> Sign Out
                </a>
            </nav>
        </div>

    </aside>

    <!-- ── Main area ── -->
    <div class="driver-main">

        <!-- Top bar -->
        <div class="driver-topbar">
            <div class="topbar-title"><?php echo $topbar_title; ?></div>
            <div class="topbar-right">
                <div class="topbar-badge">
                    <i class="fas fa-circle"></i> Online
                </div>
                <a href="profile.php" style="color:var(--d-text-muted); font-size:0.82rem; display:flex; align-items:center; gap:0.4rem;">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars(explode(' ', $driver_name)[0]); ?>
                </a>
            </div>
        </div>

        <!-- Content body starts here — closed in footer.php -->
        <div class="driver-body"></div>