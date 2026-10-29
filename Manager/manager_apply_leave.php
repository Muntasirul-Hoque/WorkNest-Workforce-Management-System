<?php
require_once 'manager_config.php';
$conn = getDBConnection();
$message = '';
$messageType = '';
$leaveHistory = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sd = sanitizeInput($_POST['mgr_sd'] ?? '');
    $ed = sanitizeInput($_POST['mgr_ed'] ?? '');
    $reason = sanitizeInput($_POST['mgr_reason'] ?? '');

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
        } elseif ($diff > 7) {
            $message = "Leave duration cannot exceed 7 days!";
            $messageType = "error";
        } else {
            $sql = "SELECT Status FROM User WHERE UserName = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $mgr_user_name);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            
            if ($user['Status'] === 'On_leave') {
                $message = "You are already on leave!";
                $messageType = "error";
            } else {
                $sql = "SELECT LeaveID FROM `Leave` WHERE UserName = ? AND Status IN ('Pending', 'Processing')";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "s", $mgr_user_name);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    $message = "You already have an active leave request!";
                    $messageType = "error";
                } else {
                    $status = 'Processing';
                    $sql = "INSERT INTO `Leave` (UserName, SD, ED, Reason, Status) VALUES (?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "sssss", $mgr_user_name, $sd, $ed, $reason, $status);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "Leave application submitted successfully! (Forwarded to HR)";
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
}

$sql = "SELECT * FROM `Leave` WHERE UserName = ? ORDER BY SD DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $mgr_user_name);
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
    <link rel="stylesheet" href="manager_dashboard.css">
    <link rel="stylesheet" href="manager_apply_leave.css">
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
            <li class="mgrSidebar_navItem">
                <a href="manager_assign_task.php" class="mgrSidebar_navLink">
                    <span class="mgrSidebar_icon">📝</span> Assign Task
                </a>
            </li>
            <li class="mgrSidebar_navItem active">
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
                <h1 class="mgrHeader_title">Apply Leave</h1>
                <p class="mgrHeader_subtitle">Submit a leave request (max 7 days)</p>
            </div>
        </header>

        <div class="mgrForm_card">
            <h2 class="mgrForm_title">🏖️ New Leave Application</h2>
            
            <?php if ($message): ?>
                <div class="mgrForm_message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mgrForm_grid">
                    <div class="mgrForm_group">
                        <label class="mgrForm_label" for="mgr_sd">Start Date <span class="required">*</span></label>
                        <input type="date" id="mgr_sd" name="mgr_sd" class="mgrForm_input" value="<?php echo $_POST['mgr_sd'] ?? date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>

                    <div class="mgrForm_group">
                        <label class="mgrForm_label" for="mgr_ed">End Date <span class="required">*</span></label>
                        <input type="date" id="mgr_ed" name="mgr_ed" class="mgrForm_input" value="<?php echo $_POST['mgr_ed'] ?? date('Y-m-d', strtotime('+3 days')); ?>" required>
                    </div>

                    <div class="mgrForm_group full">
                        <label class="mgrForm_label" for="mgr_reason">Reason <span class="required">*</span></label>
                        <textarea id="mgr_reason" name="mgr_reason" class="mgrForm_textarea" placeholder="Explain the reason for your leave" required><?php echo $_POST['mgr_reason'] ?? ''; ?></textarea>
                    </div>

                    <div class="mgrForm_info">
                        <p>⏰ Maximum duration: <strong>7 days</strong></p>
                        <p>📌 Your request will be sent directly to HR for approval</p>
                    </div>

                    <button type="submit" class="mgrForm_submit">📤 Submit Leave Request</button>
                </div>
            </form>
        </div>

        <div class="mgrHistory_container">
            <h2 class="mgrHistory_title">📋 Your Leave History</h2>
            <div class="mgrHistory_tableContainer">
                <table class="mgrHistory_table">
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
                                $days = $sd->diff($ed)->days;
                                $statusClass = strtolower($leave['Status']);
                            ?>
                                <tr>
                                    <td><?php echo $leave['LeaveID']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($leave['SD'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($leave['ED'])); ?></td>
                                    <td><?php echo $days + 1; ?></td>
                                    <td><?php echo htmlspecialchars(substr($leave['Reason'], 0, 40)) . '...'; ?></td>
                                    <td><span class="mgrStatus_badge mgrStatus_<?php echo $statusClass; ?>"><?php echo $leave['Status']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="mgrTasks_empty">No leave history found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>