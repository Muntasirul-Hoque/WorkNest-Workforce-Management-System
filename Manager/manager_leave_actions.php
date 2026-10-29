<?php
require_once 'manager_config.php';
$conn = getDBConnection();
header('Content-Type: application/json');

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

$validActions = ['Processing', 'Rejected'];
if (!in_array($action, $validActions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

// Verify that the leave belongs to a subordinate of this manager
$sql = "SELECT l.LeaveID 
        FROM `Leave` l 
        JOIN Employee e ON l.UserName = e.UserName 
        WHERE l.LeaveID = ? AND e.ManagerID = ? AND l.Status = 'Pending'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $leaveId, $mgr_manager_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Leave request not found or not authorized']);
    exit();
}

// Update leave status
$sql = "UPDATE `Leave` SET Status = ? WHERE LeaveID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $action, $leaveId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true, 
        'message' => "Leave request {$action} successfully"
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Error updating leave request: ' . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>