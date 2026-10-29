<?php
require_once 'manager_config.php';
$conn = getDBConnection();
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : 'all';
$searchTerm = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$sql = "SELECT l.*, u.FirstName, u.LastName, u.Dept 
        FROM `Leave` l 
        JOIN Employee e ON l.UserName = e.UserName 
        JOIN User u ON e.UserName = u.UserName 
        WHERE e.ManagerID = ?";
$params = [$mgr_manager_id];
$types = "i";

if ($statusFilter !== 'all') {
    $sql .= " AND l.Status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if (!empty($searchTerm)) {
    $sql .= " AND (u.FirstName LIKE ? OR u.LastName LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $types .= "ss";
}

$sql .= " ORDER BY l.SD ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$leaves = [];
while ($row = mysqli_fetch_assoc($result)) {
    $leaves[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests - WorkNest</title>
    <link rel="stylesheet" href="manager_dashboard.css">
    <style>
        .mgrPage_filter { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
        .mgrPage_filter select, .mgrPage_filter input { padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
        .mgrPage_filter input { flex: 1; min-width: 200px; }
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
            <li class="mgrSidebar_navItem active">
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
                <h1 class="mgrHeader_title">Leave Requests</h1>
                <p class="mgrHeader_subtitle">Review leave applications from your team</p>
            </div>
        </header>

        <div class="mgrPage_filter">
            <select id="mgrFilter_status" onchange="applyFilters()">
                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Processing" <?php echo $statusFilter === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="Accepted" <?php echo $statusFilter === 'Accepted' ? 'selected' : ''; ?>>Accepted</option>
                <option value="Rejected" <?php echo $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
            <input type="text" id="mgrSearch_input" placeholder="Search by employee name..." value="<?php echo htmlspecialchars($searchTerm); ?>" onkeyup="applyFilters()">
            <button onclick="applyFilters()" class="mgrAction_btn mgrAction_btn_processing">Search</button>
            <button onclick="resetFilters()" class="mgrAction_btn" style="background: #e2e8f0; color: #475569;">Reset</button>
        </div>

        <div class="mgrPage_tableContainer">
            <table class="mgrPage_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="mgrLeave_body">
                    <?php if (count($leaves) > 0): ?>
                        <?php foreach ($leaves as $leave): 
                            $statusClass = strtolower($leave['Status']);
                        ?>
                            <tr>
                                <td><?php echo $leave['LeaveID']; ?></td>
                                <td><?php echo htmlspecialchars($leave['FirstName'] . ' ' . $leave['LastName']); ?></td>
                                <td><?php echo htmlspecialchars($leave['Dept'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($leave['SD'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($leave['ED'])); ?></td>
                                <td><?php echo htmlspecialchars(substr($leave['Reason'], 0, 50)) . '...'; ?></td>
                                <td><span class="mgrStatus_badge mgrStatus_<?php echo $statusClass; ?>"><?php echo $leave['Status']; ?></span></td>
                                <td>
                                    <?php if ($leave['Status'] === 'Pending'): ?>
                                        <button class="mgrAction_btn mgrAction_btn_approve" onclick="handleLeaveAction(<?php echo $leave['LeaveID']; ?>, 'Processing')">Accept</button>
                                        <button class="mgrAction_btn mgrAction_btn_reject" onclick="handleLeaveAction(<?php echo $leave['LeaveID']; ?>, 'Rejected')">Reject</button>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 13px;">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="mgrPage_empty">No leave requests found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function handleLeaveAction(leaveId, action) {
            if (!confirm(`Are you sure you want to ${action} this leave request?`)) return;
            
            const formData = new FormData();
            formData.append('leave_id', leaveId);
            formData.append('action', action);

            fetch('manager_leave_actions.php', {
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

        function applyFilters() {
            const status = document.getElementById('mgrFilter_status').value;
            const search = document.getElementById('mgrSearch_input').value;
            let url = window.location.pathname + '?';
            if (status !== 'all') url += 'status=' + encodeURIComponent(status) + '&';
            if (search) url += 'search=' + encodeURIComponent(search);
            window.location.href = url;
        }

        function resetFilters() {
            window.location.href = window.location.pathname;
        }

        document.getElementById('mgrSearch_input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') applyFilters();
        });
    </script>
</body>
</html>