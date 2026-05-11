<?php
session_start();
$_SESSION = array();
session_destroy();

session_start();
$_SESSION['logout_msg'] = "You have successfully logged out.";

header("Location: index.php");
exit();
?>