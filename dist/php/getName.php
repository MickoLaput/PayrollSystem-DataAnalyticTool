<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['employee_id'])) {
    echo "Employee";
    exit;
}

$employeeId = $_SESSION['employee_id'];

$query = "SELECT FirstName, MiddleName, LastName FROM Employee WHERE EmployeeID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Concatenate names and trim extra spaces
    $fullName = trim($row['FirstName'] . ' ' . $row['MiddleName'] . ' ' . $row['LastName']);
    echo $fullName;
} else {
    echo "Employee";
}

$stmt->close();
$conn->close();
?>
