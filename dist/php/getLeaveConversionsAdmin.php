<?php
// getLeaveConversionsAdmin.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

// Only PENDING conversions (you can remove the WHERE if you want all)
$sql = "
  SELECT 
    lc.ConversionID,
    lc.EmployeeID,
    CONCAT(e.FirstName, ' ', e.LastName) AS Name,
    lc.RequestDate,
    lc.LeaveType,
    lc.DaysRequested,
    lc.Reason,
    lc.Status
  FROM leaveconversion AS lc
  JOIN employee AS e ON lc.EmployeeID = e.EmployeeID
  WHERE lc.Status = 'PENDING'
  ORDER BY lc.RequestDate DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$out = [];
while ($row = $result->fetch_assoc()) {
    $out[] = $row;
}

echo json_encode($out);

$stmt->close();
$conn->close();
