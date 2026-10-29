<?php
// Start session
session_start();

// Include database connection
require_once '../includes/db_connection.php';

// Include functions FIRST before any checks
require_once '../includes/functions.php';

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

// Set page title
$page_title = "HR Dashboard - WorkNest";

// Make sure $conn is available globally (Prevents calling getDBConnection() twice)
global $conn;
$conn = $GLOBALS['conn'];
?>