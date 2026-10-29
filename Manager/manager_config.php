<?php
// Start session
session_start();

// Include database connection
require_once '../includes/db_connection.php';

// Include functions
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_role'])) {
    header("Location: ../login.php");
    exit();
}

// Check if user has Manager role
if (strpos($_SESSION['user_role'], 'Manager') === false) {
    header("Location: ../unauthorized.html");
    exit();
}

// Get manager details from session
$mgr_user_name = $_SESSION['user_name'] ?? '';
$mgr_first_name = $_SESSION['first_name'] ?? 'Manager';
$mgr_last_name = $_SESSION['last_name'] ?? '';

// Get ManagerID from database
$mgr_manager_id = null;
$sql = "SELECT ManagerID FROM Manager WHERE UserName = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $mgr_user_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $mgr_manager_id = $row['ManagerID'];
}

// If no ManagerID found, redirect
if ($mgr_manager_id === null) {
    header("Location: ../unauthorized.html");
    exit();
}

// Set page title
$page_title = "Manager Dashboard - WorkNest";
?>