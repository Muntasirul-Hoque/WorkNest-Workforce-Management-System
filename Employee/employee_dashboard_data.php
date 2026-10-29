<?php
require_once 'employee_config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// ==================== GET STATS ====================
if ($action === 'get_stats') {
    $stats = [
        'totalTasks' => 0,
        'pendingTasks' => 0,
        'leaveBalance' => 0,
        'promotionStatus' => '-'
    ];

    // Total tasks
    $sql = "SELECT COUNT(*) as count FROM Task WHERE EmployeeID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $emp_employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) $stats['totalTasks'] = (int)$row['count'];

    // Pending tasks
    $sql = "SELECT COUNT(*) as count FROM Task WHERE EmployeeID = ? AND T_Status IN ('Pending', 'In_Progress')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $emp_employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) $stats['pendingTasks'] = (int)$row['count'];

    // Leave request count
    $sql = "SELECT COUNT(*) as count FROM `Leave` WHERE UserName = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $emp_user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) $stats['leaveBalance'] = (int)$row['count'];

    // Promotion status (latest)
    $sql = "SELECT Status FROM Promotion WHERE EmployeeID = ? ORDER BY PromotionDate DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $emp_employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) $stats['promotionStatus'] = $row['Status'];

    echo json_encode(['success' => true, 'stats' => $stats]);
    exit();
}

// ==================== GET IN PROGRESS TASKS ====================
if ($action === 'get_in_progress') {
    $sql = "SELECT TaskID, Title, DueDate FROM Task WHERE EmployeeID = ? AND T_Status = 'In_Progress' ORDER BY DueDate ASC LIMIT 5";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $emp_employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $tasks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tasks[] = [
            'TaskID' => $row['TaskID'],
            'taskId' => 'T-' . $row['TaskID'],
            'title' => $row['Title'],
            'dueDate' => $row['DueDate']
        ];
    }

    echo json_encode(['success' => true, 'tasks' => $tasks]);
    exit();
}

// ==================== GET RECENT LEAVES ====================
if ($action === 'get_recent_leaves') {
    $sql = "SELECT LeaveID, SD, ED, Reason, Status FROM `Leave` WHERE UserName = ? ORDER BY LeaveID DESC LIMIT 5";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $emp_user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $leaves = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $leaves[] = [
            'LeaveID' => $row['LeaveID'],
            'startDate' => $row['SD'],
            'endDate' => $row['ED'],
            'reason' => $row['Reason'],
            'status' => $row['Status']
        ];
    }

    echo json_encode(['success' => true, 'leaves' => $leaves]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>