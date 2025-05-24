<?php
// getLeaveConversions.php
session_start();
require_once __DIR__ . '/db.php';

// Make sure we know who’s querying
if (empty($_SESSION['employee_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$employeeID = (int)$_SESSION['employee_id'];

$sql = "
    SELECT 
        RequestDate,
        LeaveType,
        DaysRequested,
        Reason,
        Status
    FROM leaveconversion
    WHERE EmployeeID = ?
    ORDER BY RequestDate DESC
";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $employeeID);
    $stmt->execute();
    $result = $stmt->get_result();

    $conversions = [];
    while ($row = $result->fetch_assoc()) {
        $conversions[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($conversions);

    $stmt->close();
} else {
    // Prepared statement failed
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
}

$conn->close();
