<?php
require_once 'hr_config.php';

$hr_first_name = $_SESSION['first_name'] ?? 'HR';
$hr_last_name = $_SESSION['last_name'] ?? 'User';

$conn = getDBConnection();
$message = '';
$messageType = '';
$pendingPromotions = [];
$promotionHistory = [];
$current_user_role = $_SESSION['user_role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $promotionId = isset($_POST['hr_promotionId']) ? (int)$_POST['hr_promotionId'] : 0;
    $action = isset($_POST['hr_action']) ? sanitizeInput($_POST['hr_action']) : '';
    $newRole = isset($_POST['hr_newRole']) ? sanitizeInput($_POST['hr_newRole']) : '';
    $newSalary = isset($_POST['hr_newSalary']) ? (float)$_POST['hr_newSalary'] : 0;
    
    if (empty($promotionId) || empty($action)) {
        $message = "Missing required parameters!";
        $messageType = "error";
    } else {
        if ($current_user_role === 'Manager') {
            if ($action !== 'Processing') {
                $message = "Managers can only move promotions to Processing.";
                $messageType = "error";
            } else {
                $sql = "UPDATE Promotion SET Status = 'Processing' WHERE PromotionID = ? AND Status = 'Pending'";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $promotionId);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Promotion request moved to Processing.";
                    $messageType = "success";
                } else {
                    $message = "Error updating request.";
                    $messageType = "error";
                }
            }
        } elseif ($current_user_role === 'HR') {
            if ($action === 'Approved') {
                if (empty($newRole)) {
                    $message = "Please select a new role for the promotion.";
                    $messageType = "error";
                } else {
                    mysqli_begin_transaction($conn);
                    try {
                        $sql = "UPDATE Promotion SET Status = 'Approved' WHERE PromotionID = ? AND Status = 'Processing'";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "i", $promotionId);
                        mysqli_stmt_execute($stmt);
                        
                        $sql = "SELECT e.UserName, u.Salary FROM Promotion p JOIN Employee e ON p.EmployeeID = e.EmployeeID JOIN User u ON e.UserName = u.UserName WHERE p.PromotionID = ?";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "i", $promotionId);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        $promotion = mysqli_fetch_assoc($result);
                        
                        $sql = "UPDATE User SET Role = ?, Salary = ? WHERE UserName = ?";
                        $stmt = mysqli_prepare($conn, $sql);
                        if ($newSalary > 0) {
                            mysqli_stmt_bind_param($stmt, "sds", $newRole, $newSalary, $promotion['UserName']);
                        } else {
                            mysqli_stmt_bind_param($stmt, "sds", $newRole, $promotion['Salary'], $promotion['UserName']);
                        }
                        mysqli_stmt_execute($stmt);
                        
                        mysqli_commit($conn);
                        $message = "Promotion approved successfully!";
                        $messageType = "success";
                    } catch (Exception $e) {
                        mysqli_rollback($conn);
                        $message = "Error: " . $e->getMessage();
                        $messageType = "error";
                    }
                }
            } elseif ($action === 'Rejected') {
                $sql = "UPDATE Promotion SET Status = 'Rejected' WHERE PromotionID = ? AND Status = 'Processing'";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $promotionId);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Promotion request rejected.";
                    $messageType = "success";
                } else {
                    $message = "Error updating request.";
                    $messageType = "error";
                }
            } else {
                $message = "Invalid HR action.";
                $messageType = "error";
            }
        } else {
            $message = "Unauthorized.";
            $messageType = "error";
        }
    }
}

if ($current_user_role === 'Manager') {
    $pendingPromotions = getPromotionsByStatus($conn, 'Pending');
} else {
    $pendingPromotions = getPromotionsByStatus($conn, 'Processing');
}

if ($current_user_role === 'HR') {
    $sql = "SELECT p.*, u.FirstName, u.LastName, u.Role as CurrentRole, u.Dept, u.Salary
            FROM Promotion p 
            JOIN Employee e ON p.EmployeeID = e.EmployeeID 
            JOIN User u ON e.UserName = u.UserName 
            ORDER BY p.PromotionDate DESC LIMIT 50";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) { $promotionHistory[] = $row; }
    }
}

function getPromotionsByStatus($conn, $status) {
    $sql = "SELECT p.*, u.FirstName, u.LastName, u.Dept, u.Role
            FROM Promotion p 
            JOIN Employee e ON p.EmployeeID = e.EmployeeID 
            JOIN User u ON e.UserName = u.UserName 
            WHERE p.Status = ? ORDER BY p.PromotionDate DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $promotions = [];
    while ($row = mysqli_fetch_assoc($result)) { $promotions[] = $row; }
    return $promotions;
}

$availableRoles = [
    'Software_Developer' => 'Software Developer', 'Software_Designer' => 'Software Designer',
    'Software_Tester' => 'Software Tester', 'Software_Manager' => 'Software Manager',
    'Salesman' => 'Salesman', 'Digital_Marketing_Specialist' => 'Digital Marketing Specialist',
    'Content_Marketing_Specialist' => 'Content Marketing Specialist', 'PR_Officer' => 'PR Officer',
    'Marketing_Manager' => 'Marketing Manager', 'Driver' => 'Driver', 'Cleaner' => 'Cleaner',
    'Watchman' => 'Watchman', 'Facilities_Manager' => 'Facilities Manager'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promote Employee - WorkNest</title>
    <link rel="stylesheet" href="hr_dashboard.css">
    <link rel="stylesheet" href="hr_promote.css">
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
            <li class="hrSidebar_navItem active"><a href="hr_promote.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">⬆️</span>Promote Employee</a></li>
            <li class="hrSidebar_navItem"><a href="hr_terminate.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">🚫</span>Terminate Employee</a></li>
            <li class="hrSidebar_navItem"><a href="hr_leave_requests.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">📋</span>Leave Requests</a></li>
            <li class="hrSidebar_navItem"><a href="hr_profile.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">👤</span>My Profile</a></li>
            <li class="hrSidebar_navItem"><a href="../logout.php" class="hrSidebar_navLink logout"><span class="hrSidebar_icon">🚪</span>Logout</a></li>
        </ul>
    </nav>

    <main class="hrMain">
        <header class="hrHeader">
            <div class="hrHeader_content">
                <h1 class="hrHeader_title">Promote Employee</h1>
                <p class="hrHeader_subtitle">Review and process promotion requests</p>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="hrForm_message <?php echo $messageType; ?>" style="padding: 12px; border-radius: 8px; margin-bottom: 20px; <?php echo $messageType === 'success' ? 'background: #d1fae5; color: #059669;' : 'background: #fecaca; color: #dc2626;'; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="hrPromote_container">
            <div class="hrPromote_card">
                <div class="hrPromote_cardTitle">
                    <span>📋 Promotion Requests</span>
                    <span class="hrPromote_badge hrPromote_badge_pending"><?php echo count($pendingPromotions); ?> requests</span>
                </div>
                
                <?php if (count($pendingPromotions) > 0): ?>
                    <?php foreach ($pendingPromotions as $promotion): ?>
                        <?php $promoID = $promotion['PromotionID']; ?>
                        <div class="hrPromote_item">
                            <div class="hrPromote_itemHeader">
                                <span class="hrPromote_itemName"><?php echo $promotion['FirstName'] . ' ' . $promotion['LastName']; ?></span>
                                <span class="hrPromote_badge <?php echo 'hrPromote_badge_' . strtolower($promotion['Status']); ?>"><?php echo $promotion['Status']; ?></span>
                            </div>
                            <div class="hrPromote_itemDetails">
                                <span>📂 <?php echo $promotion['Dept'] ?? 'N/A'; ?></span>
                                <span>👤 Current: <?php echo $promotion['Role'] ?? 'N/A'; ?></span>
                                <span>📅 <?php echo date('M d, Y', strtotime($promotion['PromotionDate'])); ?></span>
                            </div>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="hr_promotionId" value="<?php echo $promoID; ?>">
                                
                                <div id="hrPhase1_<?php echo $promoID; ?>" class="hrPromote_itemActions">
                                    <?php if ($current_user_role === 'Manager'): ?>
                                        <button type="submit" name="hr_action" value="Processing" class="hrAction_btn hrAction_btn_processing">✅ Move to Processing</button>
                                    <?php else: ?>
                                        <button type="button" onclick="showPhase2(<?php echo $promoID; ?>)" class="hrAction_btn hrAction_btn_approve"> Approve</button>
                                        <button type="submit" name="hr_action" value="Rejected" class="hrAction_btn hrAction_btn_reject"> Reject</button>
                                    <?php endif; ?>
                                </div>

                                <?php if ($current_user_role === 'HR'): ?>
                                <div id="hrPhase2_<?php echo $promoID; ?>" class="hrPromote_itemActions" style="display: none;">
                                    <label style="font-size: 13px; color: #475569; font-weight: 500;">New Role:</label>
                                    <select name="hr_newRole" id="hrRole_<?php echo $promoID; ?>" required>
                                        <option value="">Select Role...</option>
                                        <?php foreach ($availableRoles as $key => $label): ?>
                                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <label style="font-size: 13px; color: #475569; font-weight: 500;">New Salary:</label>
                                    <input type="number" step="0.01" name="hr_newSalary" placeholder="Optional" min="0">
                                    
                                    <button type="submit" name="hr_action" value="Approved" class="hrAction_btn hrAction_btn_approve"> Approve</button>
                                    <button type="button" onclick="showPhase1(<?php echo $promoID; ?>)" class="hrAction_btn hrAction_btn_processing"> Cancel</button>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="hrPromote_empty">
                        <p style="font-size: 16px;">🎉 No requests found</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="hrPromote_card">
                <div class="hrPromote_cardTitle">
                    <span>📜 Promotion History</span>
                    <span class="hrPromote_badge" style="background: #e2e8f0; color: #475569;"><?php echo count($promotionHistory); ?> records</span>
                </div>
                
                <?php if ($current_user_role === 'HR'): ?>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if (count($promotionHistory) > 0): ?>
                        <table class="hrPromote_table">
                            <thead>
                                <tr><th>Employee</th><th>Department</th><th>Status</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($promotionHistory as $history): ?>
                                    <tr>
                                        <td><strong><?php echo $history['FirstName'] . ' ' . $history['LastName']; ?></strong></td>
                                        <td><?php echo $history['Dept'] ?? 'N/A'; ?></td>
                                        <td><span class="hrPromote_badge <?php echo 'hrPromote_badge_' . strtolower($history['Status']); ?>"><?php echo $history['Status']; ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($history['PromotionDate'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="hrPromote_empty"><p>No promotion history available</p></div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <div class="hrPromote_empty"><p>Only HR can view promotion history.</p></div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="hr_promote.js"></script>
</body>
</html>