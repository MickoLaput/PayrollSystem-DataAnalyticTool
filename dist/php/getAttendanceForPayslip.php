<?php
// getAttendanceForPayslip.php

require_once __DIR__ . '/db.php';

// Get parameters from the URL
$employeeId = $_GET['employeeId'];
$fromDate   = $_GET['fromDate'];
$toDate     = $_GET['toDate'];

// Prepare the query to get attendance records from `TimeRecord`,
// left-joining on `LeaveFile` to see if the date is within an approved leave range,
// and also joining `user` + `schedule` to retrieve the scheduled TimeOut.
// NOTE: We wrap table names and columns in backticks to avoid conflicts with reserved words.
$query = "
    SELECT 
        tr.`Date`,
        tr.`CheckInTime`,
        tr.`CheckOutTime`,
        tr.`Status` AS TimeRecordStatus,
        lf.`LeaveStartDate`,
        lf.`LeaveEndDate`,
        lf.`Indicator` AS LeaveStatus,
        s.`TimeOut` AS ScheduledTimeOut
    FROM `TimeRecord` tr
    LEFT JOIN `user` u 
        ON tr.`EmployeeID` = u.`EmployeeID`
    LEFT JOIN `schedule` s
        ON u.`ScheduleID` = s.`ScheduleID`
    LEFT JOIN `LeaveFile` lf
        ON tr.`EmployeeID` = lf.`EmployeeID`
       AND tr.`Date` BETWEEN lf.`LeaveStartDate` AND lf.`LeaveEndDate`
    WHERE tr.`EmployeeID` = ?
      AND tr.`Date` BETWEEN ? AND ?
    ORDER BY tr.`Date`
";

// Prepare the statement
$stmt = $conn->prepare($query);

// If preparation fails, show the error and exit
if (!$stmt) {
    die("SQL error in prepare: " . $conn->error);
}

// Bind parameters
$stmt->bind_param("iss", $employeeId, $fromDate, $toDate);
$stmt->execute();
$result = $stmt->get_result();

$attendanceRecords = [];

while ($row = $result->fetch_assoc()) {
    // 1. Determine final status based on TimeRecord.Status and LeaveFile.Status
    //    (e.g., 'Present', 'Late', 'Absent', etc.)
    $finalStatus = $row['TimeRecordStatus'] ?: 'Absent'; // if TimeRecordStatus is empty, mark as 'Absent'

    // If the LeaveFile status is APPROVED, override with 'On Leave'
    if ($row['LeaveStatus'] === 'APPROVED') {
        $finalStatus = 'On Leave';
    }

    // 2. Calculate hours worked and overtime only if CheckIn/CheckOut exist
    //    and final status is not 'On Leave' or 'Absent'
    $hoursWorked = 0;
    $overtimePay = 0;

    if (!empty($row['CheckInTime']) && !empty($row['CheckOutTime']) 
        && $finalStatus !== 'On Leave' 
        && $finalStatus !== 'Absent'
    ) {
        $checkIn  = strtotime($row['CheckInTime']);
        $checkOut = strtotime($row['CheckOutTime']);

        // Basic hours worked
        $hoursWorked = ($checkOut - $checkIn) / 3600;  // convert seconds to hours

        // Compare CheckOutTime to the scheduled TimeOut (if we have one)
        if (!empty($row['ScheduledTimeOut'])) {
            $scheduledTimeOut = strtotime($row['ScheduledTimeOut']);
            if ($checkOut > $scheduledTimeOut) {
                // Calculate the difference in seconds, then convert to hours
                $exceedingTime  = $checkOut - $scheduledTimeOut;
                $exceedingHours = $exceedingTime / 3600;
                // For example, floor the exceeding hours
                $overtimePay = floor($exceedingHours);
            }
        }
    }

    // 3. Add the record to the array
    $attendanceRecords[] = [
        'Date'        => $row['Date'],
        'Status'      => $finalStatus,
        'HoursWorked' => round($hoursWorked, 2),
        'OvertimePay' => $overtimePay
    ];
}

// Return the data as JSON
echo json_encode($attendanceRecords);

// Close the statement and connection
$stmt->close();
$conn->close();
