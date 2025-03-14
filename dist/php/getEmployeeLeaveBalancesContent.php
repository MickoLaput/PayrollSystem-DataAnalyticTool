<?php
session_start();
require_once __DIR__ . '/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the Employee ID is provided
if (!isset($_GET['employeeId'])) {
    echo json_encode(['error' => 'Employee ID is missing']);
    exit();
}

$employeeId = intval($_GET['employeeId']);

// Fetch leave balances along with employee details
$query = "
    SELECT 
        CONCAT(e.FirstName, ' ', e.LastName) AS Name,
        p.PositionName AS Position,
        e.Email,
        lb.LeaveType,
        lb.DaysAvailable
    FROM 
        LeaveBalance lb
    JOIN 
        Employee e ON lb.EmployeeID = e.EmployeeID
    JOIN 
        Position p ON p.PositionID = e.PositionID
    WHERE 
        e.EmployeeID = ?;
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $leaveBalances = [];
    while ($row = $result->fetch_assoc()) {
        $leaveBalances[] = $row;
    }
    echo json_encode($leaveBalances);
} else {
    echo json_encode(['error' => 'No leave balances found']);
}

$conn->close();
?>
