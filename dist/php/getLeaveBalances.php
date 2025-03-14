<?php
session_start(); // Start the session to access session variables

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection file
require_once __DIR__ . '/db.php';

// Check if the user is logged in and has an employee ID
if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$employeeId = $_SESSION['employee_id'];

// Prepare and execute the query to fetch leave balances
$query = "SELECT LeaveType, DaysAvailable FROM leavebalance WHERE EmployeeID = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    echo json_encode(['error' => 'Database prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $employeeId); // Bind the employee ID
$stmt->execute();
$result = $stmt->get_result();

$leaveBalances = [];
while ($row = $result->fetch_assoc()) {
    $leaveBalances[] = $row; // Add each row to the leaveBalances array
}

// Return the leave balances as a JSON response
echo json_encode($leaveBalances);

// Close the database connection
$stmt->close();
$conn->close();
?>
