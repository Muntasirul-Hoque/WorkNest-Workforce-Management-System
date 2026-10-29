<?php
require_once 'employee_config.php';

$message = '';
$messageType = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emp_update'])) {
    $firstName = sanitizeInput($_POST['emp_firstName'] ?? '');
    $lastName = sanitizeInput($_POST['emp_lastName'] ?? '');
    $email = sanitizeInput($_POST['emp_email'] ?? '');
    $phone = sanitizeInput($_POST['emp_phone'] ?? '');
    $address = sanitizeInput($_POST['emp_address'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($address)) {
        $message = "All fields are required!";
        $messageType = "error";
    } else {
        $sql = "UPDATE User SET FirstName = ?, LastName = ?, Email = ?, Phone = ?, Address = ? WHERE UserName = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $firstName, $lastName, $email, $phone, $address, $emp_user_name);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $emp_first_name = $firstName;
            $emp_last_name = $lastName;
            $message = "Profile updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error updating profile: " . mysqli_error($conn);
            $messageType = "error";
        }
    }
}

// Get full user data
$sql = "SELECT u.*, e.EmployeeID, e.ManagerID, m_u.FirstName AS ManagerFirstName, m_u.LastName AS ManagerLastName 
        FROM User u 
        LEFT JOIN Employee e ON u.UserName = e.UserName 
        LEFT JOIN Manager m ON e.ManagerID = m.ManagerID 
        LEFT JOIN User m_u ON m.UserName = m_u.UserName 
        WHERE u.UserName = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $emp_user_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$userData = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - WorkNest</title>
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
            <li class="empSidebar_navItem"><a href="employee_promotion.php" class="empSidebar_navLink"><span class="empSidebar_icon">⬆️</span> Apply for Promotion</a></li>
            <li class="empSidebar_navItem active"><a href="employee_profile.php" class="empSidebar_navLink"><span class="empSidebar_icon">👤</span> My Profile</a></li>
            <li class="empSidebar_navItem"><a href="../logout.php" class="empSidebar_navLink logout"><span class="empSidebar_icon">🚪</span> Logout</a></li>
        </ul>
    </nav>

    <main class="empMain">
        <header class="empHeader">
            <div class="empHeader_content">
                <h1 class="empHeader_title">My Profile</h1>
                <p class="empHeader_subtitle">View and update your personal information</p>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="empForm_message <?php echo $messageType; ?>" style="max-width: 900px;"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Info Box -->
        <div class="empInfoBox" style="max-width: 900px;">
            <p><strong>Employee ID:</strong> EMP-<?php echo str_pad($userData['EmployeeID'], 4, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($userData['UserName']); ?></p>
            <p><strong>Current Role:</strong> <?php echo htmlspecialchars($userData['Role']); ?></p>
            <p><strong>Reports To:</strong> <?php echo $userData['ManagerFirstName'] ? htmlspecialchars($userData['ManagerFirstName'] . ' ' . $userData['ManagerLastName']) : 'N/A'; ?></p>
            <p><strong>Joined:</strong> <?php echo date('M d, Y', strtotime($userData['JoinDate'])); ?></p>
        </div>

        <div class="empForm">
            <h2 class="empForm_title">✏️ Edit Profile</h2>

            <form method="POST" action="">
                <input type="hidden" name="emp_update" value="1">
                <div class="empForm_grid">
                    <div class="empForm_group">
                        <label class="empForm_label">First Name <span class="required">*</span></label>
                        <input type="text" name="emp_firstName" class="empForm_input" value="<?php echo htmlspecialchars($userData['FirstName']); ?>" required>
                    </div>

                    <div class="empForm_group">
                        <label class="empForm_label">Last Name <span class="required">*</span></label>
                        <input type="text" name="emp_lastName" class="empForm_input" value="<?php echo htmlspecialchars($userData['LastName']); ?>" required>
                    </div>

                    <div class="empForm_group">
                        <label class="empForm_label">Email <span class="required">*</span></label>
                        <input type="email" name="emp_email" class="empForm_input" value="<?php echo htmlspecialchars($userData['Email']); ?>" required>
                    </div>

                    <div class="empForm_group">
                        <label class="empForm_label">Phone <span class="required">*</span></label>
                        <input type="tel" name="emp_phone" class="empForm_input" value="<?php echo htmlspecialchars($userData['Phone']); ?>" required>
                    </div>

                    <div class="empForm_group full">
                        <label class="empForm_label">Address <span class="required">*</span></label>
                        <input type="text" name="emp_address" class="empForm_input" value="<?php echo htmlspecialchars($userData['Address']); ?>" required>
                    </div>

                    <div class="empForm_group">
                        <label class="empForm_label">Department</label>
                        <input type="text" class="empForm_input" value="<?php echo htmlspecialchars($userData['Dept']); ?>" disabled>
                    </div>

                    <div class="empForm_group">
                        <label class="empForm_label">Status</label>
                        <input type="text" class="empForm_input" value="<?php echo htmlspecialchars($userData['Status']); ?>" disabled>
                    </div>

                    <button type="submit" class="empForm_submit">💾 Update Profile</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>