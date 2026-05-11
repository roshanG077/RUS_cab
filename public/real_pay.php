<?php
session_start();
require_once '../config/db.php';

// 1. Setup Keys (Get these from Razorpay Dashboard > Settings > API Keys)
$key_id = "rzp_test_YOUR_ACTUAL_KEY"; 
$key_secret = "YOUR_ACTUAL_SECRET";

// 2. Get Ride Details from URL
$booking_id = $_GET['id'] ?? die("Error: Booking ID missing");
$query = mysqli_query($conn, "SELECT fare FROM bookings WHERE id = '$booking_id'");
$ride = mysqli_fetch_assoc($query);
$fare = $ride['fare'];

// 3. Create Order via cURL (No SDK library needed!)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'receipt' => 'rcp_' . $booking_id,
    'amount' => $fare * 100, // Razorpay uses Paise
    'currency' => 'INR'
]));
curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$result = curl_exec($ch);
$order = json_decode($result);
curl_close($ch);

if (isset($order->error)) {
    die("Razorpay Error: " . $order->error->description);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Secure Payment - RUS CAB</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body style="background:#f4f7f6; font-family: sans-serif; text-align:center; padding-top:100px;">

    <h2>Processing Payment for Ride #<?php echo $booking_id; ?></h2>
    <p>Please do not refresh the page...</p>

    <script>
    var options = {
        "key": "<?php echo $key_id; ?>", 
        "amount": "<?php echo $order->amount; ?>", 
        "currency": "INR",
        "name": "RUS CAB",
        "description": "Ride Payment",
        "order_id": "<?php echo $order->id; ?>", // Real Order ID from Razorpay
        "handler": function (response){
            // Success! Send to verification
            window.location.href = "verify_payment.php?booking_id=<?php echo $booking_id; ?>&pay_id=" + response.razorpay_payment_id;
        },
        "theme": { "color": "#FF4B28" }
    };
    var rzp1 = new Razorpay(options);
    window.onload = function() {
        rzp1.open(); // Opens automatically on load
    };
    </script>
</body>
</html>