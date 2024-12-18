<?php
session_start();
session_destroy(); // Destroy all session data
header("Location: login.php"); // Redirect to login page
exit();
?>
