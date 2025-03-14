<?php
// Include your database connection file
require_once __DIR__ . '/db.php';

// Get the employee ID (passed from frontend via query string)
$employeeId = $_GET['employeeId'];

// Check if the employeeId is set and is a valid number
if (!isset($employeeId) || !is_numeric($employeeId)) {
    echo json_encode(['error' => 'Invalid employee ID']);
    exit;
}

// Query to fetch the employee's first name, middle name, last name, and salary rate
$query = "
    SELECT e.FirstName, e.MiddleName, e.LastName, p.SalaryPosition AS rate, e.MaxicareType
    FROM Employee e
    JOIN Position p ON e.PositionID = p.PositionID
    WHERE e.EmployeeID = ?
";
$stmt = $conn->prepare($query);

// Bind the parameter
$stmt->bind_param("i", $employeeId);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Check if any data was fetched
if ($result->num_rows > 0) {
    // Fetch the employee's data
    $employee = $result->fetch_assoc();

    // Return the employee's name and salary rate as JSON
    echo json_encode($employee);
} else {
    // If no data found, return an error
    echo json_encode(['error' => 'Employee not found']);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
