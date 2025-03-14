<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db.php';

// Query to get only active employee data
$sql = "SELECT e.EmployeeID, e.Email, e.FirstName, e.MiddleName, e.LastName, p.PositionName as Position FROM Employee e, Position p
        WHERE  p.PositionID = e.PositionID
        AND e.Active_ind = TRUE
       ";
$result = $conn->query($sql);

$employees = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}
echo json_encode($employees);

$conn->close();
?>
