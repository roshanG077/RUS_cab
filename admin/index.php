<?php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['admin_id'])) { header("Location: dashboard.php"); exit(); }

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; // In a real app, use password_hash

    if ($username == "admin" && $password == "admin123") {
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_name'] = "System Admin";
        header("Location: dashboard.php");
    } else {
        $error = "Invalid Admin Credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Admin Login - RUS CAB</title>
    <link rel="stylesheet" href="../admin/assets/css/style.css">
    <!-- <style>
        body { background: var(--secondary); display: flex; align-items: center; justify-content: center; height: 100vh; }
        .admin-login { background: white; padding: 40px; border-radius: 15px; width: 350px; text-align: center; }
    </style> -->
</head>
<body>
    <div class="admin-login">
        <h2 style="color: var(--primary);">RUS CAB Admin</h2>
        <p style="margin-bottom: 20px;">Access Control Panel</p>
        <?php if($error) echo "<p style='color:red; font-size:13px;'>$error</p>"; ?>
        <form method="POST">
            <div class="input-group"><input type="text" name="username" placeholder="Username" required></div>
            <div class="input-group"><input type="password" name="password" placeholder="Password" required></div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Login to Console</button>
        </form>
    </div>
</body>
</html>