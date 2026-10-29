<?php
// We use session_check.php because it handles the session, but we also need functions and the global DB connection.
require_once '../includes/session_check.php';
require_once '../includes/db_connection.php';
require_once '../includes/functions.php';

// Use the global connection (do NOT call getDBConnection() again!)
global $conn;
$conn = $GLOBALS['conn'];

header('Content-Type: application/json');

// IMPORTANT FIX: Capture role from session directly here
$current_user_role = $_SESSION['user_role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$leaveId = isset($_POST['leave_id']) ? (int)$_POST['leave_id'] : 0;
$action = isset($_POST['action']) ? sanitizeInput($_POST['action']) : '';

if (empty($leaveId) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// ROLE-BASED WORKFLOW
if ($current_user_role === 'Manager') {
    if ($action !== 'Processing') {
        echo json_encode(['success' => false, 'message' => 'Managers can only move requests to Processing.']);
        exit();
    }
    $newStatus = 'Processing';
} elseif ($current_user_role === 'HR') {
    if (!in_array($action, ['Accepted', 'Rejected'])) {
        echo json_encode(['success' => false, 'message' => 'HR can only Accept or Reject requests.']);
        exit();
    }
    $newStatus = $action;
} else {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Session Role is: ' . $current_user_role]);
    exit();
}

// Update leave status
$sql = "UPDATE `Leave` SET Status = ? WHERE LeaveID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $newStatus, $leaveId);

if (mysqli_stmt_execute($stmt)) {
    // If HR accepts, update user status to 'On_leave'
    if ($newStatus === 'Accepted') {
        $sql = "SELECT UserName FROM `Leave` WHERE LeaveID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $leaveId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $userName = $row['UserName'];
            $sql = "UPDATE User SET Status = 'On_leave' WHERE UserName = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $userName);
            mysqli_stmt_execute($stmt);
        }
    }
    
    echo json_encode(['success' => true, 'message' => "Leave request moved to {$newStatus} successfully"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating leave request: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>