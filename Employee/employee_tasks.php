<?php
require_once 'employee_config.php';

// Get all tasks for this employee
$tasks = [];
$sql = "SELECT * FROM Task WHERE EmployeeID = ? ORDER BY 
        CASE T_Status 
            WHEN 'In_Progress' THEN 1 
            WHEN 'Pending' THEN 2 
            WHEN 'Completed' THEN 3 
        END, DueDate ASC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $emp_employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $tasks[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - WorkNest</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="employee_dashboard.css">
</head>
<body>
    <nav class="empSidebar">
        <div class="empSidebar_header">
            <h1 class="empSidebar_logo">WorkNest</h1>
            <span class="empSidebar_role">Employee Panel</span>
        </div>
        <ul class="empSidebar_nav">
            <li class="empSidebar_navItem"><a href="employee_dashboard.php" class="empSidebar_navLink"><span class="empSidebar_icon">📊</span> Dashboard</a></li>
            <li class="empSidebar_navItem active"><a href="employee_tasks.php" class="empSidebar_navLink"><span class="empSidebar_icon">📝</span> My Tasks</a></li>
            <li class="empSidebar_navItem"><a href="employee_leave.php" class="empSidebar_navLink"><span class="empSidebar_icon">🏖️</span> Apply Leave</a></li>
            <li class="empSidebar_navItem"><a href="employee_promotion.php" class="empSidebar_navLink"><span class="empSidebar_icon">⬆️</span> Apply for Promotion</a></li>
            <li class="empSidebar_navItem"><a href="employee_profile.php" class="empSidebar_navLink"><span class="empSidebar_icon">👤</span> My Profile</a></li>
            <li class="empSidebar_navItem"><a href="../logout.php" class="empSidebar_navLink logout"><span class="empSidebar_icon">🚪</span> Logout</a></li>
        </ul>
    </nav>

    <main class="empMain">
        <header class="empHeader">
            <div class="empHeader_content">
                <h1 class="empHeader_title">My Tasks</h1>
                <p class="empHeader_subtitle">View, accept and complete your assigned tasks</p>
            </div>
        </header>

        <div class="empFilter">
            <select id="empFilter_status" onchange="filterTasks()">
                <option value="all">All Status</option>
                <option value="Pending">Pending</option>
                <option value="In_Progress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>
            <input type="text" id="empSearch_input" placeholder="Search by title..." onkeyup="filterTasks()">
        </div>

        <div class="empTableContainer">
            <table class="empTaskTable" id="empTask_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title & Description</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="empTask_body">
                    <?php if (count($tasks) > 0): ?>
                        <?php foreach ($tasks as $task): 
                            $statusClass = strtolower($task['T_Status']);
                        ?>
                            <tr>
                                <td>T-<?php echo $task['TaskID']; ?></td>
                                <td><strong><?php echo htmlspecialchars($task['Title']); ?></strong><br>
                                    <small style="color:#64748b;"><?php echo htmlspecialchars(substr($task['Description'], 0, 80)) . '...'; ?></small>
                                </td>
                                <td><span class="empStatus_badge empStatus_<?php echo $statusClass; ?>"><?php echo str_replace('_', ' ', $task['T_Status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($task['DueDate'])); ?></td>
                                <td class="empTaskActions">
                                    <?php if ($task['T_Status'] === 'Pending'): ?>
                                        <button class="empAction_btn empAction_btn_accept" onclick="handleTaskAction(<?php echo $task['TaskID']; ?>, 'accept')">Accept</button>
                                    <?php elseif ($task['T_Status'] === 'In_Progress'): ?>
                                        <button class="empAction_btn empAction_btn_complete" onclick="handleTaskAction(<?php echo $task['TaskID']; ?>, 'complete')">Mark Complete</button>
                                    <?php else: ?>
                                        <span style="color:#94a3b8; font-size:12px;">Done ✓</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="empEmptyState">No tasks assigned yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="employee_tasks.js"></script>
</body>
</html>