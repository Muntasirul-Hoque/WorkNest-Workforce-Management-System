<?php
require_once 'manager_config.php';

// Use session variables (already defined in manager_config.php)
$mgr_first_name = $_SESSION['first_name'] ?? 'Manager';
$mgr_last_name = $_SESSION['last_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="manager_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sidebar Navigation -->
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

    <!-- Main Content -->
    <main class="mgrMain">
        <!-- Header -->
        <header class="mgrHeader">
            <div class="mgrHeader_content">
                <h1 class="mgrHeader_title">Dashboard</h1>
                <p class="mgrHeader_subtitle">Welcome back, <span id="mgrName_display"><?php echo htmlspecialchars($mgr_first_name . ' ' . $mgr_last_name); ?></span></p>
            </div>
            <div class="mgrHeader_actions">
                <div class="mgrHeader_profile">
                    <span class="mgrHeader_profileName"><?php echo htmlspecialchars(strtoupper(substr($mgr_first_name, 0, 1) . substr($mgr_last_name, 0, 1))); ?></span>
                </div>
            </div>
        </header>

        <!-- Stats Cards -->
        <section class="mgrStats">
            <div class="mgrStats_card">
                <div class="mgrStats_cardIcon">👥</div>
                <div class="mgrStats_cardContent">
                    <h3 class="mgrStats_cardNumber" id="mgrStats_subordinates">0</h3>
                    <p class="mgrStats_cardLabel">Subordinate Employees</p>
                </div>
            </div>
            <div class="mgrStats_card">
                <div class="mgrStats_cardIcon">📋</div>
                <div class="mgrStats_cardContent">
                    <h3 class="mgrStats_cardNumber" id="mgrStats_pendingLeaves">0</h3>
                    <p class="mgrStats_cardLabel">Pending Leaves</p>
                </div>
            </div>
            <div class="mgrStats_card">
                <div class="mgrStats_cardIcon">⬆️</div>
                <div class="mgrStats_cardContent">
                    <h3 class="mgrStats_cardNumber" id="mgrStats_pendingPromotions">0</h3>
                    <p class="mgrStats_cardLabel">Pending Promotions</p>
                </div>
            </div>
            <div class="mgrStats_card">
                <div class="mgrStats_cardIcon">📝</div>
                <div class="mgrStats_cardContent">
                    <h3 class="mgrStats_cardNumber" id="mgrStats_activeTasks">0</h3>
                    <p class="mgrStats_cardLabel">Active Tasks</p>
                </div>
            </div>
        </section>

        <!-- Task Overview Table -->
        <section class="mgrTasks">
            <div class="mgrTasks_container">
                <h2 class="mgrTasks_title">📋 Assigned Tasks Overview</h2>
                <div class="mgrTasks_tableContainer">
                    <table class="mgrTasks_table" id="mgrTasks_table">
                        <thead>
                            <tr>
                                <th>Task Title</th>
                                <th>Assigned To</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Assigned Date</th>
                            </tr>
                        </thead>
                        <tbody id="mgrTasks_body">
                            <tr>
                                <td colspan="5" class="mgrTasks_empty">Loading tasks...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script src="manager_dashboard.js"></script>
</body>
</html>