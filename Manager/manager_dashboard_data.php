<?php
require_once 'manager_config.php';
$conn = getDBConnection();
header('Content-Type: application/json');

// Handle task list request
if (isset($_GET['action']) && $_GET['action'] === 'get_tasks') {
    $sql = "SELECT t.*, u.FirstName, u.LastName, u.UserName 
            FROM Task t 
            JOIN Employee e ON t.EmployeeID = e.EmployeeID 
            JOIN User u ON e.UserName = u.UserName 
            WHERE t.ManagerID = ? 
            ORDER BY t.AssignedDate DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $mgr_manager_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $tasks = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tasks[] = [
            'TaskID' => $row['TaskID'],
            'Title' => $row['Title'],
            'Description' => $row['Description'],
            'T_Status' => $row['T_Status'],
            'AssignedDate' => $row['AssignedDate'],
            'DueDate' => $row['DueDate'],
            'EmployeeName' => $row['FirstName'] . ' ' . $row['LastName']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'tasks' => $tasks
    ]);
    exit();
}

// Get dashboard stats
$response = [
    'success' => true,
    'subordinates' => 0,
    'pending_leaves' => 0,
    'pending_promotions' => 0,
    'active_tasks' => 0
];

// Get subordinate employees count
$sql = "SELECT COUNT(*) as count FROM Employee WHERE ManagerID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $mgr_manager_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $response['subordinates'] = (int)$row['count'];
}

// Get pending leaves from subordinates
$sql = "SELECT COUNT(*) as count 
        FROM `Leave` l 
        JOIN Employee e ON l.UserName = e.UserName 
        WHERE e.ManagerID = ? AND l.Status = 'Pending'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $mgr_manager_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $response['pending_leaves'] = (int)$row['count'];
}

// Get pending promotions from subordinates
$sql = "SELECT COUNT(*) as count 
        FROM Promotion p 
        JOIN Employee e ON p.EmployeeID = e.EmployeeID 
        WHERE e.ManagerID = ? AND p.Status = 'Pending'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $mgr_manager_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $response['pending_promotions'] = (int)$row['count'];
}

// Get active tasks assigned by this manager
$sql = "SELECT COUNT(*) as count 
        FROM Task 
        WHERE ManagerID = ? AND T_Status IN ('Pending', 'In_Progress')";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $mgr_manager_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) {
    $response['active_tasks'] = (int)$row['count'];
}

echo json_encode($response);
?>