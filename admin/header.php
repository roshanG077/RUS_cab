<?php
/* admin/header.php
   Shared shell for all authenticated admin pages.
   Requires: $_SESSION['admin_id'], $_SESSION['admin_name']
*/
$current_page = basename($_SERVER['PHP_SELF']);
$admin_name   = $_SESSION['admin_name'] ?? 'Admin';
$initials     = strtoupper(substr($admin_name, 0, 2));

$page_meta = [
    'dashboard.php'  => ['title' => 'Overview',              'sub' => 'Platform performance at a glance'],
    'users.php'      => ['title' => 'Passengers Directory',  'sub' => 'Manage registered user accounts'],
    'drivers.php'    => ['title' => 'Driver Fleet',          'sub' => 'Approve, monitor and manage drivers'],
    'rides.php'      => ['title' => 'Ride Activity Monitor', 'sub' => 'Search, filter and act on all bookings'],
    'earnings.php'   => ['title' => 'Financial Control',     'sub' => 'Commission settings and driver payouts'],
    'complaints.php' => ['title' => 'Support Desk',          'sub' => 'Review and resolve open tickets'],
];

$meta  = $page_meta[$current_page] ?? ['title' => 'Admin Panel', 'sub' => ''];
$title = $meta['title'];
$sub   = $meta['sub'];

// Fetch commission for topbar badge (only if DB is available)
$commission_rate_badge = 15;
if (isset($commission_rate)) $commission_rate_badge = $commission_rate;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> — RUS CAB Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Poppins:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php if ($current_page === 'dashboard.php'): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<div class="admin-app">

    <!-- ── Sidebar ── -->
    <aside class="admin-sidebar">

        <!-- Brand -->
        <div class="admin-brand">
            <div class="admin-brand-icon"><i class="fas fa-taxi"></i></div>
            <div>
                <div class="admin-brand-text">RUS CAB</div>
                <div class="admin-brand-sub">Control Panel</div>
            </div>
        </div>

        <!-- Admin pill -->
        <div class="sidebar-admin-pill">
            <div class="sidebar-admin-avatar"><?php echo $initials; ?></div>
            <div class="sidebar-admin-info">
                <strong><?php echo htmlspecialchars($admin_name); ?></strong>
                <span>Administrator</span>
            </div>
        </div>

        <!-- Main nav -->
        <div class="sidebar-section-label">Management</div>
        <nav class="admin-nav">
            <a href="dashboard.php" class="admin-nav-link <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="users.php" class="admin-nav-link <?php echo ($current_page === 'users.php') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="drivers.php" class="admin-nav-link <?php echo ($current_page === 'drivers.php') ? 'active' : ''; ?>">
                <i class="fas fa-id-card"></i> Manage Drivers
            </a>
            <a href="rides.php" class="admin-nav-link <?php echo ($current_page === 'rides.php') ? 'active' : ''; ?>">
                <i class="fas fa-route"></i> All Rides
            </a>
        </nav>

        <div class="sidebar-section-label">Finance & Support</div>
        <nav class="admin-nav">
            <a href="earnings.php" class="admin-nav-link <?php echo ($current_page === 'earnings.php') ? 'active' : ''; ?>">
                <i class="fas fa-wallet"></i> Earnings & Payouts
            </a>
            <a href="complaints.php" class="admin-nav-link <?php echo ($current_page === 'complaints.php') ? 'active' : ''; ?>">
                <i class="fas fa-triangle-exclamation"></i> Complaints
            </a>
        </nav>

        <!-- Logout -->
        <div class="sidebar-bottom">
            <nav class="admin-nav">
                <a href="../admin/admin_logout.php" class="admin-nav-link danger">
                    <i class="fas fa-arrow-right-from-bracket"></i> Sign Out
                </a>
            </nav>
        </div>

    </aside>

    <!-- ── Main ── -->
    <div class="admin-main">

        <!-- Top bar -->
        <div class="admin-topbar">
            <div class="topbar-left">
                <h2><?php echo $title; ?></h2>
                <?php if ($sub): ?><p><?php echo $sub; ?></p><?php endif; ?>
            </div>
            <div class="topbar-right">
                <?php if (isset($commission_rate)): ?>
                <div class="topbar-commission">
                    <i class="fas fa-percent" style="font-size:0.65rem;"></i>
                    Commission: <?php echo $commission_rate; ?>%
                </div>
                <?php endif; ?>
                <span style="font-size:0.78rem;color:var(--a-text-muted);display:flex;align-items:center;gap:0.4rem;">
                    <i class="fas fa-user-shield"></i>
                    <?php echo htmlspecialchars($admin_name); ?>
                </span>
            </div>
        </div>

        <!-- Content body — closed in footer.php -->
        <div class="admin-body">