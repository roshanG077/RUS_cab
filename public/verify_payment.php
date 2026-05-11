<?php
session_start();
require_once '../config/db.php';
$bid = $_GET['booking_id'];
$pid = $_GET['pay_id'];

mysqli_query($conn, "UPDATE bookings SET payment_status = 'Paid', payment_id = '$pid' WHERE id = '$bid'");
header("Location: dashboard.php?msg=Paid");
?>