<?php
session_start();
require_once '../config/db.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['booking_id'])) {
    
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    
    // 1. Fetch trip details to calculate earnings
    $query = "SELECT fare, driver_id, payment_method, payment_status FROM bookings WHERE id = '$booking_id'";
    $result = mysqli_query($conn, $query);
    $ride = mysqli_fetch_assoc($result);

    if ($ride) {
        $fare = $ride['fare'];
        $driver_id = $ride['driver_id'];

        // 2. Logic: If it's a digital payment (UPI/Card) and already paid, add to Driver's Wallet
        if ($ride['payment_method'] != 'Cash' && $ride['payment_status'] == 'Paid') {
            $wallet_update = "UPDATE drivers SET wallet_balance = wallet_balance + $fare WHERE id = '$driver_id'";
            mysqli_query($conn, $wallet_update);
        }

        // 3. Update the ride status to 'Completed'
        $status_update = "UPDATE bookings SET status = 'Completed' WHERE id = '$booking_id'";
        
        if (mysqli_query($conn, $status_update)) {
            // Success! Redirect back to dashboard
            header("Location: dashboard.php?msg=Ride completed successfully! Earnings updated.");
            exit();
        } else {
            echo "Error updating status: " . mysqli_error($conn);
        }
    } else {
        echo "Ride not found in database.";
    }
} else {
    header("Location: dashboard.php");
}
?>