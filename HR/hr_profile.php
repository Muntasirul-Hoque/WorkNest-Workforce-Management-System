<?php
require_once 'hr_config.php';

$message = '';
$messageType = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hr_update'])) {
    $firstName = sanitizeInput($_POST['hr_firstName'] ?? '');
    $lastName = sanitizeInput($_POST['hr_lastName'] ?? '');
    $email = sanitizeInput($_POST['hr_email'] ?? '');
    $phone = sanitizeInput($_POST['hr_phone'] ?? '');
    $address = sanitizeInput($_POST['hr_address'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($address)) {
        $message = "All fields are required!";
        $messageType = "error";
    } else {
        $sql = "UPDATE User SET FirstName = ?, LastName = ?, Email = ?, Phone = ?, Address = ? WHERE UserName = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $firstName, $lastName, $email, $phone, $address, $hr_user_name);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $hr_first_name = $firstName;
            $hr_last_name = $lastName;
            $message = "Profile updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating profile: " . mysqli_error($conn);
            $messageType = "error";
        }
    }
}

// Get full user data
$sql = "SELECT u.*, h.HRID 
        FROM User u 
        LEFT JOIN HR h ON u.UserName = h.UserName 
        WHERE u.UserName = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $hr_user_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($result);

// Get stats for info box
$totalUsers = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM User WHERE Role != 'HR'");
if ($row = mysqli_fetch_assoc($result)) $totalUsers = (int)$row['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - WorkNest</title>
    <link rel="stylesheet" href="hr_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Profile-specific styles (matching employee look) */
        .hrProfile_form { background: #fff; border-radius: 16px; padding: 24px 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); max-width: 800px; margin-bottom: 32px; }
        .hrProfile_formTitle { font-size: 18px; font-weight: 600; margin-bottom: 20px; }
        .hrProfile_grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .hrProfile_group { display: flex; flex-direction: column; }
        .hrProfile_group.full { grid-column: 1 / -1; }
        .hrProfile_label { font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 6px; }
        .hrProfile_label .required { color: #dc2626; }
        .hrProfile_input { padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-family: 'Inter', sans-serif; transition: border-color 0.2s ease; }
        .hrProfile_input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .hrProfile_input:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
        .hrProfile_submit { padding: 12px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; grid-column: 1 / -1; transition: transform 0.2s ease; }
        .hrProfile_submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3); }
        .hrProfile_message { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; max-width: 900px; }
        .hrProfile_message.success { background: #d1fae5; color: #059669; }
        .hrProfile_message.error { background: #fecaca; color: #dc2626; }
        .hrProfile_infoBox { background: #f8fafc; border-radius: 8px; padding: 16px; margin-bottom: 20px; border-left: 4px solid #667eea; max-width: 900px; }
        .hrProfile_infoBox p { margin: 4px 0; font-size: 14px; color: #475569; }
        .hrProfile_infoBox strong { color: #1a1a2e; }
        @media (max-width: 768px) {
            .hrProfile_grid { grid-template-columns: 1fr; }
            .hrProfile_group.full { grid-column: 1; }
            .hrProfile_submit { grid-column: 1; }
        }
    </style>
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
            <li class="hrSidebar_navItem"><a href="hr_leave_requests.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">📋</span>Leave Requests</a></li>
            <li class="hrSidebar_navItem active"><a href="hr_profile.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">👤</span>My Profile</a></li>
            <li class="hrSidebar_navItem"><a href="../logout.php" class="hrSidebar_navLink logout"><span class="hrSidebar_icon">🚪</span>Logout</a></li>
        </ul>
    </nav>

    <main class="hrMain">
        <header class="hrHeader">
            <div class="hrHeader_content">
                <h1 class="hrHeader_title">My Profile</h1>
                <p class="hrHeader_subtitle">View and update your personal information</p>
            </div>
            <div class="hrHeader_actions">
                <div class="hrHeader_profile">
                    <span><?php echo htmlspecialchars(strtoupper(substr($hr_first_name, 0, 1) . substr($hr_last_name, 0, 1))); ?></span>
                </div>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="hrProfile_message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Info Box -->
        <div class="hrProfile_infoBox">
            <p><strong>HR ID:</strong> HR-<?php echo str_pad($userData['HRID'], 4, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($userData['UserName']); ?></p>
            <p><strong>Current Role:</strong> <?php echo htmlspecialchars($userData['Role']); ?></p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($userData['Dept']); ?></p>
            <p><strong>Managing:</strong> <?php echo $totalUsers; ?> user(s) in system</p>
            <p><strong>Joined:</strong> <?php echo date('M d, Y', strtotime($userData['JoinDate'])); ?></p>
        </div>

        <div class="hrProfile_form">
            <h2 class="hrProfile_formTitle">✏️ Edit Profile</h2>

            <form method="POST" action="">
                <input type="hidden" name="hr_update" value="1">
                <div class="hrProfile_grid">
                    <div class="hrProfile_group">
                        <label class="hrProfile_label">First Name <span class="required">*</span></label>
                        <input type="text" name="hr_firstName" class="hrProfile_input" value="<?php echo htmlspecialchars($userData['FirstName']); ?>" required>
                    </div>

                    <div class="hrProfile_group">
                        <label class="hrProfile_label">Last Name <span class="required">*</span></label>
                        <input type="text" name="hr_lastName" class="hrProfile_input" value="<?php echo htmlspecialchars($userData['LastName']); ?>" required>
                    </div>

                    <div class="hrProfile_group">
                        <label class="hrProfile_label">Email <span class="required">*</span></label>
                        <input type="email" name="hr_email" class="hrProfile_input" value="<?php echo htmlspecialchars($userData['Email']); ?>" required>
                    </div>

                    <div class="hrProfile_group">
                        <label class="hrProfile_label">Phone <span class="required">*</span></label>
                        <input type="tel" name="hr_phone" class="hrProfile_input" value="<?php echo htmlspecialchars($userData['Phone']); ?>" required>
                    </div>

                    <div class="hrProfile_group full">
                        <label class="hrProfile_label">Address <span class="required">*</span></label>
                        <input type="text" name="hr_address" class="hrProfile_input" value="<?php echo htmlspecialchars($userData['Address']); ?>" required>
                    </div>

                    <div class="hrProfile_group">
                        <label class="hrProfile_label">Department</label>
                        <input type="text" class="hrProfile_input" value="<?php echo htmlspecialchars($userData['Dept']); ?>" disabled>
                    </div>

                    <div class="hrProfile_group">
                        <label class="hrProfile_label">Status</label>
                        <input type="text" class="hrProfile_input" value="<?php echo htmlspecialchars($userData['Status']); ?>" disabled>
                    </div>

                    <button type="submit" class="hrProfile_submit">💾 Update Profile</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>