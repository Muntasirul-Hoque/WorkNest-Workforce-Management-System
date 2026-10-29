<?php
require_once 'employee_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$taskId = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
$action = isset($_POST['action']) ? sanitizeInput($_POST['action']) : '';

if (empty($taskId) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Verify task belongs to this employee
$sql = "SELECT TaskID, T_Status FROM Task WHERE TaskID = ? AND EmployeeID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $taskId, $emp_employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$task = mysqli_fetch_assoc($result);

if (!$task) {
    echo json_encode(['success' => false, 'message' => 'Task not found or not authorized']);
    exit();
}

if ($action === 'accept') {
    if ($task['T_Status'] !== 'Pending') {
        echo json_encode(['success' => false, 'message' => 'Task is not pending']);
        exit();
    }
    $sql = "UPDATE Task SET T_Status = 'In_Progress' WHERE TaskID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $taskId);
    $msg = 'Task accepted successfully!';
} elseif ($action === 'complete') {
    if ($task['T_Status'] !== 'In_Progress') {
        echo json_encode(['success' => false, 'message' => 'Task is not in progress']);
        exit();
    }
    $sql = "UPDATE Task SET T_Status = 'Completed' WHERE TaskID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $taskId);
    $msg = 'Task marked as completed!';
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => $msg]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating task: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>