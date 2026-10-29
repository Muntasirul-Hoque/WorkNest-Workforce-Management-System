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

// Check if user has Employee role (NOT HR, NOT Manager)
if ($_SESSION['user_role'] === 'HR' || strpos($_SESSION['user_role'], 'Manager') !== false) {
    header("Location: ../unauthorized.html");
    exit();
}

// Get employee details from session
$emp_user_name = $_SESSION['user_name'] ?? '';
$emp_first_name = $_SESSION['first_name'] ?? 'Employee';
$emp_last_name = $_SESSION['last_name'] ?? '';

// Get global connection
global $conn;
$conn = $GLOBALS['conn'];

// Get EmployeeID from database
$emp_employee_id = null;
$sql = "SELECT EmployeeID FROM Employee WHERE UserName = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $emp_user_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $emp_employee_id = $row['EmployeeID'];
}

// If no EmployeeID found, redirect
if ($emp_employee_id === null) {
    header("Location: ../unauthorized.html");
    exit();
}

// Set page title
$page_title = "Employee Dashboard - WorkNest";
?>