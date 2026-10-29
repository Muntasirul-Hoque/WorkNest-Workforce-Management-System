<?php
require_once 'hr_config.php';

$hr_first_name = $_SESSION['first_name'] ?? 'HR';
$hr_last_name = $_SESSION['last_name'] ?? 'User';

$conn = getDBConnection();
$message = '';
$messageType = '';
$employeeToTerminate = null;

$employeeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($employeeId > 0) {
    $employeeToTerminate = getEmployeeById($conn, $employeeId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hr_terminate'])) {
    $employeeId = (int)$_POST['hr_employeeId'];
    
    $sql = "UPDATE User SET Status = 'Inactive' WHERE UserName = (SELECT UserName FROM Employee WHERE EmployeeID = ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $employeeId);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Employee terminated successfully!";
        $messageType = "success";
        $employeeToTerminate = null;
    } else {
        $message = "Error terminating employee: " . mysqli_error($conn);
        $messageType = "error";
    }
}

$employees = getAllActiveEmployees($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminate Employee - WorkNest</title>
    <link rel="stylesheet" href="hr_dashboard.css">
    <link rel="stylesheet" href="hr_terminate.css">
</head>
<body>
    <nav class="hrSidebar">
        <div class="hrSidebar_header">
            <h1 class="hrSidebar_logo">WorkNest</h1>
            <span class="hrSidebar_role">HR Panel</span>
        </div>
        <ul class="hrSidebar_nav">
            <li class="hrSidebar_navItem"><a href="hr_dashboard.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">📊</span>Dashboard</a></li>
            <li class="hrSidebar_navItem"><a href="hr_update_employee.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">✏️</span>Update Employee</a></li>
            <li class="hrSidebar_navItem"><a href="hr_recruit.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">➕</span>Recruit Employee</a></li>
            <li class="hrSidebar_navItem"><a href="hr_promote.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">⬆️</span>Promote Employee</a></li>
            <li class="hrSidebar_navItem active"><a href="hr_terminate.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">🚫</span>Terminate Employee</a></li>
            <li class="hrSidebar_navItem"><a href="hr_leave_requests.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">📋</span>Leave Requests</a></li>
            <li class="hrSidebar_navItem"><a href="hr_profile.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">👤</span>My Profile</a></li>
            <li class="hrSidebar_navItem"><a href="../logout.php" class="hrSidebar_navLink logout"><span class="hrSidebar_icon">🚪</span>Logout</a></li>
        </ul>
    </nav>

    <main class="hrMain">
        <header class="hrHeader">
            <h1 class="hrHeader_title">Terminate Employee</h1>
            <p class="hrHeader_subtitle">Search and terminate an employee</p>
        </header>

        <?php if ($message): ?>
            <div class="hrForm_message <?php echo $messageType; ?>" style="max-width: 800px; margin-bottom: 20px;"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="hrTerminate_container">
            <div class="hrTerminate_card">
                <h3 style="margin-bottom: 16px;">Select Employee</h3>
                <form method="GET" action="">
                    <select name="id" class="hrTerminate_select" onchange="this.form.submit()">
                        <option value="">Select an employee...</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp['EmployeeID']; ?>" <?php echo ($employeeId == $emp['EmployeeID']) ? 'selected' : ''; ?>>
                                <?php echo $emp['FirstName'] . ' ' . $emp['LastName'] . ' (' . $emp['Role'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <?php if (count($employees) === 0): ?>
                    <p style="color: #64748b;">No active employees found to terminate.</p>
                <?php endif; ?>
            </div>

            <div class="hrTerminate_card">
                <h3 style="margin-bottom: 16px;">Employee Details</h3>
                <?php if ($employeeToTerminate): ?>
                    <div class="hrTerminate_employeeInfo">
                        <p><strong>Name:</strong> <?php echo $employeeToTerminate['FirstName'] . ' ' . $employeeToTerminate['LastName']; ?></p>
                        <p><strong>Email:</strong> <?php echo $employeeToTerminate['Email']; ?></p>
                        <p><strong>Role:</strong> <?php echo $employeeToTerminate['Role']; ?></p>
                        <p><strong>Status:</strong> <span class="hrStatus_badge hrStatus_active"><?php echo $employeeToTerminate['Status']; ?></span></p>
                    </div>
                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to terminate this employee? This action cannot be undone.')">
                        <input type="hidden" name="hr_employeeId" value="<?php echo $employeeToTerminate['EmployeeID']; ?>">
                        <button type="submit" name="hr_terminate" class="hrTerminate_confirmBtn">🚫 Terminate Employee</button>
                    </form>
                <?php else: ?>
                    <p style="color: #94a3b8;">Select an employee from the list to view details.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="hr_terminate.js"></script>
</body>
</html>