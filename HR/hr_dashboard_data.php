<?php
require_once 'hr_config.php';

global $conn;
$conn = $GLOBALS['conn'];

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$current_user_role = $_SESSION['user_role'] ?? '';

// Handle employee overview request
if (isset($_GET['action']) && $_GET['action'] === 'get_employees') {
    $sql = "SELECT FirstName, LastName, Role, Dept, Status 
            FROM User 
            WHERE Role NOT IN ('HR') 
            AND Dept NOT IN ('HR')
            ORDER BY FirstName";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn), 'employees' => []]);
        exit();
    }
    
    $employees = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    
    echo json_encode(['success' => true, 'employees' => $employees, 'count' => count($employees)]);
    exit();
}

// Handle Leave Requests (Manager sees Pending, HR sees Processing)
if (isset($_GET['action']) && $_GET['action'] === 'get_leaves') {
    if ($current_user_role === 'Manager') {
        $sql = "SELECT l.*, u.FirstName, u.LastName, u.Dept 
                FROM `Leave` l 
                JOIN User u ON l.UserName = u.UserName 
                WHERE l.Status = 'Pending'";
    } elseif ($current_user_role === 'HR') {
        $sql = "SELECT l.*, u.FirstName, u.LastName, u.Dept 
                FROM `Leave` l 
                JOIN User u ON l.UserName = u.UserName 
                WHERE l.Status = 'Processing'";
    } else {
        echo json_encode(['success' => false, 'leaves' => []]);
        exit();
    }

    $result = mysqli_query($conn, $sql);
    $leaves = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $leaves[] = $row;
    }
    echo json_encode(['success' => true, 'leaves' => $leaves]);
    exit();
}

// Handle Promotion Requests (Manager sees Pending, HR sees Processing)
if (isset($_GET['action']) && $_GET['action'] === 'get_promotions') {
    if ($current_user_role === 'Manager') {
        $sql = "SELECT p.*, u.FirstName, u.LastName, u.Dept, u.Role 
                FROM Promotion p 
                JOIN Employee e ON p.EmployeeID = e.EmployeeID 
                JOIN User u ON e.UserName = u.UserName 
                WHERE p.Status = 'Pending'";
    } elseif ($current_user_role === 'HR') {
        $sql = "SELECT p.*, u.FirstName, u.LastName, u.Dept, u.Role 
                FROM Promotion p 
                JOIN Employee e ON p.EmployeeID = e.EmployeeID 
                JOIN User u ON e.UserName = u.UserName 
                WHERE p.Status = 'Processing'";
    } else {
        echo json_encode(['success' => false, 'promotions' => []]);
        exit();
    }

    $result = mysqli_query($conn, $sql);
    $promotions = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $promotions[] = $row;
    }
    echo json_encode(['success' => true, 'promotions' => $promotions]);
    exit();
}

// Regular dashboard data
$response = [
    'success' => true,
    'total_employees' => 0,
    'pending_leaves' => 0,
    'pending_promotions' => 0,
    'active_employees' => 0,
    'hr_name' => ($_SESSION['first_name'] ?? 'HR') . ' ' . ($_SESSION['last_name'] ?? 'User')
];

$sql = "SELECT COUNT(*) as count FROM Employee";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) $response['total_employees'] = (int)$row['count'];

if ($current_user_role === 'Manager') {
    $sql = "SELECT COUNT(*) as count FROM `Leave` WHERE Status = 'Pending'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) $response['pending_leaves'] = (int)$row['count'];

    $sql = "SELECT COUNT(*) as count FROM Promotion WHERE Status = 'Pending'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) $response['pending_promotions'] = (int)$row['count'];
} else {
    $sql = "SELECT COUNT(*) as count FROM `Leave` WHERE Status = 'Processing'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) $response['pending_leaves'] = (int)$row['count'];

    $sql = "SELECT COUNT(*) as count FROM Promotion WHERE Status = 'Processing'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) $response['pending_promotions'] = (int)$row['count'];
}

$sql = "SELECT COUNT(*) as count FROM User WHERE Status = 'Active' AND Role NOT IN ('HR')";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) $response['active_employees'] = (int)$row['count'];

echo json_encode($response);
?>