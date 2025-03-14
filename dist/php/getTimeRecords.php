<?php
session_start();
require_once __DIR__ . '/db.php';

// Check if the user is logged in and has an employee ID in the session
if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$employeeId = $_SESSION['employee_id'];

// Retrieve optional query parameters for month and year
$month = isset($_GET['month']) ? $_GET['month'] : null;
$year = isset($_GET['year']) ? $_GET['year'] : null;

// Base query to fetch time records for the employee
$query = "SELECT Date, CheckInTime, CheckOutTime FROM TimeRecord WHERE EmployeeID = ?";

// Prepare parameters and types
$params = [$employeeId];
$paramTypes = "i";

// Add filters for month and year if provided
if ($month) {
    $query .= " AND MONTH(Date) = ?";
    $params[] = $month;
    $paramTypes .= "i";
}
if ($year) {
    $query .= " AND YEAR(Date) = ?";
    $params[] = $year;
    $paramTypes .= "i";
}

$query .= " ORDER BY Date ASC";

// Prepare and execute the statement
$stmt = $conn->prepare($query);
if ($stmt === false) {
    echo json_encode(['error' => 'Database prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

// Return the time records as JSON
echo json_encode($records);

// Close connections
$stmt->close();
$conn->close();
?>
