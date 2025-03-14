<?php
require_once __DIR__ . '/db.php';

$currentDate = date('Y-m-d');

// 1) Get the total number of active employees
$sqlTotalEmployees = "
    SELECT COUNT(*) AS total
    FROM Employee
    WHERE Active_ind = 1
";
$resultTotal = $conn->query($sqlTotalEmployees);
$totalEmployees = 0;
if ($resultTotal && $resultTotal->num_rows > 0) {
    $row = $resultTotal->fetch_assoc();
    $totalEmployees = (int)$row['total'];
}

// 2) Count how many are "On Time" today (TimeRecord.Status = 'On Time')
$sqlOnTime = "
    SELECT COUNT(*) AS onTimeCount
    FROM TimeRecord
    WHERE Date = '$currentDate'
      AND Status = 'On Time'
";
$resultOnTime = $conn->query($sqlOnTime);
$onTimeCount = 0;
if ($resultOnTime && $resultOnTime->num_rows > 0) {
    $onTimeCount = (int)$resultOnTime->fetch_assoc()['onTimeCount'];
}

// 3) Count how many are "Late" today (TimeRecord.Status = 'Late')
$sqlLate = "
    SELECT COUNT(*) AS lateCount
    FROM TimeRecord
    WHERE Date = '$currentDate'
      AND Status = 'Late'
";
$resultLate = $conn->query($sqlLate);
$lateCount = 0;
if ($resultLate && $resultLate->num_rows > 0) {
    $lateCount = (int)$resultLate->fetch_assoc()['lateCount'];
}

// 4) Calculate Absent as totalEmployees - (onTimeCount + lateCount)
$absentCount = $totalEmployees - ($onTimeCount + $lateCount);

// Return as JSON
echo json_encode([
    'onTime' => $onTimeCount,
    'late'   => $lateCount,
    'absent' => $absentCount
]);

$conn->close();
?>
