<?php
require_once 'hr_config.php';
$conn = getDBConnection();
$current_user_role = $_SESSION['user_role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests - WorkNest</title>
    <link rel="stylesheet" href="hr_dashboard.css">
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
            <li class="hrSidebar_navItem"><a href="hr_terminate.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">🚫</span>Terminate Employee</a></li>
            <li class="hrSidebar_navItem active"><a href="hr_leave_requests.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">📋</span>Leave Requests</a></li>
            <li class="hrSidebar_navItem"><a href="hr_profile.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">👤</span>My Profile</a></li>
            <li class="hrSidebar_navItem"><a href="../logout.php" class="hrSidebar_navLink logout"><span class="hrSidebar_icon">🚪</span>Logout</a></li>
        </ul>
    </nav>

    <main class="hrMain">
        <header class="hrHeader">
            <h1 class="hrHeader_title">Leave Requests</h1>
            <p class="hrHeader_subtitle">Manage all leave requests</p>
        </header>

        <div class="hrPage_filter" style="display: flex; gap: 12px; margin-bottom: 20px;">
            <select id="hrFilter_status" onchange="filterLeaves()" style="padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <option value="all">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Processing">Processing</option>
                <option value="Accepted">Accepted</option>
                <option value="Rejected">Rejected</option>
            </select>
            <input type="text" id="hrSearch_input" placeholder="Search by name..." onkeyup="filterLeaves()" style="padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 8px; flex: 1;">
        </div>

        <div class="hrTableContainer">
            <table class="hrEmployeeTable" id="hrLeave_table">
                <thead>
                    <tr><th>ID</th><th>Employee</th><th>Department</th><th>Start Date</th><th>End Date</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody id="hrLeave_body">
                    <?php
                    if ($current_user_role === 'Manager') {
                        $sql = "SELECT l.*, u.FirstName, u.LastName, u.Dept FROM `Leave` l JOIN User u ON l.UserName = u.UserName WHERE l.Status = 'Pending'";
                    } else {
                        $sql = "SELECT l.*, u.FirstName, u.LastName, u.Dept FROM `Leave` l JOIN User u ON l.UserName = u.UserName WHERE l.Status = 'Processing'";
                    }
                    $result = mysqli_query($conn, $sql);
                    
                    if (mysqli_num_rows($result) > 0) {
                        while ($leave = mysqli_fetch_assoc($result)) {
                            $statusClass = strtolower($leave['Status']);
                            echo "<tr>
                                <td>{$leave['LeaveID']}</td>
                                <td>{$leave['FirstName']} {$leave['LastName']}</td>
                                <td>{$leave['Dept']}</td>
                                <td>{$leave['SD']}</td>
                                <td>{$leave['ED']}</td>
                                <td>" . substr($leave['Reason'], 0, 50) . "...</td>
                                <td><span class='hrStatus_badge hrStatus_{$statusClass}'>{$leave['Status']}</span></td>
                                <td>";
                            
                            if ($current_user_role === 'Manager') {
                                echo "<button class='hrAction_btn hrAction_btn_processing' onclick='handleLeaveAction({$leave['LeaveID']}, \"Processing\")'>✅ Move to Processing</button>";
                            } else {
                                echo "<button class='hrAction_btn hrAction_btn_approve' onclick='handleLeaveAction({$leave['LeaveID']}, \"Accepted\")'>Accept</button>
                                      <button class='hrAction_btn hrAction_btn_reject' onclick='handleLeaveAction({$leave['LeaveID']}, \"Rejected\")'>Reject</button>";
                            }
                            
                            echo "</td></tr>";
                        }
                    } else {
                        $emptyMsg = ($current_user_role === 'Manager') ? 'No pending leave requests' : 'No processing leave requests';
                        echo "<tr><td colspan='8' class='hrEmptyState'>{$emptyMsg}</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="hr_leave_requests.js"></script>
</body>
</html>