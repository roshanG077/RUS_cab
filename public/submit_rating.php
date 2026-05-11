<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bid = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $did = mysqli_real_escape_string($conn, $_POST['driver_id']);
    $uid = $_SESSION['user_id'];
    $stars = mysqli_real_escape_string($conn, $_POST['stars']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);

    // 1. SAFETY CHECK: Does this Booking ID actually exist?
    $check = mysqli_query($conn, "SELECT id FROM bookings WHERE id = '$bid'");
    if (mysqli_num_rows($check) == 0) {
        die("Error: This ride does not exist in the database. You cannot rate it.");
    }

    // 2. Insert into ratings table
    $sql = "INSERT INTO ratings (booking_id, driver_id, user_id, stars, feedback) 
            VALUES ('$bid', '$did', '$uid', '$stars', '$feedback')";
    
    if (mysqli_query($conn, $sql)) {
        // 3. Mark as rated
        mysqli_query($conn, "UPDATE bookings SET is_rated = 1 WHERE id = '$bid'");

        // 4. Update Driver Avg Rating
        $avg_res = mysqli_query($conn, "SELECT AVG(stars) as avg_r FROM ratings WHERE driver_id = '$did'");
        $avg_data = mysqli_fetch_assoc($avg_res);
        $new_avg = round($avg_data['avg_r'], 2);
        mysqli_query($conn, "UPDATE drivers SET rating = '$new_avg' WHERE id = '$did'");

        header("Location: dashboard.php?msg=Rating Submitted!");
    } else {
        echo "Database Error: " . mysqli_error($conn);
    }
}