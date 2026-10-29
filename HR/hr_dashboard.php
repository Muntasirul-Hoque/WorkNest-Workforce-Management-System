<?php
require_once 'hr_config.php';

$hr_user_name = $_SESSION['user_name'] ?? '';
$hr_first_name = $_SESSION['first_name'] ?? 'HR';
$hr_last_name = $_SESSION['last_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard - WorkNest</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hr_dashboard.css">
</head>
<body>
    <nav class="hrSidebar">
        <div class="hrSidebar_header">
            <h1 class="hrSidebar_logo">WorkNest</h1>
            <span class="hrSidebar_role">HR Panel</span>
        </div>
        <ul class="hrSidebar_nav">
            <li class="hrSidebar_navItem active"><a href="hr_dashboard.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">📊</span>Dashboard</a></li>
            <li class="hrSidebar_navItem"><a href="hr_update_employee.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">✏️</span>Update Employee</a></li>
            <li class="hrSidebar_navItem"><a href="hr_recruit.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">➕</span>Recruit Employee</a></li>
            <li class="hrSidebar_navItem"><a href="hr_promote.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">⬆️</span>Promote Employee</a></li>
            <li class="hrSidebar_navItem"><a href="hr_terminate.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">🚫</span>Terminate Employee</a></li>
            <li class="hrSidebar_navItem"><a href="hr_leave_requests.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">📋</span>Leave Requests</a></li>
            <li class="hrSidebar_navItem"><a href="hr_profile.php" class="hrSidebar_navLink"><span class="hrSidebar_icon">👤</span>My Profile</a></li>
            <li class="hrSidebar_navItem"><a href="../logout.php" class="hrSidebar_navLink logout"><span class="hrSidebar_icon">🚪</span>Logout</a></li>
        </ul>
    </nav>

    <main class="hrMain">
        <header class="hrHeader">
            <div class="hrHeader_content">
                <h1 class="hrHeader_title">Dashboard</h1>
                <p class="hrHeader_subtitle">Welcome back, <span id="hrName_display"><?php echo htmlspecialchars($hr_first_name . ' ' . $hr_last_name); ?></span></p>
            </div>
            <div class="hrHeader_actions">
                <div class="hrHeader_profile">
                    <span class="hrHeader_profileName" id="hrName_short"><?php echo htmlspecialchars(strtoupper(substr($hr_first_name, 0, 1) . substr($hr_last_name, 0, 1))); ?></span>
                </div>
            </div>
        </header>

        <section class="hrStats">
            <div class="hrStats_card">
                <div class="hrStats_cardIcon">👥</div>
                <div class="hrStats_cardContent">
                    <h3 class="hrStats_cardNumber" id="hrStats_totalEmployees">0</h3>
                    <p class="hrStats_cardLabel">Total Employees</p>
                </div>
            </div>
            <div class="hrStats_card">
                <div class="hrStats_cardIcon">📋</div>
                <div class="hrStats_cardContent">
                    <h3 class="hrStats_cardNumber" id="hrStats_pendingLeaves">0</h3>
                    <p class="hrStats_cardLabel">Pending Leaves</p>
                </div>
            </div>
            <div class="hrStats_card">
                <div class="hrStats_cardIcon">⬆️</div>
                <div class="hrStats_cardContent">
                    <h3 class="hrStats_cardNumber" id="hrStats_pendingPromotions">0</h3>
                    <p class="hrStats_cardLabel">Pending Promotions</p>
                </div>
            </div>
            <div class="hrStats_card">
                <div class="hrStats_cardIcon">🟢</div>
                <div class="hrStats_cardContent">
                    <h3 class="hrStats_cardNumber" id="hrStats_activeEmployees">0</h3>
                    <p class="hrStats_cardLabel">Active Employees</p>
                </div>
            </div>
        </section>

        <section class="hrTableContainer">
            <h2 class="hrTableTitle">📋 Employee Overview</h2>
            <div class="hrTableScroll">
                <table class="hrEmployeeTable" id="hrRecent_employeeTable">
                    <thead>
                        <tr><th>First Name</th><th>Department</th><th>Role</th><th>Status</th></tr>
                    </thead>
                    <tbody id="hrRecent_employeeBody">
                        <tr><td colspan="4" class="hrEmptyState">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="hr_dashboard.js"></script>
</body>
</html>