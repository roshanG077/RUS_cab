<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $entered_otp = mysqli_real_escape_string($conn, $_POST['entered_otp']);

    // 1. Fetch the real OTP from the database
    $query = "SELECT otp FROM bookings WHERE id = '$booking_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        // 2. Compare the OTPs
        if ($row['otp'] == $entered_otp) {
            
            // 3. SUCCESS: Change status to 'Running' (This starts the journey)
            $update = "UPDATE bookings SET status = 'Running' WHERE id = '$booking_id'";
            
            if (mysqli_query($conn, $update)) {
                header("Location: dashboard.php?msg=Journey Started! Drive Safely.");
                exit();
            } else {
                die("Database Error: " . mysqli_error($conn));
            }

        } else {
            // OTP Mismatch
            header("Location: dashboard.php?msg=Invalid OTP. Please try again.");
            exit();
        }
    } else {
        die("Booking not found.");
    }
}
?>