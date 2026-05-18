<?php
// =============================================================
//  RUS Cab — Database Configuration Template
//  Copy this file to config/db.php and fill in your credentials.
//  ⚠️  NEVER commit config/db.php to version control!
// =============================================================

$host    = "localhost";         // Database host (usually localhost)
$db_user = "your_db_username";  // e.g. root (XAMPP default)
$db_pass = "your_db_password";  // Leave empty "" for default XAMPP
$db_name = "rus_cab_db";        // Name of your MySQL database

// Create connection (MySQLi Object-Oriented)
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset to UTF-8 for proper character encoding
$conn->set_charset("utf8mb4");
?>
