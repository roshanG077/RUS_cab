<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['admin_id'])) { header("Location: index.php"); exit(); }

// Stats
$stats = [
    'users'   => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users"))['c'] ?? 0,
    'drivers' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM drivers"))['c'] ?? 0,
    'rides'   => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM bookings"))['c'] ?? 0,
    'revenue' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(fare) AS total FROM bookings WHERE status='Completed'"))['total'] ?? 0,
];

// Commission
$setting_query = mysqli_query($conn, "SELECT commission_rate FROM settings LIMIT 1");
$commission_rate = ($setting_query && mysqli_num_rows($setting_query) > 0)
    ? mysqli_fetch_assoc($setting_query)['commission_rate']
    : 15.00;

$company_earnings = ($stats['revenue'] * $commission_rate) / 100;
$open_complaints  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM complaints WHERE status='Open'"))['c'] ?? 0;
$pending_drivers  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM drivers WHERE is_active=0 AND is_suspended=0"))['c'] ?? 0;

// Recent rides
$recent_rides = mysqli_query($conn,
    "SELECT b.id, b.pickup_location, b.dropoff_location, b.fare, b.status,
            u.full_name AS passenger
     FROM bookings b
     LEFT JOIN users u ON b.user_id = u.id
     ORDER BY b.id DESC LIMIT 8"
);

// Real 7-day revenue for chart
$chart_labels = [];
$chart_data   = [];
for ($i = 6; $i >= 0; $i--) {
    $date  = date('Y-m-d', strtotime("-$i days"));
    $label = date('D', strtotime($date));
    $rev   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(fare) AS s FROM bookings WHERE status='Completed' AND DATE(created_at)='$date'"))['s'] ?? 0;
    $chart_labels[] = $label;
    $chart_data[]   = (float) $rev;
}

include 'header.php';
?>

<!-- Stat cards -->
<div class="a-stats-grid">
    <div class="a-stat-card blue">
        <div class="a-stat-text">
            <div class="a-stat-label">Passengers</div>
            <div class="a-stat-value"><?php echo number_format($stats['users']); ?></div>
            <div class="a-stat-sub">Registered accounts</div>
        </div>
        <div class="a-stat-icon blue"><i class="fas fa-users"></i></div>
    </div>
    <div class="a-stat-card green">
        <div class="a-stat-text">
            <div class="a-stat-label">Drivers</div>
            <div class="a-stat-value"><?php echo number_format($stats['drivers']); ?></div>
            <div class="a-stat-sub"><?php echo $pending_drivers; ?> pending approval</div>
        </div>
        <div class="a-stat-icon green"><i class="fas fa-car-side"></i></div>
    </div>
    <div class="a-stat-card teal">
        <div class="a-stat-text">
            <div class="a-stat-label">Total Rides</div>
            <div class="a-stat-value"><?php echo number_format($stats['rides']); ?></div>
            <div class="a-stat-sub">All-time bookings</div>
        </div>
        <div class="a-stat-icon teal"><i class="fas fa-route"></i></div>
    </div>
    <div class="a-stat-card amber">
        <div class="a-stat-text">
            <div class="a-stat-label">Company Profit</div>
            <div class="a-stat-value">₹<?php echo number_format($company_earnings, 0); ?></div>
            <div class="a-stat-sub"><?php echo $commission_rate; ?>% of ₹<?php echo number_format($stats['revenue'], 0); ?></div>
        </div>
        <div class="a-stat-icon amber"><i class="fas fa-rupee-sign"></i></div>
    </div>
</div>

<!-- Quick notices -->
<?php if ($pending_drivers > 0): ?>
<div class="a-alert a-alert-info" style="margin-bottom:var(--a-sp-md);">
    <i class="fas fa-circle-info"></i>
    <strong><?php echo $pending_drivers; ?> driver<?php echo $pending_drivers > 1 ? 's' : ''; ?></strong> awaiting approval.
    <a href="drivers.php" style="color:var(--a-blue);font-weight:700;margin-left:0.4rem;">Review now →</a>
</div>
<?php endif; ?>
<?php if ($open_complaints > 0): ?>
<div class="a-alert a-alert-danger" style="margin-bottom:var(--a-sp-md);">
    <i class="fas fa-triangle-exclamation"></i>
    <strong><?php echo $open_complaints; ?> open complaint<?php echo $open_complaints > 1 ? 's' : ''; ?></strong> need attention.
    <a href="complaints.php" style="color:var(--a-red);font-weight:700;margin-left:0.4rem;">View tickets →</a>
</div>
<?php endif; ?>

<!-- Chart + Recent Rides -->
<div class="a-grid-2">

    <!-- Revenue chart -->
    <div class="a-panel">
        <div class="a-panel-header">
            <h4><i class="fas fa-chart-line"></i> Revenue — Last 7 Days</h4>
            <span style="font-size:0.72rem;color:var(--a-text-muted);">Platform gross earnings</span>
        </div>
        <div class="a-chart-wrap">
            <canvas id="revenueChart" style="width:100%;"></canvas>
        </div>
    </div>

    <!-- Recent rides -->
    <div class="a-panel">
        <div class="a-panel-header">
            <h4><i class="fas fa-clock-rotate-left"></i> Recent Rides</h4>
            <a href="rides.php" style="font-size:0.75rem;color:var(--a-amber-dark);font-weight:600;">View all →</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="a-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Passenger</th>
                        <th>Fare</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ride = mysqli_fetch_assoc($recent_rides)): ?>
                    <tr>
                        <td style="font-weight:600;color:var(--a-text);">#<?php echo $ride['id']; ?></td>
                        <td style="font-size:0.8rem;"><?php echo htmlspecialchars($ride['passenger'] ?? 'Unknown'); ?></td>
                        <td style="font-weight:700;color:var(--a-text);">₹<?php echo $ride['fare']; ?></td>
                        <td>
                            <?php
                            $s = strtolower($ride['status']);
                            $badge_map = ['pending'=>'a-badge-pending','accepted'=>'a-badge-accepted','running'=>'a-badge-running','completed'=>'a-badge-completed','cancelled'=>'a-badge-cancelled'];
                            ?>
                            <span class="a-badge <?php echo $badge_map[$s] ?? 'a-badge-pending'; ?>">
                                <?php echo $ride['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
const ctx = document.getElementById('revenueChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?php echo json_encode($chart_data); ?>,
            borderColor: '#e8a020',
            backgroundColor: 'rgba(232,160,32,0.08)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.42,
            pointBackgroundColor: '#e8a020',
            pointRadius: 4,
            pointHoverRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' ₹' + ctx.parsed.y.toLocaleString('en-IN')
                }
            }
        },
        scales: {
            x: {
                grid: { color: 'rgba(0,0,0,0.04)' },
                ticks: { font: { family: 'Poppins', size: 11 }, color: '#9ca3af' }
            },
            y: {
                grid: { color: 'rgba(0,0,0,0.04)' },
                ticks: {
                    font: { family: 'Poppins', size: 11 },
                    color: '#9ca3af',
                    callback: v => '₹' + v.toLocaleString('en-IN')
                }
            }
        }
    }
});
</script>

<?php include 'footer.php'; ?>