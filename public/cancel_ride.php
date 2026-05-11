<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$booking_id = mysqli_real_escape_string($conn, $_GET['id']);
$cancel_type = $_GET['type']; // 'free' or 'paid'

if ($cancel_type == 'paid') {
    // Logic: Set status to Cancelled and update the fare to ₹50 as a penalty
    $sql = "UPDATE bookings SET status = 'Cancelled', fare = 50.00, payment_status = 'Unpaid' 
            WHERE id = '$booking_id'";
    $msg = "Ride cancelled. A cancellation fee of ₹50 has been applied.";
} else {
    // Logic: Simply cancel the ride
    $sql = "UPDATE bookings SET status = 'Cancelled' WHERE id = '$booking_id'";
    $msg = "Ride cancelled successfully for free.";
}

if (mysqli_query($conn, $sql)) {
    header("Location: dashboard.php?msg=" . urlencode($msg));
} else {
    echo "Error: " . mysqli_error($conn);
}
?>