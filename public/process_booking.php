<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to book a cab.");
}

if (isset($_POST['pickup_location'])) {
    $uid    = $_SESSION['user_id']; 
    $p      = mysqli_real_escape_string($conn, $_POST['pickup_location']);
    $d      = mysqli_real_escape_string($conn, $_POST['dropoff_location']);
    $dist   = mysqli_real_escape_string($conn, $_POST['distance_km']);
    $fare   = mysqli_real_escape_string($conn, $_POST['fare']);
    $method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // Default system values
    $date = date('Y-m-d');
    $time = date('H:i:s');
    $otp  = rand(1000, 9999);
    $car  = mysqli_real_escape_string($conn, $_POST['car_type'] ?? 'Sedan');
    // LOGIC: If the user paid via Card or UPI in the modal, 
    // we set payment_status to 'Paid' immediately.
    $p_status = ($method == 'Cash') ? 'Unpaid' : 'Paid';

    // The Query - matching your master schema
    $sql = "INSERT INTO bookings (user_id, pickup_location, dropoff_location, pickup_date, pickup_time, distance_km, fare, car_type, otp, payment_method, payment_status, status) 
            VALUES ('$uid', '$p', '$d', '$date', '$time', '$dist', '$fare', '$car', '$otp', '$method', '$p_status', 'Pending')";

    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php?booking=success");
        exit();
    } else {
        die("MySQL Error: " . mysqli_error($conn));
    }
}
?>