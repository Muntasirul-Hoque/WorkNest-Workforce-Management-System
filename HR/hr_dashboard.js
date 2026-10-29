document.addEventListener("DOMContentLoaded", function () {
  loadDashboardData();
  loadEmployeeOverview();
  setInterval(loadDashboardData, 60000);
});

function loadDashboardData() {
  fetch("hr_dashboard_data.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        document.getElementById("hrStats_totalEmployees").textContent = data.total_employees;
        document.getElementById("hrStats_pendingLeaves").textContent = data.pending_leaves;
        document.getElementById("hrStats_pendingPromotions").textContent = data.pending_promotions;
        document.getElementById("hrStats_activeEmployees").textContent = data.active_employees;
        document.getElementById("hrName_display").textContent = data.hr_name;
      }
    })
    .catch((error) => console.error("Error loading dashboard data:", error));
}

function loadEmployeeOverview() {
  fetch("hr_dashboard_data.php?action=get_employees")
    .then((response) => response.json())
    .then((data) => {
      const tbody = document.getElementById("hrRecent_employeeBody");
      if (!data.success) {
        tbody.innerHTML = `<tr><td colspan="4" class="hrEmptyState">Error: ${data.error || 'Unknown error'}</td></tr>`;
        return;
      }
      if (data.employees && data.employees.length > 0) {
        tbody.innerHTML = data.employees.map((emp) => `
            <tr>
                <td><strong>${emp.FirstName || 'N/A'}</strong></td>
                <td>${emp.Dept || 'N/A'}</td>
                <td>${emp.Role || 'N/A'}</td>
                <td><span class="hrStatus_badge hrStatus_${(emp.Status || 'unknown').toLowerCase()}">${emp.Status || 'Unknown'}</span></td>
            </tr>
        `).join("");
      } else {
        tbody.innerHTML = `<tr><td colspan="4" class="hrEmptyState">No employees found</td></tr>`;
      }
    })
    .catch((error) => {
      console.error("Error loading employee overview:", error);
      document.getElementById("hrRecent_employeeBody").innerHTML = `<tr><td colspan="4" class="hrEmptyState">Error loading data</td></tr>`;
    });
}