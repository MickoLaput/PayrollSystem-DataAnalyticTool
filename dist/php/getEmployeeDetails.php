<?php
session_start();
require_once __DIR__ . '/db.php';

// Check if the user is logged in and has an employee ID
if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$employeeId = $_SESSION['employee_id'];

$query = "
    SELECT e.FirstName, e.LastName, p.SalaryPosition AS Salary, p.Indicator, e.MaxicareType, e.Age
    FROM Employee e
    JOIN Position p ON e.PositionID = p.PositionID
    WHERE e.EmployeeID = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
    echo json_encode($employee);
} else {
    echo json_encode(['error' => 'Employee not found']);
}

$stmt->close();
$conn->close();
?>
