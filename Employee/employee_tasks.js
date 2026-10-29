function handleTaskAction(taskId, action) {
    if (!confirm(`Are you sure you want to ${action} this task?`)) return;

    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('action', action);

    fetch('employee_tasks_actions.php', {
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
        alert('An error occurred. Check console for details.');
    });
}

function filterTasks() {
    const status = document.getElementById('empFilter_status').value;
    const search = document.getElementById('empSearch_input').value.toLowerCase();
    const rows = document.querySelectorAll('#empTask_body tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const statusCell = row.querySelector('.empStatus_badge');
        const statusText = statusCell ? statusCell.textContent.toLowerCase().replace(' ', '_') : '';
        const statusMatch = status === 'all' || statusText === status.toLowerCase();
        const searchMatch = text.includes(search);
        row.style.display = (statusMatch && searchMatch) ? '' : 'none';
    });
}