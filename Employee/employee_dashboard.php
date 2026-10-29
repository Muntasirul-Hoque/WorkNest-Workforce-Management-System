<?php
require_once 'employee_config.php';

$emp_first_name = $_SESSION['first_name'] ?? 'Employee';
$emp_last_name = $_SESSION['last_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
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
            <li class="empSidebar_navItem active">
                <a href="employee_dashboard.php" class="empSidebar_navLink"><span class="empSidebar_icon">📊</span> Dashboard</a>
            </li>
            <li class="empSidebar_navItem">
                <a href="employee_tasks.php" class="empSidebar_navLink"><span class="empSidebar_icon">📝</span> My Tasks</a>
            </li>
            <li class="empSidebar_navItem">
                <a href="employee_leave.php" class="empSidebar_navLink"><span class="empSidebar_icon">🏖️</span> Apply Leave</a>
            </li>
            <li class="empSidebar_navItem">
                <a href="employee_promotion.php" class="empSidebar_navLink"><span class="empSidebar_icon">⬆️</span> Apply for Promotion</a>
            </li>
            <li class="empSidebar_navItem">
                <a href="employee_profile.php" class="empSidebar_navLink"><span class="empSidebar_icon">👤</span> My Profile</a>
            </li>
            <li class="empSidebar_navItem">
                <a href="../logout.php" class="empSidebar_navLink logout"><span class="empSidebar_icon">🚪</span> Logout</a>
            </li>
        </ul>
    </nav>

    <main class="empMain">
        <header class="empHeader">
            <div class="empHeader_content">
                <h1 class="empHeader_title">Dashboard</h1>
                <p class="empHeader_subtitle">Welcome back, <span id="empName_display"><?php echo htmlspecialchars($emp_first_name . ' ' . $emp_last_name); ?></span></p>
            </div>
            <div class="empHeader_actions">
                <div class="empHeader_profile">
                    <span class="empHeader_profileName"><?php echo htmlspecialchars(strtoupper(substr($emp_first_name, 0, 1) . substr($emp_last_name, 0, 1))); ?></span>
                </div>
            </div>
        </header>

        <section class="empStats">
            <div class="empStats_card">
                <div class="empStats_cardIcon">📝</div>
                <div class="empStats_cardContent">
                    <h3 class="empStats_cardNumber" id="empStats_totalTasks">0</h3>
                    <p class="empStats_cardLabel">My Tasks</p>
                </div>
            </div>
            <div class="empStats_card">
                <div class="empStats_cardIcon">⏳</div>
                <div class="empStats_cardContent">
                    <h3 class="empStats_cardNumber" id="empStats_pendingTasks">0</h3>
                    <p class="empStats_cardLabel">Pending Tasks</p>
                </div>
            </div>
            <div class="empStats_card">
                <div class="empStats_cardIcon">🏖️</div>
                <div class="empStats_cardContent">
                    <h3 class="empStats_cardNumber" id="empStats_leaveBalance">0</h3>
                    <p class="empStats_cardLabel">Leave Requests</p>
                </div>
            </div>
            <div class="empStats_card">
                <div class="empStats_cardIcon">⬆️</div>
                <div class="empStats_cardContent">
                    <h3 class="empStats_cardNumber" id="empStats_promotionStatus">-</h3>
                    <p class="empStats_cardLabel">Promotion Status</p>
                </div>
            </div>
        </section>

        <section class="empTableContainer">
            <h2 class="empTableTitle">🔥 In Progress Tasks</h2>
            <div class="empTableScroll">
                <table class="empTaskTable">
                    <thead>
                        <tr>
                            <th>Task ID</th>
                            <th>Title</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody id="empInProgress_body">
                        <tr><td colspan="3" class="empEmptyState">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="empTableContainer">
            <h2 class="empTableTitle">📋 Recent Leave Requests</h2>
            <div class="empTableScroll">
                <table class="empTaskTable">
                    <thead>
                        <tr>
                            <th>Leave ID</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="empRecentLeave_body">
                        <tr><td colspan="5" class="empEmptyState">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="employee_dashboard.js"></script>
</body>
</html>