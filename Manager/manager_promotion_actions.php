<?php
require_once 'manager_config.php';
$conn = getDBConnection();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$promotionId = isset($_POST['promotion_id']) ? (int)$_POST['promotion_id'] : 0;
$action = isset($_POST['action']) ? sanitizeInput($_POST['action']) : '';

if (empty($promotionId) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$validActions = ['Processing', 'Rejected'];
if (!in_array($action, $validActions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

// Verify that the promotion belongs to a subordinate of this manager
$sql = "SELECT p.PromotionID 
        FROM Promotion p 
        JOIN Employee e ON p.EmployeeID = e.EmployeeID 
        WHERE p.PromotionID = ? AND e.ManagerID = ? AND p.Status = 'Pending'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $promotionId, $mgr_manager_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Promotion request not found or not authorized']);
    exit();
}

// Update promotion status
$sql = "UPDATE Promotion SET Status = ? WHERE PromotionID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $action, $promotionId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true, 
        'message' => "Promotion request {$action} successfully"
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Error updating promotion request: ' . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>