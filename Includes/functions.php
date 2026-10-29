<?php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function getEmployeeById($conn, $employeeID) {
    $sql = "SELECT u.*, e.EmployeeID, e.ManagerID 
            FROM User u 
            JOIN Employee e ON u.UserName = e.UserName 
            WHERE e.EmployeeID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $employeeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function getAllEmployees($conn) {
    $sql = "SELECT u.*, e.EmployeeID, e.ManagerID, m.UserName as ManagerUserName 
            FROM User u 
            JOIN Employee e ON u.UserName = e.UserName 
            LEFT JOIN Manager m ON e.ManagerID = m.ManagerID 
            WHERE u.Role NOT IN ('HR', 'Manager') 
            ORDER BY u.JoinDate DESC";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getAllManagers($conn) {
    $sql = "SELECT u.*, m.ManagerID 
            FROM User u 
            JOIN Manager m ON u.UserName = m.UserName 
            WHERE u.Status = 'Active'";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getPendingLeaveRequests($conn) {
    $sql = "SELECT l.*, u.FirstName, u.LastName, u.Role, u.Dept 
            FROM `Leave` l 
            JOIN User u ON l.UserName = u.UserName 
            WHERE l.Status IN ('Pending', 'Processing') 
            ORDER BY l.SD ASC";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getPendingPromotions($conn) {
    $sql = "SELECT p.*, u.FirstName, u.LastName, u.Role, u.Dept, u.Salary
            FROM Promotion p 
            JOIN Employee e ON p.EmployeeID = e.EmployeeID 
            JOIN User u ON e.UserName = u.UserName 
            WHERE p.Status IN ('Pending', 'Processing') 
            ORDER BY p.PromotionDate ASC";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getAllEmployeesForDropdown($conn) {
    $sql = "SELECT e.EmployeeID, u.FirstName, u.LastName, u.Role 
            FROM Employee e 
            JOIN User u ON e.UserName = u.UserName 
            WHERE u.Role NOT IN ('HR', 'Manager')
            ORDER BY u.FirstName";
    $result = mysqli_query($conn, $sql);
    $employees = [];
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $employees[] = $row;
        }
    }
    return $employees;
}

function getAllActiveEmployees($conn) {
    $sql = "SELECT e.EmployeeID, u.FirstName, u.LastName, u.Email, u.Role, u.Dept, u.Status
            FROM Employee e 
            JOIN User u ON e.UserName = u.UserName 
            WHERE u.Status = 'Active' AND u.Role NOT IN ('HR', 'Manager')
            ORDER BY u.FirstName";
    $result = mysqli_query($conn, $sql);
    $employees = [];
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $employees[] = $row;
        }
    }
    return $employees;
}
?>