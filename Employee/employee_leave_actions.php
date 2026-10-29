<?php
// Leave actions are currently handled inline in employee_leave.php via POST
// This file is reserved for future AJAX-based leave actions.

require_once 'employee_config.php';
header('Content-Type: application/json');

echo json_encode(['success' => false, 'message' => 'No action handler implemented yet']);
exit();
?>