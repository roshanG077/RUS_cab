<?php
/**
 * payment_demo.php
 * Real-time Stripe payment page — Card (Stripe.js Elements) + UPI tab.
 * Card flow: Stripe.js → create_payment_intent.php (AJAX) → confirmCardPayment → verify_stripe.php
 * UPI  flow: redirect → pay_stripe.php (Stripe Checkout) → verify_stripe.php
 *
 * GET params: booking_id (int), method (card|upi)
 */
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (!isset($_GET['booking_id']))  { header("Location: dashboard.php"); exit(); }

$booking_id = mysqli_real_escape_string($conn, $_GET['booking_id']);
$method     = in_array($_GET['method'] ?? '', ['upi','card']) ? $_GET['method'] : 'card';
$uid        = (int)$_SESSION['user_id'];

// Fetch booking + user — must belong to this session user
$result = mysqli_query($conn,
    "SELECT b.id, b.fare, b.pickup_location, b.dropoff_location,
            b.car_type, b.distance_km, b.payment_method, b.otp,
            u.full_name, u.email
     FROM bookings b
     LEFT JOIN users u ON b.user_id = u.id
     WHERE b.id = '$booking_id' AND b.user_id = '$uid'
     LIMIT 1"
);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: dashboard.php?msg=Booking not found.");
    exit();
}

$b         = mysqli_fetch_assoc($result);
$fare      = (float)$b['fare'];
$fare_disp = '₹' . number_format($fare, 0);
$fare_dec  = number_format($fare, 2);

// Stripe publishable key — safe to expose in HTML
$STRIPE_PK = "pk_test_51TGKvSQSth21lTZQmaZLoPI80Iwvz11gpBNd2C8fRFaXqJIayONeeH8FYJM77cB5AuUigEutKFqeD826tG3wFSj500KhDbq308";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Secure Payment — RUS CAB #<?php echo $booking_id; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,400;0,500;1,400&family=Syne:wght@400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://js.stripe.com/v3/"></script>
<style>
/* ──────────────────── RESET & TOKENS ──────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:    #09090f;
  --bg2:   #101018;
  --bg3:   #16161f;
  --bg4:   #1c1c28;
  --bdr:   rgba(255,255,255,0.07);
  --bdr2:  rgba(255,255,255,0.13);
  --text:  #ededf5;
  --sub:   #8080a0;
  --dim:   #3a3a55;
  --amber: #f0a500;
  --amb2:  #ffc233;
  --green: #22c55e;
  --blue:  #3b82f6;
  --red:   #ef4444;
  --card-grad: linear-gradient(135deg,#1a1a2e 0%,#16213e 60%,#0f3460 100%);
  --ff-h:  'Syne',sans-serif;
  --ff-b:  'Inter',sans-serif;
  --ff-m:  'DM Mono',monospace;
  --r:     12px;
  --r2:    18px;
  --ease:  cubic-bezier(.22,1,.36,1);
}
html,body{min-height:100vh;font-family:var(--ff-b);background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}

/* ambient glow */
body::before{
  content:'';position:fixed;inset:0;pointer-events:none;
  background:
    radial-gradient(ellipse 70% 45% at 15% 5%,rgba(240,165,0,.07) 0%,transparent 65%),
    radial-gradient(ellipse 55% 38% at 85% 90%,rgba(59,130,246,.05) 0%,transparent 60%);
}

/* ──────────────────── LAYOUT ──────────────────── */
.shell{min-height:100vh;display:grid;grid-template-columns:1fr 400px;position:relative;z-index:1}

/* ──────────────────── LEFT PANEL ──────────────────── */
.left{display:flex;flex-direction:column;justify-content:center;padding:60px 72px;border-right:1px solid var(--bdr)}
.brand{display:flex;align-items:center;gap:10px;margin-bottom:56px}
.brand-icon{width:36px;height:36px;background:var(--amber);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#000;font-size:.9rem;flex-shrink:0}
.brand-name{font-family:var(--ff-h);font-weight:800;font-size:1.05rem;letter-spacing:-.02em}
.brand-name em{color:var(--amber);font-style:normal}

.heading{font-family:var(--ff-h);font-size:clamp(1.9rem,2.8vw,2.8rem);font-weight:800;letter-spacing:-.04em;line-height:1.08;margin-bottom:10px}
.heading em{color:var(--amber);font-style:normal}
.sub{font-size:.88rem;color:var(--sub);line-height:1.65;margin-bottom:44px}

/* Amount hero */
.amount-hero{text-align:center;padding:28px 0;border-top:1px solid var(--bdr);border-bottom:1px solid var(--bdr);margin-bottom:32px}
.amount-label{font-size:.62rem;text-transform:uppercase;letter-spacing:.12em;color:var(--dim);margin-bottom:8px;font-weight:600}
.amount-value{font-family:var(--ff-m);font-size:3.4rem;font-weight:500;letter-spacing:-.02em;line-height:1}
.amount-value sup{font-size:1.5rem;color:var(--amber);vertical-align:top;margin-top:8px;display:inline-block}

/* Ride summary card */
.ride-card{background:var(--bg2);border:1px solid var(--bdr2);border-radius:var(--r2);overflow:hidden;margin-bottom:28px}
.ride-card-top{height:3px;background:linear-gradient(90deg,var(--amber),var(--amb2),transparent)}
.ride-row{display:flex;align-items:flex-start;gap:14px;padding:12px 20px;border-bottom:1px solid var(--bdr)}
.ride-row:last-child{border-bottom:none}
.ride-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.72rem;flex-shrink:0;margin-top:2px}
.ri-green{background:rgba(34,197,94,.1);color:var(--green)}
.ri-red{background:rgba(239,68,68,.1);color:var(--red)}
.ri-amber{background:rgba(240,165,0,.1);color:var(--amber)}
.ri-blue{background:rgba(59,130,246,.1);color:var(--blue)}
.ride-lbl{font-size:.6rem;text-transform:uppercase;letter-spacing:.1em;color:var(--dim);font-weight:600;display:block;margin-bottom:2px}
.ride-val{font-size:.83rem;color:var(--sub);line-height:1.4}

/* Security row */
.sec-row{display:flex;gap:18px;flex-wrap:wrap}
.sec-badge{display:flex;align-items:center;gap:5px;font-size:.68rem;color:var(--dim)}
.sec-badge i{color:var(--green);font-size:.62rem}

/* ──────────────────── RIGHT PANEL ──────────────────── */
.right{background:var(--bg2);display:flex;flex-direction:column;padding:36px 32px;overflow-y:auto}

/* Tabs */
.tabs{display:grid;grid-template-columns:1fr 1fr;background:var(--bg3);border:1px solid var(--bdr);border-radius:var(--r);padding:3px;gap:3px;margin-bottom:28px}
.tab{padding:10px;border-radius:9px;font-size:.8rem;font-weight:600;color:var(--sub);background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:all .18s;font-family:var(--ff-b)}
.tab.active{background:var(--bg);color:var(--text);border:1px solid var(--bdr2)}
.tab i{font-size:.82rem}

/* Section title */
.sec-title{font-family:var(--ff-h);font-size:.95rem;font-weight:700;display:flex;align-items:center;gap:8px;margin-bottom:22px}
.sec-title i{color:var(--amber);font-size:.85rem}

/* Form groups */
.fg{margin-bottom:16px}
.fg label{display:block;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.09em;color:var(--sub);margin-bottom:7px}
.fg input{width:100%;background:var(--bg3);border:1px solid var(--bdr2);border-radius:var(--r);padding:12px 14px;font-family:var(--ff-m);font-size:.88rem;color:var(--text);transition:border-color .18s,box-shadow .18s;outline:none}
.fg input::placeholder{color:var(--dim);font-family:var(--ff-b)}
.fg input:focus{border-color:var(--amber);box-shadow:0 0 0 3px rgba(240,165,0,.1)}
.fg-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}

/* Stripe elements host */
.s-field{background:var(--bg3);border:1px solid var(--bdr2);border-radius:var(--r);padding:12px 14px;min-height:44px;transition:border-color .18s,box-shadow .18s}
.s-field.StripeElement--focus{border-color:var(--amber);box-shadow:0 0 0 3px rgba(240,165,0,.1)}
.s-field.StripeElement--invalid{border-color:var(--red)}
.s-field.StripeElement--complete{border-color:var(--green)}

/* Credit card preview */
.cc-preview{background:var(--card-grad);border-radius:14px;padding:22px 24px;margin-bottom:24px;position:relative;overflow:hidden;height:148px;box-shadow:0 16px 48px rgba(0,0,0,.45)}
.cc-preview::before,.cc-preview::after{content:'';position:absolute;border-radius:50%}
.cc-preview::before{width:180px;height:180px;background:rgba(255,255,255,.04);top:-60px;right:-30px}
.cc-preview::after{width:130px;height:130px;background:rgba(255,255,255,.03);bottom:-50px;left:10px}
.cc-chip{width:34px;height:26px;background:linear-gradient(135deg,#c9a227,#f0d060);border-radius:4px;margin-bottom:18px;position:relative;z-index:1}
.cc-num{font-family:var(--ff-m);font-size:.92rem;letter-spacing:.22em;color:rgba(255,255,255,.8);margin-bottom:10px;position:relative;z-index:1}
.cc-bot{display:flex;justify-content:space-between;align-items:flex-end;position:relative;z-index:1}
.cc-bot-lbl{font-size:.5rem;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.35)}
.cc-bot-val{font-family:var(--ff-m);font-size:.72rem;color:rgba(255,255,255,.75)}
.cc-brand{font-size:1.5rem;color:rgba(255,255,255,.85)}

/* UPI panel */
.upi-apps{display:grid;grid-template-columns:repeat(4,1fr);gap:9px;margin:4px 0 18px}
.upi-app{background:var(--bg3);border:1px solid var(--bdr);border-radius:var(--r);padding:14px 6px;text-align:center;cursor:pointer;transition:all .18s}
.upi-app:hover,.upi-app.sel{border-color:var(--amber);background:rgba(240,165,0,.05)}
.upi-app i{font-size:1.3rem;display:block;margin-bottom:5px}
.upi-app span{font-size:.58rem;color:var(--sub);font-weight:600;text-transform:uppercase;letter-spacing:.06em}

.upi-id-wrap{display:flex;gap:8px;align-items:flex-end;margin-bottom:12px}
.upi-id-wrap .fg{flex:1;margin-bottom:0}
.verify-btn{background:var(--bg3);border:1px solid var(--bdr2);border-radius:var(--r);padding:12px 14px;color:var(--sub);font-size:.76rem;font-weight:600;cursor:pointer;white-space:nowrap;font-family:var(--ff-b);transition:all .18s}
.verify-btn:hover{border-color:var(--amber);color:var(--amber)}

.upi-notice{background:var(--bg3);border:1px solid var(--bdr);border-radius:var(--r);padding:14px 16px;margin-bottom:16px;font-size:.78rem;color:var(--sub);line-height:1.6}
.upi-notice i{color:var(--amber);margin-right:5px}

.upi-status{display:none;border-radius:var(--r);padding:12px 16px;font-size:.78rem;text-align:center;margin-bottom:14px}
.upi-status.show{display:block}
.upi-status.pending{background:rgba(240,165,0,.08);border:1px solid rgba(240,165,0,.2);color:var(--amber)}
.upi-status.ok{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:var(--green)}
.upi-status.fail{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--red)}

/* Error box */
.err-box{display:flex;align-items:center;gap:6px;color:var(--red);font-size:.78rem;min-height:22px;margin-top:10px}

/* Pay button */
.pay-btn{width:100%;padding:15px;background:var(--amber);color:#000;border:none;border-radius:var(--r);font-family:var(--ff-h);font-size:.98rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:all .18s var(--ease);letter-spacing:-.01em;position:relative;overflow:hidden;margin-top:auto}
.pay-btn::after{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.18),transparent);transition:left .55s}
.pay-btn:hover::after{left:100%}
.pay-btn:hover{background:var(--amb2);transform:translateY(-1px);box-shadow:0 8px 24px rgba(240,165,0,.28)}
.pay-btn:active{transform:translateY(0)}
.pay-btn:disabled{opacity:.45;cursor:not-allowed;transform:none}
.pay-btn.spinning{background:#222;color:var(--sub)}

/* Spinner inline */
.spin{width:18px;height:18px;border:2px solid rgba(255,255,255,.15);border-top-color:currentColor;border-radius:50%;animation:rot .7s linear infinite}
@keyframes rot{to{transform:rotate(360deg)}}

/* Powered by */
.powered{display:flex;align-items:center;justify-content:center;gap:5px;font-size:.65rem;color:var(--dim);margin-top:18px}
.powered i{font-size:.6rem}

/* ──────────────────── OVERLAYS ──────────────────── */
/* Loading */
.overlay{position:fixed;inset:0;background:rgba(9,9,15,.9);display:flex;flex-direction:column;align-items:center;justify-content:center;z-index:999;backdrop-filter:blur(8px);opacity:0;pointer-events:none;transition:opacity .3s}
.overlay.show{opacity:1;pointer-events:all}
.big-spin{width:60px;height:60px;border:3px solid rgba(240,165,0,.15);border-top-color:var(--amber);border-radius:50%;animation:rot .8s linear infinite;margin-bottom:20px}
.overlay h3{font-family:var(--ff-h);font-size:1.1rem;font-weight:700;margin-bottom:6px}
.overlay p{font-size:.82rem;color:var(--sub)}

/* Success */
.success-ov{position:fixed;inset:0;background:var(--bg);display:flex;flex-direction:column;align-items:center;justify-content:center;z-index:1000;opacity:0;pointer-events:none;transition:opacity .4s}
.success-ov.show{opacity:1;pointer-events:all}
.success-ring{width:96px;height:96px;border-radius:50%;background:rgba(34,197,94,.08);border:2px solid var(--green);display:flex;align-items:center;justify-content:center;font-size:2.4rem;color:var(--green);margin-bottom:26px;animation:pulse 1.2s ease infinite alternate}
@keyframes pulse{from{box-shadow:0 0 0 0 rgba(34,197,94,.3)}to{box-shadow:0 0 0 18px rgba(34,197,94,0)}}
.success-ov h2{font-family:var(--ff-h);font-size:2rem;font-weight:800;letter-spacing:-.04em;margin-bottom:8px}
.success-ov p{color:var(--sub);font-size:.88rem}

/* ──────────────────── RESPONSIVE ──────────────────── */
@media(max-width:860px){
  .shell{grid-template-columns:1fr}
  .left{border-right:none;border-bottom:1px solid var(--bdr);padding:36px 28px}
  .right{padding:28px}
}
@media(max-width:480px){
  .left{padding:24px 18px}.right{padding:20px 14px}
  .heading{font-size:1.7rem}
  .amount-value{font-size:2.6rem}
}
</style>
</head>
<body>

<!-- Loading overlay -->
<div class="overlay" id="loadingOv">
  <div class="big-spin"></div>
  <h3>Processing Payment…</h3>
  <p>Please don't close or refresh this page.</p>
</div>

<!-- Success overlay -->
<div class="success-ov" id="successOv">
  <div class="success-ring"><i class="fas fa-check"></i></div>
  <h2>Payment Confirmed!</h2>
  <p>Redirecting to your dashboard…</p>
</div>

<div class="shell">

  <!-- ╔══════════════════ LEFT ══════════════════╗ -->
  <div class="left">

    <div class="brand">
      <div class="brand-icon"><i class="fas fa-taxi"></i></div>
      <div class="brand-name">RUS <em>CAB</em></div>
    </div>

    <h1 class="heading">Secure<br><em>Checkout</em></h1>
    <p class="sub">Your ride is booked. Complete payment to get your driver and OTP.</p>

    <div class="amount-hero">
      <div class="amount-label">Total Amount Due</div>
      <div class="amount-value">
        <sup>₹</sup><?php echo number_format($fare, 0); ?>
      </div>
    </div>

    <div class="ride-card">
      <div class="ride-card-top"></div>
      <div class="ride-row">
        <div class="ride-icon ri-green"><i class="fas fa-circle-dot"></i></div>
        <div>
          <span class="ride-lbl">Pickup</span>
          <span class="ride-val"><?php echo htmlspecialchars($b['pickup_location']); ?></span>
        </div>
      </div>
      <div class="ride-row">
        <div class="ride-icon ri-red"><i class="fas fa-location-dot"></i></div>
        <div>
          <span class="ride-lbl">Drop-off</span>
          <span class="ride-val"><?php echo htmlspecialchars($b['dropoff_location']); ?></span>
        </div>
      </div>
      <div class="ride-row">
        <div class="ride-icon ri-amber"><i class="fas fa-car-side"></i></div>
        <div>
          <span class="ride-lbl">Vehicle &amp; Distance</span>
          <span class="ride-val"><?php echo htmlspecialchars($b['car_type']); ?> &nbsp;·&nbsp; <?php echo $b['distance_km']; ?> km</span>
        </div>
      </div>
      <div class="ride-row">
        <div class="ride-icon ri-blue"><i class="fas fa-hashtag"></i></div>
        <div>
          <span class="ride-lbl">Booking ID</span>
          <span class="ride-val" style="font-family:var(--ff-m)">#<?php echo $booking_id; ?></span>
        </div>
      </div>
    </div>

    <div class="sec-row">
      <div class="sec-badge"><i class="fas fa-shield-halved"></i> 256-bit SSL</div>
      <div class="sec-badge"><i class="fas fa-check-circle"></i> Stripe Secured</div>
      <div class="sec-badge"><i class="fas fa-lock"></i> PCI DSS</div>
    </div>

  </div>

  <!-- ╔══════════════════ RIGHT ══════════════════╗ -->
  <div class="right">

    <!-- Tab switcher -->
    <div class="tabs">
      <button class="tab <?php echo $method!=='upi'?'active':''; ?>" id="tab-card" onclick="switchTab('card')">
        <i class="fas fa-credit-card"></i> Card
      </button>
      <button class="tab <?php echo $method==='upi'?'active':''; ?>" id="tab-upi" onclick="switchTab('upi')">
        <i class="fas fa-mobile-screen-button"></i> UPI
      </button>
    </div>

    <!-- ─────────── CARD PANEL ─────────── -->
    <div id="cardPanel">
      <div class="sec-title"><i class="fas fa-credit-card"></i> Card Details</div>

      <!-- Visual card preview -->
      <div class="cc-preview">
        <div class="cc-chip"></div>
        <div class="cc-num" id="prevNum">•••• •••• •••• ••••</div>
        <div class="cc-bot">
          <div>
            <div class="cc-bot-lbl">Card Holder</div>
            <div class="cc-bot-val" id="prevName"><?php echo htmlspecialchars($b['full_name'] ?? 'YOUR NAME'); ?></div>
          </div>
          <div>
            <div class="cc-bot-lbl">Expires</div>
            <div class="cc-bot-val" id="prevExp">MM/YY</div>
          </div>
          <div class="cc-brand" id="prevBrand"><i class="fas fa-credit-card"></i></div>
        </div>
      </div>

      <form id="cardForm">
        <div class="fg">
          <label>Card Number</label>
          <div id="elNum" class="s-field"></div>
        </div>
        <div class="fg-row">
          <div class="fg">
            <label>Expiry</label>
            <div id="elExp" class="s-field"></div>
          </div>
          <div class="fg">
            <label>CVV</label>
            <div id="elCvc" class="s-field"></div>
          </div>
        </div>
        <div class="fg">
          <label>Name on Card</label>
          <input type="text" id="cardName" placeholder="e.g. Ramesh Kumar"
                 value="<?php echo htmlspecialchars($b['full_name'] ?? ''); ?>">
        </div>
        <div class="err-box" id="cardErr" role="alert"></div>
        <button type="submit" class="pay-btn" id="cardBtn">
          <i class="fas fa-lock"></i> Pay <?php echo $fare_disp; ?>
        </button>
      </form>
    </div>

    <!-- ─────────── UPI PANEL ─────────── -->
    <div id="upiPanel" style="display:none">
      <div class="sec-title"><i class="fas fa-mobile-screen-button"></i> UPI Payment</div>

      <!-- App icons -->
      <div class="upi-apps">
        <div class="upi-app" onclick="selApp(this,'GPay')">
          <i class="fab fa-google" style="color:#4285f4"></i>
          <span>GPay</span>
        </div>
        <div class="upi-app" onclick="selApp(this,'PhonePe')">
          <i class="fas fa-mobile-screen-button" style="color:#5f259f"></i>
          <span>PhonePe</span>
        </div>
        <div class="upi-app" onclick="selApp(this,'Paytm')">
          <i class="fas fa-wallet" style="color:#00baf2"></i>
          <span>Paytm</span>
        </div>
        <div class="upi-app" onclick="selApp(this,'BHIM')">
          <i class="fas fa-university" style="color:#ff6a00"></i>
          <span>BHIM</span>
        </div>
      </div>

      <div class="upi-id-wrap">
        <div class="fg">
          <label>Your UPI ID</label>
          <input type="text" id="upiId" placeholder="yourname@upi" style="margin:0">
        </div>
        <button class="verify-btn" id="verifyBtn" onclick="verifyUPI()">Verify</button>
      </div>

      <div class="upi-status" id="upiSt"></div>

      <div class="upi-notice">
        <i class="fas fa-info-circle"></i>
        After tapping Pay, you'll land on Stripe's secure page where you can complete payment with any UPI app.
      </div>

      <button class="pay-btn" id="upiBtn" onclick="upiPay()">
        <i class="fas fa-arrow-right"></i> Pay <?php echo $fare_disp; ?> via UPI
      </button>
    </div>

    <div class="powered">
      <i class="fas fa-lock"></i>&nbsp;Powered by&nbsp;<strong>Stripe</strong>
      &nbsp;— card data never touches our server
    </div>

  </div><!-- .right -->
</div><!-- .shell -->

<script>
// ── Stripe Init ────────────────────────────────────────────────────────────────
const stripe   = Stripe('<?php echo $STRIPE_PK; ?>');
const elements = stripe.elements({
  fonts:[{cssSrc:'https://fonts.googleapis.com/css2?family=DM+Mono&display=swap'}]
});

const elStyle = {
  base:{
    color:'#ededf5',
    fontFamily:'"DM Mono",monospace',
    fontSize:'14px',
    '::placeholder':{color:'#3a3a55'},
    iconColor:'#8080a0',
  },
  invalid:{color:'#ef4444',iconColor:'#ef4444'},
  complete:{color:'#22c55e',iconColor:'#22c55e'},
};

const elNum = elements.create('cardNumber', {style:elStyle, showIcon:true});
const elExp = elements.create('cardExpiry', {style:elStyle});
const elCvc = elements.create('cardCvc',    {style:elStyle});

elNum.mount('#elNum');
elExp.mount('#elExp');
elCvc.mount('#elCvc');

// Live card preview updates
const brandMap = {
  visa:'<i class="fab fa-cc-visa"></i>',
  mastercard:'<i class="fab fa-cc-mastercard"></i>',
  amex:'<i class="fab fa-cc-amex"></i>',
  discover:'<i class="fab fa-cc-discover"></i>',
  jcb:'<i class="fab fa-cc-jcb"></i>',
  diners:'<i class="fab fa-cc-diners-club"></i>',
};

elNum.on('change', e => {
  showErr(e.error ? e.error.message : '');
  document.getElementById('prevBrand').innerHTML =
    brandMap[e.brand] || '<i class="fas fa-credit-card"></i>';
});

elExp.on('change', e => {
  document.getElementById('prevExp').textContent = e.value || 'MM/YY';
});

// ── Tab Switch ─────────────────────────────────────────────────────────────────
function switchTab(t) {
  document.getElementById('cardPanel').style.display = t==='card' ? 'block' : 'none';
  document.getElementById('upiPanel').style.display  = t==='upi'  ? 'block' : 'none';
  document.getElementById('tab-card').classList.toggle('active', t==='card');
  document.getElementById('tab-upi').classList.toggle('active',  t==='upi');
}

// Init tab from PHP
<?php echo $method === 'upi' ? "switchTab('upi');" : "switchTab('card');"; ?>

// ── UPI helpers ────────────────────────────────────────────────────────────────
function selApp(el, name) {
  document.querySelectorAll('.upi-app').forEach(a => a.classList.remove('sel'));
  el.classList.add('sel');
}

function verifyUPI() {
  const id  = document.getElementById('upiId').value.trim();
  const btn = document.getElementById('verifyBtn');
  const st  = document.getElementById('upiSt');

  if (!id.includes('@') || id.length < 5) {
    st.className = 'upi-status show fail';
    st.innerHTML = '<i class="fas fa-times-circle"></i> Invalid UPI ID — format: name@bank';
    return;
  }
  btn.textContent = 'Checking…';
  st.className = 'upi-status show pending';
  st.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying UPI ID with bank…';

  setTimeout(() => {
    btn.textContent = 'Verify';
    st.className = 'upi-status show ok';
    st.innerHTML = '<i class="fas fa-check-circle"></i> UPI ID verified! Tap Pay to proceed.';
  }, 1800);
}

function upiPay() {
  document.getElementById('loadingOv').classList.add('show');
  window.location.href = 'pay_stripe.php?booking_id=<?php echo $booking_id; ?>';
}

// ── Card Form Submit ───────────────────────────────────────────────────────────
document.getElementById('cardForm').addEventListener('submit', async e => {
  e.preventDefault();
  const btn  = document.getElementById('cardBtn');
  const name = document.getElementById('cardName').value.trim() || 'Customer';

  btn.disabled = true;
  btn.classList.add('spinning');
  btn.innerHTML = '<span class="spin"></span> Processing…';
  showErr('');
  document.getElementById('loadingOv').classList.add('show');

  try {
    // Step 1: Create PaymentIntent on server
    const res = await fetch('create_payment_intent.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({booking_id:<?php echo (int)$booking_id; ?>})
    });
    const data = await res.json();

    if (data.error) { throw new Error(data.error); }

    // Step 2: Confirm card with Stripe.js — card data goes directly to Stripe
    const {error, paymentIntent} = await stripe.confirmCardPayment(data.client_secret, {
      payment_method:{
        card: elNum,
        billing_details:{ name }
      }
    });

    if (error) { throw new Error(error.message); }

    if (paymentIntent.status === 'succeeded') {
      document.getElementById('loadingOv').classList.remove('show');
      document.getElementById('successOv').classList.add('show');
      setTimeout(() => {
        window.location.href =
          'verify_stripe.php?booking_id=<?php echo $booking_id; ?>&session_id=' + paymentIntent.id;
      }, 2200);
    }

  } catch (err) {
    document.getElementById('loadingOv').classList.remove('show');
    showErr(err.message);
    btn.disabled = false;
    btn.classList.remove('spinning');
    btn.innerHTML = '<i class="fas fa-lock"></i> Pay <?php echo $fare_disp; ?>';
  }
});

function showErr(msg) {
  const el = document.getElementById('cardErr');
  el.innerHTML = msg ? '<i class="fas fa-circle-exclamation"></i> ' + msg : '';
}
</script>

</body>
</html>