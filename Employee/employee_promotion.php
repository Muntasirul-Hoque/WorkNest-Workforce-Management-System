<?php
require_once 'employee_config.php';

$message = '';
$messageType = '';

// Handle promotion application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emp_apply'])) {
    // Check for active promotion request (Pending or Processing)
    $sql = "SELECT PromotionID FROM Promotion WHERE EmployeeID = ? AND Status IN ('Pending', 'Processing')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $emp_employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $message = "You already have a pending promotion request! Please wait for it to be processed.";
        $messageType = "error";
    } else {
        $promotionDate = date('Y-m-d');
        $status = 'Pending';
        $sql = "INSERT INTO Promotion (EmployeeID, PromotionDate, Status) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $emp_employee_id, $promotionDate, $status);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Promotion application submitted successfully! Your manager will review it.";
            $messageType = "success";
        } else {
            $message = "Error submitting promotion: " . mysqli_error($conn);
            $messageType = "error";
        }
    }
}

// Get promotion history
$promotionHistory = [];
$sql = "SELECT * FROM Promotion WHERE EmployeeID = ? ORDER BY PromotionDate DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $emp_employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $promotionHistory[] = $row;
}

// Check if employee has active promotion
$hasActivePromotion = false;
$sql = "SELECT PromotionID FROM Promotion WHERE EmployeeID = ? AND Status IN ('Pending', 'Processing')";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $emp_employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) > 0) {
    $hasActivePromotion = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Promotion - WorkNest</title>
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
            <li class="empSidebar_navItem"><a href="employee_leave.php" class="empSidebar_navLink"><span class="empSidebar_icon">🏖️</span> Apply Leave</a></li>
            <li class="empSidebar_navItem active"><a href="employee_promotion.php" class="empSidebar_navLink"><span class="empSidebar_icon">⬆️</span> Apply for Promotion</a></li>
            <li class="empSidebar_navItem"><a href="employee_profile.php" class="empSidebar_navLink"><span class="empSidebar_icon">👤</span> My Profile</a></li>
            <li class="empSidebar_navItem"><a href="../logout.php" class="empSidebar_navLink logout"><span class="empSidebar_icon">🚪</span> Logout</a></li>
        </ul>
    </nav>

    <main class="empMain">
        <header class="empHeader">
            <div class="empHeader_content">
                <h1 class="empHeader_title">Apply for Promotion</h1>
                <p class="empHeader_subtitle">Submit a promotion request to your manager</p>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="empForm_message <?php echo $messageType; ?>" style="max-width:800px;"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="empForm">
            <h2 class="empForm_title">⬆️ New Promotion Application</h2>

            <?php if ($hasActivePromotion): ?>
                <div class="empForm_message error">You already have a pending promotion request. Please wait for the manager's review.</div>
            <?php else: ?>
                <p style="color:#475569; margin-bottom:20px; font-size:14px;">Click the button below to submit your promotion request. Your manager will review it and forward it to HR.</p>
                <form method="POST" action="">
                    <button type="submit" name="emp_apply" class="empForm_submit">📤 Apply for Promotion</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="empTableContainer">
            <h2 class="empTableTitle">📜 My Promotion History</h2>
            <div class="empTableScroll">
                <table class="empTaskTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Submitted</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($promotionHistory) > 0): ?>
                            <?php foreach ($promotionHistory as $promo): 
                                $statusClass = strtolower($promo['Status']);
                            ?>
                                <tr>
                                    <td><?php echo $promo['PromotionID']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($promo['PromotionDate'])); ?></td>
                                    <td><span class="empStatus_badge empStatus_<?php echo $statusClass; ?>"><?php echo $promo['Status']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="empEmptyState">No promotion history yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>