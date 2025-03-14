<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/db.php';

$query = "
    SELECT lf.LeaveFileID,
           CONCAT(e.FirstName, ' ', e.LastName) AS Name,
           lf.LeaveType,
           lf.LeaveStartDate,
           lf.LeaveEndDate,
           lf.indicator,
           lf.Reason,
           lf.Attachment
    FROM LeaveFile lf
    JOIN Employee e ON lf.EmployeeID = e.EmployeeID
    WHERE lf.indicator = 'PENDING'
";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $leaveRequests = [];

    while ($row = $result->fetch_assoc()) {
        // If Attachment is not empty, just use a placeholder
        if (!empty($row['Attachment'])) {
            $row['Attachment'] = 'Has file attached';
        }
        $leaveRequests[] = $row;
    }

    echo json_encode($leaveRequests);
} else {
    echo json_encode(['error' => 'No leave requests found']);
}

$conn->close();
