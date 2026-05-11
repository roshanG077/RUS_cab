<?php
session_start();

// Unset all session variables specific to the user
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);

// If you want to completely destroy the session entirely:
// session_destroy(); 

// Redirect back to the landing page
header("Location: index.php");
exit();
?>