<?php
require_once 'manager_config.php';
$conn = getDBConnection();
$sql = "SELECT p.*, u.FirstName, u.LastName, u.Dept, u.Role as CurrentRole 
        FROM Promotion p 
        JOIN Employee e ON p.EmployeeID = e.EmployeeID 
        JOIN User u ON e.UserName = u.UserName 
        WHERE e.ManagerID = ? AND p.Status = 'Pending'
        ORDER BY p.PromotionDate ASC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $mgr_manager_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$promotions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $promotions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Requests - WorkNest</title>
    <link rel="stylesheet" href="manager_dashboard.css">
    <style>
        .mgrPage_table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .mgrPage_table th { text-align: left; padding: 14px 12px; background: #f8fafc; color: #475569; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        .mgrPage_table td { padding: 12px; border-bottom: 1px solid #f1f5f9; }
        .mgrPage_table tr:hover td { background: #f8fafc; }
        .mgrPage_tableContainer { background: #fff; border-radius: 16px; padding: 20px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow-x: auto; }
        .mgrPage_empty { text-align: center; color: #94a3b8; padding: 30px 0; }
    </style>
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
            <li class="mgrSidebar_navItem active">
                <a href="manager_promotions.php" class="mgrSidebar_navLink">
                    <span class="mgrSidebar_icon">⬆️</span> Promotion Requests
                </a>
            </li>
            <li class="mgrSidebar_navItem">
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
                <h1 class="mgrHeader_title">Promotion Requests</h1>
                <p class="mgrHeader_subtitle">Review promotion applications from your team</p>
            </div>
        </header>

        <div class="mgrPage_tableContainer">
            <table class="mgrPage_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Current Role</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($promotions) > 0): ?>
                        <?php foreach ($promotions as $promo): ?>
                            <tr>
                                <td><?php echo $promo['PromotionID']; ?></td>
                                <td><?php echo htmlspecialchars($promo['FirstName'] . ' ' . $promo['LastName']); ?></td>
                                <td><?php echo htmlspecialchars($promo['Dept'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($promo['CurrentRole'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($promo['PromotionDate'])); ?></td>
                                <td><span class="mgrStatus_badge mgrStatus_pending"><?php echo $promo['Status']; ?></span></td>
                                <td>
                                    <button class="mgrAction_btn mgrAction_btn_approve" onclick="handlePromotionAction(<?php echo $promo['PromotionID']; ?>, 'Processing')">Accept</button>
                                    <button class="mgrAction_btn mgrAction_btn_reject" onclick="handlePromotionAction(<?php echo $promo['PromotionID']; ?>, 'Rejected')">Reject</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="mgrPage_empty">No pending promotion requests found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function handlePromotionAction(promotionId, action) {
            if (!confirm(`Are you sure you want to ${action} this promotion request?`)) return;
            
            const formData = new FormData();
            formData.append('promotion_id', promotionId);
            formData.append('action', action);

            fetch('manager_promotion_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Error processing request');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    </script>
</body>
</html>