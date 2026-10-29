document.addEventListener("DOMContentLoaded", function () {
    loadDashboardData();
    loadInProgressTasks();
    loadRecentLeaves();
    setInterval(loadDashboardData, 60000);
});

function loadDashboardData() {
    fetch("employee_dashboard_data.php?action=get_stats")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("empStats_totalTasks").textContent = data.stats.totalTasks || 0;
                document.getElementById("empStats_pendingTasks").textContent = data.stats.pendingTasks || 0;
                document.getElementById("empStats_leaveBalance").textContent = data.stats.leaveBalance || 0;
                document.getElementById("empStats_promotionStatus").textContent = data.stats.promotionStatus || '-';
            }
        })
        .catch(error => console.error("Error loading stats:", error));
}

function loadInProgressTasks() {
    fetch("employee_dashboard_data.php?action=get_in_progress")
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("empInProgress_body");
            if (data.success && data.tasks.length > 0) {
                tbody.innerHTML = data.tasks.map(task => `
                    <tr>
                        <td>${task.taskId}</td>
                        <td><strong>${escapeHtml(task.title)}</strong></td>
                        <td>${formatDate(task.dueDate)}</td>
                    </tr>
                `).join("");
            } else {
                tbody.innerHTML = `<tr><td colspan="3" class="empEmptyState">No in-progress tasks</td></tr>`;
            }
        })
        .catch(error => console.error("Error loading in-progress tasks:", error));
}

function loadRecentLeaves() {
    fetch("employee_dashboard_data.php?action=get_recent_leaves")
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("empRecentLeave_body");
            if (data.success && data.leaves.length > 0) {
                tbody.innerHTML = data.leaves.map(leave => `
                    <tr>
                        <td>${leave.LeaveID}</td>
                        <td>${formatDate(leave.startDate)}</td>
                        <td>${formatDate(leave.endDate)}</td>
                        <td>${escapeHtml(leave.reason.substring(0, 50))}...</td>
                        <td><span class="empStatus_badge empStatus_${leave.status.toLowerCase()}">${leave.status}</span></td>
                    </tr>
                `).join("");
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="empEmptyState">No recent leave requests</td></tr>`;
            }
        })
        .catch(error => console.error("Error loading recent leaves:", error));
}

function formatDate(dateString) {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}