<?php
session_start();
header('Content-Type: application/json');

// Include database connection file
require_once __DIR__ . '/db.php';

// Check if employee is logged in
if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$employeeId = $_SESSION['employee_id'];

// Prepare SQL query to fetch employee information
$query = "
    SELECT 
        e.EmployeeID, 
        e.FirstName, 
        e.MiddleName, 
        e.LastName, 
        e.Age, 
        e.Email, 
        e.Phone, 
        e.StreetNBuildingHouseNo, 
        e.Barangay, 
        e.City, 
        e.Postal_zip_code, 
        e.MaxicareType, 
        p.PositionName 
    FROM Employee e
    LEFT JOIN Position p ON e.PositionID = p.PositionID
    WHERE e.EmployeeID = ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => $conn->error]);
    exit;
}

$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Employee not found']);
    exit;
}

$employeeData = $result->fetch_assoc();

echo json_encode($employeeData);

$stmt->close();
$conn->close();
?>
