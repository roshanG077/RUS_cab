<?php
// Database credentials
$host = "localhost";
$db_user = "root"; // Default XAMPP/WAMP username
$db_pass = "";     // Default XAMPP/WAMP password (leave empty)
$db_name = "rus_cab_db"; // The name of the database we will create

// Create connection using MySQLi Object-Oriented approach
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset to UTF-8 for proper character encoding
$conn->set_charset("utf8mb4");
?>