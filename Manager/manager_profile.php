<?php
require_once 'manager_config.php';

$message = '';
$messageType = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mgr_update'])) {
    $firstName = sanitizeInput($_POST['mgr_firstName'] ?? '');
    $lastName = sanitizeInput($_POST['mgr_lastName'] ?? '');
    $email = sanitizeInput($_POST['mgr_email'] ?? '');
    $phone = sanitizeInput($_POST['mgr_phone'] ?? '');
    $address = sanitizeInput($_POST['mgr_address'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($address)) {
        $message = "All fields are required!";
        $messageType = "error";
    } else {
        $sql = "UPDATE User SET FirstName = ?, LastName = ?, Email = ?, Phone = ?, Address = ? WHERE UserName = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $firstName, $lastName, $email, $phone, $address, $mgr_user_name);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $mgr_first_name = $firstName;
            $mgr_last_name = $lastName;
            $message = "Profile updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating profile: " . mysqli_error($conn);
            $messageType = "error";
        }
    }
}

// Get full user data
$sql = "SELECT u.*, m.ManagerID, e.EmployeeID 
        FROM User u 
        LEFT JOIN Manager m ON u.UserName = m.UserName 
        LEFT JOIN Employee e ON u.UserName = e.UserName 
        WHERE u.UserName = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $mgr_user_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($result);

// Get team size
$teamSize = 0;
$sql = "SELECT COUNT(*) as count FROM Employee WHERE ManagerID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $mgr_manager_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result)) $teamSize = (int)$row['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - WorkNest</title>
    <link rel="stylesheet" href="manager_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Profile-specific styles (matching employee look) */
        .mgrProfile_form { background: #fff; border-radius: 16px; padding: 24px 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); max-width: 800px; margin-bottom: 32px; }
        .mgrProfile_formTitle { font-size: 18px; font-weight: 600; margin-bottom: 20px; }
        .mgrProfile_grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .mgrProfile_group { display: flex; flex-direction: column; }
        .mgrProfile_group.full { grid-column: 1 / -1; }
        .mgrProfile_label { font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 6px; }
        .mgrProfile_label .required { color: #dc2626; }
        .mgrProfile_input { padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: 'Inter', sans-serif; transition: border-color 0.2s ease; }
        .mgrProfile_input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .mgrProfile_input:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
        .mgrProfile_submit { padding: 12px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; grid-column: 1 / -1; transition: transform 0.2s ease; }
        .mgrProfile_submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3); }
        .mgrProfile_message { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .mgrProfile_message.success { background: #d1fae5; color: #059669; }
        .mgrProfile_message.error { background: #fecaca; color: #dc2626; }
        .mgrProfile_infoBox { background: #f8fafc; border-radius: 8px; padding: 16px; margin-bottom: 20px; border-left: 4px solid #667eea; max-width: 900px; }
        .mgrProfile_infoBox p { margin: 4px 0; font-size: 14px; color: #475569; }
        .mgrProfile_infoBox strong { color: #1a1a2e; }
        @media (max-width: 768px) {
            .mgrProfile_grid { grid-template-columns: 1fr; }
            .mgrProfile_group.full { grid-column: 1; }
            .mgrProfile_submit { grid-column: 1; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
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
            <li class="mgrSidebar_navItem">
                <a href="manager_apply_leave.php" class="mgrSidebar_navLink">
                    <span class="mgrSidebar_icon">🏖️</span> Apply Leave
                </a>
            </li>
            <li class="mgrSidebar_navItem active">
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

    <!-- Main Content -->
    <main class="mgrMain">
        <header class="mgrHeader">
            <div class="mgrHeader_content">
                <h1 class="mgrHeader_title">My Profile</h1>
                <p class="mgrHeader_subtitle">View and update your personal information</p>
            </div>
            <div class="mgrHeader_actions">
                <div class="mgrHeader_profile">
                    <span><?php echo htmlspecialchars(strtoupper(substr($mgr_first_name, 0, 1) . substr($mgr_last_name, 0, 1))); ?></span>
                </div>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="mgrProfile_message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Info Box -->
        <div class="mgrProfile_infoBox">
            <p><strong>Manager ID:</strong> MGR-<?php echo str_pad($userData['ManagerID'], 4, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($userData['UserName']); ?></p>
            <p><strong>Current Role:</strong> <?php echo htmlspecialchars($userData['Role']); ?></p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($userData['Dept']); ?></p>
            <p><strong>Team Size:</strong> <?php echo $teamSize; ?> employee(s)</p>
            <p><strong>Joined:</strong> <?php echo date('M d, Y', strtotime($userData['JoinDate'])); ?></p>
        </div>

        <div class="mgrProfile_form">
            <h2 class="mgrProfile_formTitle">✏️ Edit Profile</h2>

            <form method="POST" action="">
                <input type="hidden" name="mgr_update" value="1">
                <div class="mgrProfile_grid">
                    <div class="mgrProfile_group">
                        <label class="mgrProfile_label">First Name <span class="required">*</span></label>
                        <input type="text" name="mgr_firstName" class="mgrProfile_input" value="<?php echo htmlspecialchars($userData['FirstName']); ?>" required>
                    </div>

                    <div class="mgrProfile_group">
                        <label class="mgrProfile_label">Last Name <span class="required">*</span></label>
                        <input type="text" name="mgr_lastName" class="mgrProfile_input" value="<?php echo htmlspecialchars($userData['LastName']); ?>" required>
                    </div>

                    <div class="mgrProfile_group">
                        <label class="mgrProfile_label">Email <span class="required">*</span></label>
                        <input type="email" name="mgr_email" class="mgrProfile_input" value="<?php echo htmlspecialchars($userData['Email']); ?>" required>
                    </div>

                    <div class="mgrProfile_group">
                        <label class="mgrProfile_label">Phone <span class="required">*</span></label>
                        <input type="tel" name="mgr_phone" class="mgrProfile_input" value="<?php echo htmlspecialchars($userData['Phone']); ?>" required>
                    </div>

                    <div class="mgrProfile_group full">
                        <label class="mgrProfile_label">Address <span class="required">*</span></label>
                        <input type="text" name="mgr_address" class="mgrProfile_input" value="<?php echo htmlspecialchars($userData['Address']); ?>" required>
                    </div>

                    <div class="mgrProfile_group">
                        <label class="mgrProfile_label">Department</label>
                        <input type="text" class="mgrProfile_input" value="<?php echo htmlspecialchars($userData['Dept']); ?>" disabled>
                    </div>

                    <div class="mgrProfile_group">
                        <label class="mgrProfile_label">Status</label>
                        <input type="text" class="mgrProfile_input" value="<?php echo htmlspecialchars($userData['Status']); ?>" disabled>
                    </div>

                    <button type="submit" class="mgrProfile_submit">💾 Update Profile</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>