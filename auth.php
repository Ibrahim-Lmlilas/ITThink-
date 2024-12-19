<?php

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user']['role'] === 'admin';
}

function requireAdmin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
    
    if (!isAdmin()) {
        header("Location: dashboarduser.php");
        exit();
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>
