<?php
require_once 'employee_config.php';

$message = '';
$messageType = '';

// Handle leave application
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sd = sanitizeInput($_POST['emp_sd'] ?? '');
    $ed = sanitizeInput($_POST['emp_ed'] ?? '');
    $reason = sanitizeInput($_POST['emp_reason'] ?? '');

    if (empty($sd) || empty($ed) || empty($reason)) {
        $message = "All fields are required!";
        $messageType = "error";
    } else {
        $today = date('Y-m-d');
        $sdDate = new DateTime($sd);
        $edDate = new DateTime($ed);
        $diff = $sdDate->diff($edDate)->days;

        if ($sd < $today) {
            $message = "Start date cannot be in the past!";
            $messageType = "error";
        } elseif ($ed < $sd) {
            $message = "End date must be after start date!";
            $messageType = "error";
        } elseif ($diff > 30) {
            $message = "Leave duration cannot exceed 30 days!";
            $messageType = "error";
        } else {
            // Check for active leave request
            $sql = "SELECT LeaveID FROM `Leave` WHERE UserName = ? AND Status IN ('Pending', 'Processing')";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $emp_user_name);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $message = "You already have an active leave request!";
                $messageType = "error";
            } else {
                $status = 'Pending';
                $sql = "INSERT INTO `Leave` (UserName, SD, ED, Reason, Status) VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssss", $emp_user_name, $sd, $ed, $reason, $status);

                if (mysqli_stmt_execute($stmt)) {
                    $message = "Leave application submitted successfully! Your manager will review it.";
                    $messageType = "success";
                    $_POST = [];
                } else {
                    $message = "Error submitting leave: " . mysqli_error($conn);
                    $messageType = "error";
                }
            }
        }
    }
}

// Get leave history
$leaveHistory = [];
$sql = "SELECT * FROM `Leave` WHERE UserName = ? ORDER BY SD DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $emp_user_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $leaveHistory[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Leave - WorkNest</title>
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
            <li class="empSidebar_navItem"><a href="employee_tasks.php" class="empSidebar_navLink"><span class="empSidebar_icon">📝</span> My Tasks</a></li>
            <li class="empSidebar_navItem active"><a href="employee_leave.php" class="empSidebar_navLink"><span class="empSidebar_icon">🏖️</span> Apply Leave</a></li>
            <li class="empSidebar_navItem"><a href="employee_promotion.php" class="empSidebar_navLink"><span class="empSidebar_icon">⬆️</span> Apply for Promotion</a></li>
            <li class="empSidebar_navItem"><a href="employee_profile.php" class="empSidebar_navLink"><span class="empSidebar_icon">👤</span> My Profile</a></li>
            <li class="empSidebar_navItem"><a href="../logout.php" class="empSidebar_navLink logout"><span class="empSidebar_icon">🚪</span> Logout</a></li>
        </ul>
    </nav>

    <main class="empMain">
        <header class="empHeader">
            <div class="empHeader_content">
                <h1 class="empHeader_title">Apply Leave</h1>
                <p class="empHeader_subtitle">Submit a leave request to your manager</p>
            </div>
        </header>

        <div class="empForm">
            <h2 class="empForm_title">🏖️ New Leave Application</h2>

            <?php if ($message): ?>
                <div class="empForm_message <?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="empForm_grid">
                    <div class="empForm_group">
                        <label class="empForm_label">Start Date <span class="required">*</span></label>
                        <input type="date" name="emp_sd" class="empForm_input" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>

                    <div class="empForm_group">
                        <label class="empForm_label">End Date <span class="required">*</span></label>
                        <input type="date" name="emp_ed" class="empForm_input" value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" required>
                    </div>

                    <div class="empForm_group full">
                        <label class="empForm_label">Reason <span class="required">*</span></label>
                        <textarea name="emp_reason" class="empForm_textarea" placeholder="Explain the reason for your leave" required></textarea>
                    </div>

                    <button type="submit" class="empForm_submit">📤 Submit Leave Request</button>
                </div>
            </form>
        </div>

        <div class="empTableContainer">
            <h2 class="empTableTitle">📋 My Leave History</h2>
            <div class="empTableScroll">
                <table class="empTaskTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($leaveHistory) > 0): ?>
                            <?php foreach ($leaveHistory as $leave): 
                                $sd = new DateTime($leave['SD']);
                                $ed = new DateTime($leave['ED']);
                                $days = $sd->diff($ed)->days + 1;
                                $statusClass = strtolower($leave['Status']);
                            ?>
                                <tr>
                                    <td><?php echo $leave['LeaveID']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($leave['SD'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($leave['ED'])); ?></td>
                                    <td><?php echo $days; ?></td>
                                    <td><?php echo htmlspecialchars(substr($leave['Reason'], 0, 40)) . '...'; ?></td>
                                    <td><span class="empStatus_badge empStatus_<?php echo $statusClass; ?>"><?php echo $leave['Status']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="empEmptyState">No leave history yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>