<?php
require_once 'manager_config.php';

$message = '';
$messageType = '';
$assignedTasks = [];

$employees = [];
$sql = "SELECT e.EmployeeID, u.FirstName, u.LastName, u.UserName 
        FROM Employee e 
        JOIN User u ON e.UserName = u.UserName 
        WHERE e.ManagerID = ? AND u.Status = 'Active'
        ORDER BY u.FirstName";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $mgr_manager_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $employees[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = isset($_POST['mgr_employeeId']) ? (int)$_POST['mgr_employeeId'] : 0;
    $title = sanitizeInput($_POST['mgr_title'] ?? '');
    $description = sanitizeInput($_POST['mgr_description'] ?? '');
    $dueDate = sanitizeInput($_POST['mgr_dueDate'] ?? '');

    if (empty($employeeId) || empty($title) || empty($description) || empty($dueDate)) {
        $message = "All fields are required!";
        $messageType = "error";
    } else {
        $sql = "SELECT EmployeeID FROM Employee WHERE EmployeeID = ? AND ManagerID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $employeeId, $mgr_manager_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 0) {
            $message = "Invalid employee selected!";
            $messageType = "error";
        } else {
            $assignedDate = date('Y-m-d');
            $status = 'Pending';
            
            $sql = "INSERT INTO Task (ManagerID, EmployeeID, Title, Description, T_Status, AssignedDate, DueDate) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iisssss", $mgr_manager_id, $employeeId, $title, $description, $status, $assignedDate, $dueDate);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Task assigned successfully!";
                $messageType = "success";
                $_POST = [];
            } else {
                $message = "Error assigning task: " . mysqli_error($conn);
                $messageType = "error";
            }
        }
    }
}

$sql = "SELECT t.*, u.FirstName, u.LastName 
        FROM Task t 
        JOIN Employee e ON t.EmployeeID = e.EmployeeID 
        JOIN User u ON e.UserName = u.UserName 
        WHERE t.ManagerID = ? 
        ORDER BY t.AssignedDate DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $mgr_manager_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $assignedTasks[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Task - WorkNest</title>
    <link rel="stylesheet" href="manager_dashboard.css">
    <link rel="stylesheet" href="manager_assign_task.css">
</head>
<body>
    <nav class="mgrSidebar">
        <div class="mgrSidebar_header">
            <h1 class="mgrSidebar_logo">WorkNest</h1>
            <span class="mgrSidebar_role">Manager Panel</span>
        </div>
        <ul class="mgrSidebar_nav">
            <li class="mgrSidebar_navItem">
                <a href="manager_dashboard.php" class="mgrSidebar_navLink">
                    <span class="mgrSidebar_icon">📊</span> Dashboard
                </a>
            </li>
            <li class="mgrSidebar_navItem">
                <a href="manager_leave_requests.php" class="mgrSidebar_navLink">
                    <span class="mgrSidebar_icon">📋</span> Leave Requests
                </a>
            </li>
            <li class="mgrSidebar_navItem">
                <a href="manager_promotions.php" class="mgrSidebar_navLink">
                    <span class="mgrSidebar_icon">⬆️</span> Promotion Requests
                </a>
            </li>
            <li class="mgrSidebar_navItem active">
                <a href="manager_assign_task.php" class="mgrSidebar_navLink">
                    <span class="mgrSidebar_icon">📝</span> Assign Task
                </a>
            </li>
            <li class="mgrSidebar_navItem">
                <a href="manager_apply_leave.php" class="mgrSidebar_navLink">
                    <span class="mgrSidebar_icon">🏖️</span> Apply Leave
                </a>
            </li>
            <li class="mgrSidebar_navItem">
                <a href="manager_profile.php" class="mgrSidebar_navLink">
                    <span class="mgrSidebar_icon">👤</span> My Profile
                </a>
            </li>
            <li class="mgrSidebar_navItem">
                <a href="../logout.php" class="mgrSidebar_navLink logout">
                    <span class="mgrSidebar_icon">🚪</span> Logout
                </a>
            </li>
        </ul>
    </nav>

    <main class="mgrMain">
        <header class="mgrHeader">
            <div class="mgrHeader_content">
                <h1 class="mgrHeader_title">Assign Task</h1>
                <p class="mgrHeader_subtitle">Assign tasks to your team members</p>
            </div>
        </header>

        <div class="mgrForm_card">
            <h2 class="mgrForm_title">📝 New Task Assignment</h2>
            
            <?php if ($message): ?>
                <div class="mgrForm_message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mgrForm_grid">
                    <div class="mgrForm_group">
                        <label class="mgrForm_label" for="mgr_employeeId">Assign To <span class="required">*</span></label>
                        <select id="mgr_employeeId" name="mgr_employeeId" class="mgrForm_input" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['EmployeeID']; ?>">
                                    <?php echo htmlspecialchars($emp['FirstName'] . ' ' . $emp['LastName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mgrForm_group">
                        <label class="mgrForm_label" for="mgr_dueDate">Due Date <span class="required">*</span></label>
                        <input type="date" id="mgr_dueDate" name="mgr_dueDate" class="mgrForm_input" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                    </div>

                    <div class="mgrForm_group full">
                        <label class="mgrForm_label" for="mgr_title">Task Title <span class="required">*</span></label>
                        <input type="text" id="mgr_title" name="mgr_title" class="mgrForm_input" placeholder="Enter task title" value="<?php echo $_POST['mgr_title'] ?? ''; ?>" required>
                    </div>

                    <div class="mgrForm_group full">
                        <label class="mgrForm_label" for="mgr_description">Task Description <span class="required">*</span></label>
                        <textarea id="mgr_description" name="mgr_description" class="mgrForm_textarea" placeholder="Describe the task in detail" required><?php echo $_POST['mgr_description'] ?? ''; ?></textarea>
                    </div>

                    <button type="submit" class="mgrForm_submit">📤 Assign Task</button>
                </div>
            </form>
        </div>

        <div class="mgrTasks_container">
            <h2 class="mgrTasks_title">📋 Assigned Tasks</h2>
            <div class="mgrTasks_tableContainer">
                <table class="mgrTasks_table" id="mgrTasks_table">
                    <thead>
                        <tr>
                            <th>Task Title</th>
                            <th>Assigned To</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Assigned Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($assignedTasks) > 0): ?>
                            <?php foreach ($assignedTasks as $task): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($task['Title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($task['FirstName'] . ' ' . $task['LastName']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($task['DueDate'])); ?></td>
                                    <td><span class="mgrStatus_badge mgrStatus_<?php echo strtolower($task['T_Status']); ?>"><?php echo $task['T_Status']; ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($task['AssignedDate'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="mgrTasks_empty">No tasks assigned yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>