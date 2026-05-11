<?php
session_start();
require_once '../config/db.php';

$key_id = "rzp_test_YOUR_KEY"; 
$key_secret = "YOUR_SECRET";
$booking_id = $_GET['id'];

$res = mysqli_query($conn, "SELECT fare FROM bookings WHERE id = '$booking_id'");
$ride = mysqli_fetch_assoc($res);
$amount_paise = $ride['fare'] * 100;

// Step 1: Create Real Order via cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'receipt' => 'rcp_'.$booking_id,
    'amount' => $amount_paise,
    'currency' => 'INR'
]));
curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$order = json_decode(curl_exec($ch));

// Step 2: Display Razorpay Modal
?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "<?php echo $key_id; ?>",
    "amount": "<?php echo $amount_paise; ?>",
    "order_id": "<?php echo $order->id; ?>", // REQUIRED for QR Code
    "name": "RUS CAB",
    "handler": function (response){
        window.location.href = "verify_payment.php?booking_id=<?php echo $booking_id; ?>&pay_id=" + response.razorpay_payment_id;
    },
    "theme": { "color": "#FF4B28" }
};
var rzp1 = new Razorpay(options);
rzp1.open();
</script>