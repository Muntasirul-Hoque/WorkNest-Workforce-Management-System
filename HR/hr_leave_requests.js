function handleLeaveAction(leaveId, action) {
    if (!confirm(`Are you sure you want to ${action} this leave request?`)) return;
    
    const formData = new FormData();
    formData.append('leave_id', leaveId);
    formData.append('action', action);

    fetch('hr_leave_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
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

function filterLeaves() {
    const status = document.getElementById('hrFilter_status').value;
    const search = document.getElementById('hrSearch_input').value.toLowerCase();
    const rows = document.querySelectorAll('#hrLeave_body tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const statusCell = row.querySelector('.hrStatus_badge');
        const statusText = statusCell ? statusCell.textContent.toLowerCase() : '';
        const statusMatch = status === 'all' || statusText === status.toLowerCase();
        const searchMatch = text.includes(search);
        row.style.display = (statusMatch && searchMatch) ? '' : 'none';
    });
}