<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_role'])) {
    header("Location: ../login.php");
    exit();
}

// Check if user has HR role
if ($_SESSION['user_role'] !== 'HR') {
    header("Location: ../unauthorized.html");
    exit();
}

// Get HR details from session
$hr_user_name = $_SESSION['user_name'] ?? '';
$hr_first_name = $_SESSION['first_name'] ?? 'HR';
$hr_last_name = $_SESSION['last_name'] ?? 'User';
?>