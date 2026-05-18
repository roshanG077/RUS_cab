<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['driver_id'])) { exit; }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $action = $_POST['action'];
    $driver_id = $_SESSION['driver_id'];

    // --- Inside driver/update_ride.php ---
    if ($action == 'accept') {
        // 1. Assign the driver to the booking
        $sql = "UPDATE bookings SET driver_id = '$driver_id', status = 'Accepted' WHERE id = '$booking_id'";
        
        if (mysqli_query($conn, $sql)) {
            // 2. THE FIX: Increment both Received and Accepted counts
            $stats_sql = "UPDATE drivers SET 
                          total_requests_received = total_requests_received + 1, 
                          total_requests_accepted = total_requests_accepted + 1 
                          WHERE id = '$driver_id'";
            mysqli_query($conn, $stats_sql);

            header("Location: dashboard.php?msg=Ride Accepted!");
            exit();
        }
    }
}

header("Location: dashboard.php");
exit();