<?php
session_start();
require_once __DIR__ . '/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Fetch leave balances along with employee details
$query = "
    SELECT distinct
    e.EmployeeID,
    CONCAT(e.FirstName, ' ', e.LastName) AS Name,
    p.PositionName AS Position
FROM 
    LeaveBalance lb
JOIN 
    Employee e ON lb.EmployeeID = e.EmployeeID
JOIN 
    Position p ON p.PositionID = e.PositionID;
";

// Execute the query and check for errors
$result = $conn->query($query);
if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit();
}

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
