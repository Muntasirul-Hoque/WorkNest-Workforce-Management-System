// Manager Dashboard JavaScript

document.addEventListener("DOMContentLoaded", function () {
    loadDashboardData();
    loadTasks();

    // Set interval to refresh data every 60 seconds
    setInterval(loadDashboardData, 60000);
});

function loadDashboardData() {
    fetch("manager_dashboard_data.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("mgrStats_subordinates").textContent = data.subordinates || 0;
                document.getElementById("mgrStats_pendingLeaves").textContent = data.pending_leaves || 0;
                document.getElementById("mgrStats_pendingPromotions").textContent = data.pending_promotions || 0;
                document.getElementById("mgrStats_activeTasks").textContent = data.active_tasks || 0;
            }
        })
        .catch(error => console.error("Error loading dashboard data:", error));
}

function loadTasks() {
    fetch("manager_dashboard_data.php?action=get_tasks")
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("mgrTasks_body");
            if (data.success && data.tasks && data.tasks.length > 0) {
                tbody.innerHTML = data.tasks.map(task => `
                    <tr>
                        <td><strong>${escapeHtml(task.Title)}</strong></td>
                        <td>${escapeHtml(task.EmployeeName)}</td>
                        <td>${formatDate(task.DueDate)}</td>
                        <td><span class="mgrStatus_badge mgrStatus_${task.T_Status.toLowerCase().replace(' ', '_')}">${task.T_Status}</span></td>
                        <td>${formatDate(task.AssignedDate)}</td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="mgrTasks_empty">No tasks assigned yet</td></tr>`;
            }
        })
        .catch(error => {
            console.error("Error loading tasks:", error);
            const tbody = document.getElementById("mgrTasks_body");
            tbody.innerHTML = `<tr><td colspan="5" class="mgrTasks_empty">Error loading tasks</td></tr>`;
        });
}

function formatDate(dateString) {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric"
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}