<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['driver_id'])) {
    $driver_id = $_SESSION['driver_id'];
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    // 1. Update the Booking Status to 'Cancelled'
    // We mark 'cancelled_by' as 'Driver' so we know who to blame
    $sql_booking = "UPDATE bookings SET 
                    status = 'Cancelled', 
                    cancelled_by = 'Driver', 
                    cancel_reason = '$reason' 
                    WHERE id = '$booking_id' AND driver_id = '$driver_id'";
    
    if (mysqli_query($conn, $sql_booking)) {
        
        // 2. INCREMENT PENALTY STATS: Add +1 to the driver's cancellation count
        $sql_stats = "UPDATE drivers SET 
                      total_rides_cancelled = total_rides_cancelled + 1 
                      WHERE id = '$driver_id'";
        mysqli_query($conn, $sql_stats);

        // 3. AUTO-SUSPENSION LOGIC
        // Fetch fresh stats to check if they crossed the 25% limit
        $res = mysqli_query($conn, "SELECT total_requests_accepted, total_rides_cancelled FROM drivers WHERE id = '$driver_id'");
        $stats = mysqli_fetch_assoc($res);
        
        $accepted = $stats['total_requests_accepted'];
        $cancelled = $stats['total_rides_cancelled'];

        if ($accepted > 5) { // Only check after they've done at least 5 rides
            $rate = ($cancelled / $accepted) * 100;
            
            if ($rate > 25) { // If cancellation rate is higher than 25%
                mysqli_query($conn, "UPDATE drivers SET is_suspended = 1, is_active = 0 WHERE id = '$driver_id'");
                
                // Log them out and show suspension message
                session_destroy();
                header("Location: index.php?msg=Suspended: Cancellation rate too high ($rate%)");
                exit();
            }
        }

        header("Location: dashboard.php?msg=Ride Cancelled. Reason: $reason");
    } else {
        header("Location: dashboard.php?msg=Error processing cancellation.");
    }
} else {
    header("Location: dashboard.php");
}
?>