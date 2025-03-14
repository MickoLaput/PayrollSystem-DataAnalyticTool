<?php
require_once __DIR__ . '/db.php';

$date = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';
$status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

// Build a base query to join TimeRecord with Employee, Position, and Schedule
$query = "
    SELECT 
    e.EmployeeID,
    CONCAT(e.FirstName, ' ', IFNULL(e.MiddleName, ''), ' ', e.LastName) AS Name,
    tr.Date,
    tr.CheckInTime,
    tr.CheckOutTime,
    p.PositionName,
    s.TimeIn AS ScheduleTimeIn,
    s.TimeOut AS ScheduleTimeOut,
    CASE
        WHEN tr.CheckInTime IS NULL THEN 'Absent'
        WHEN tr.CheckInTime <= s.TimeIn THEN 'On Time'
        WHEN tr.CheckInTime > s.TimeIn THEN 'Late'
    END AS Status
FROM `Employee` e
  LEFT JOIN `User` u 
    ON e.EmployeeID = u.EmployeeID
  LEFT JOIN `Schedule` s 
    ON u.ScheduleID = s.ScheduleID
  LEFT JOIN `Position` p 
    ON e.PositionID = p.PositionID
  LEFT JOIN `TimeRecord` tr
    ON e.EmployeeID = tr.EmployeeID
WHERE 1=1
";

// Optional: Filter by date if provided
if ($date) {
    $query .= " AND tr.Date = '$date'";
}

// Optional: Filter by status if provided
// Compare the same CASE expression we used above
if ($status) {
    $query .= " AND (
        CASE
            WHEN tr.CheckInTime IS NULL THEN 'Absent'
            WHEN tr.CheckInTime <= s.TimeIn THEN 'On Time'
            WHEN tr.CheckInTime > s.TimeIn THEN 'Late'
        END
    ) = '$status'";
}

$result = $conn->query($query);
$attendanceRecords = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $attendanceRecords[] = [
            'Name'         => $row['Name'],
            'Date'         => $row['Date'],
            'CheckInTime'  => $row['CheckInTime'],
            'CheckOutTime' => $row['CheckOutTime'],
            'PositionName' => $row['PositionName'],
            'Status'       => $row['Status']
        ];
    }
}

// Return JSON
echo json_encode($attendanceRecords);
$conn->close();
?>
