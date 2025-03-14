<?php
require_once __DIR__ . '/db.php';

$searchTerm = isset($_GET['term']) ? $conn->real_escape_string($_GET['term']) : '';

$query = "
    SELECT e.EmployeeID, e.Email, e.FirstName, e.MiddleName, e.LastName, p.PositionName as Position
    FROM Employee e, Position p
    WHERE CONCAT(e.FirstName, ' ', IFNULL(e.MiddleName, ''), ' ', e.LastName) LIKE '%$searchTerm%'
    AND Active_ind = TRUE
    AND p.PositionID = e.PositionID
";
$result = $conn->query($query);

$employees = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
} else {
    error_log("No employees found or query failed: " . $conn->error); // Log any potential query error
}

echo json_encode($employees);
$conn->close();
?>
