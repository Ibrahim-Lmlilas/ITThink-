<?php
// Kan7ydo ga3 data mn session
session_start();
session_destroy();

// Kan redirectiw l login page
header("Location: auth.php");
exit();
