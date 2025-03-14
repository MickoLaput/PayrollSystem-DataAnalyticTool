<?php
/**
 * Example getPieChartData.php
 * 
 * This script outputs a JSON object with the counts of:
 *   - lateCount
 *   - presentCount
 *   - absentCount
 * 
 * Assumes:
 *   - "employee" table for the full list of employees
 *   - "timerecord" table for daily attendance
 *   - "Status" column in "timerecord" that can be "LATE" or "ONTIME" (or similar)
 *   - The "Date" column in "timerecord" stores YYYY-MM-DD for the record
 *   - We define "absent" as employees who have no timerecord row for the day
 *   - We define "late" as employees whose timerecord row has Status = "LATE"
 *   - We define "present" as employees whose timerecord row has Status = "ONTIME"
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Connect to DB (adjust credentials to match your setup)
require_once __DIR__ . '/db.php';

// 2. Determine "today" (or you might pass a date from GET)
$today = date('Y-m-d');

// 3. Query total number of employees
$sqlTotal = "SELECT COUNT(*) AS totalEmployees FROM employee";
$resultTotal = $conn->query($sqlTotal);
if (!$resultTotal) {
    // DB error
    echo json_encode(['error' => 'Database error fetching total employees.']);
    exit;
}
$rowTotal = $resultTotal->fetch_assoc();
$totalEmployees = (int) $rowTotal['totalEmployees'];

// 4. Query attendance for today
//    - We'll assume there's a "Status" column that says "LATE" or "ONTIME"
$sqlAttendance = "
    SELECT EmployeeID, Status
    FROM timerecord
    WHERE Date = '$today'
";
$resultAttendance = $conn->query($sqlAttendance);
if (!$resultAttendance) {
    // DB error
    echo json_encode(['error' => 'Database error fetching attendance.']);
    exit;
}

// 5. Initialize counters
$lateCount = 0;
$presentCount = 0;
// We won't track absentCount directly here—will calculate as difference

// 6. For each row, increment late or present
//    - "Absent" employees are simply those not in timerecord for today
$seenEmployeeIDs = []; // track who has a timerecord row

while ($row = $resultAttendance->fetch_assoc()) {
    $seenEmployeeIDs[] = $row['EmployeeID'];

    if (strtoupper($row['Status']) === 'LATE') {
        $lateCount++;
    } else {
        // We'll assume everything else means "present/on-time"
        $presentCount++;
    }
}

// 7. Calculate absent
//    - We know total employees, and how many had records
$employeesWithRecords = count(array_unique($seenEmployeeIDs));
$absentCount = $totalEmployees - $employeesWithRecords;

// 8. Output JSON
echo json_encode([
    'lateCount' => $lateCount,
    'presentCount' => $presentCount,
    'absentCount' => $absentCount
]);

$conn->close();
?>
