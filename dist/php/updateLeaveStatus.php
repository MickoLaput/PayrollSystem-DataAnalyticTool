<?php
require_once __DIR__ . '/db.php';

// Get the data from the request
$data = json_decode(file_get_contents('php://input'), true);
$leaveFileID = $data['leaveFileID'];
$status = $data['status'];

// Fetch the leave type, employee ID, start date, and end date for the leave request
$query = "SELECT LeaveType, EmployeeID, LeaveStartDate, LeaveEndDate FROM LeaveFile WHERE LeaveFileID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $leaveFileID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $leaveRequest = $result->fetch_assoc();
    $leaveType = $leaveRequest['LeaveType'];
    $employeeID = $leaveRequest['EmployeeID'];
    $leaveStartDate = $leaveRequest['LeaveStartDate'];
    $leaveEndDate = $leaveRequest['LeaveEndDate'];

    // Update the indicator status in LeaveFile table
    $queryUpdate = "UPDATE LeaveFile SET indicator = ? WHERE LeaveFileID = ?";
    $stmtUpdate = $conn->prepare($queryUpdate);
    $stmtUpdate->bind_param('si', $status, $leaveFileID);

    if ($stmtUpdate->execute()) {
        // Only deduct leave days if status is approved
        if ($status === 'APPROVED') {
            // Fetch current days available for this leave type
            $queryBalance = "SELECT DaysAvailable FROM LeaveBalance WHERE EmployeeID = ? AND LeaveType = ?";
            $stmtBalance = $conn->prepare($queryBalance);
            $stmtBalance->bind_param('is', $employeeID, $leaveType);
            $stmtBalance->execute();
            $resultBalance = $stmtBalance->get_result();

            if ($resultBalance->num_rows > 0) {
                $balance = $resultBalance->fetch_assoc();
                $daysAvailable = $balance['DaysAvailable'];

                // Calculate new days available
                $daysUsed = (strtotime($leaveEndDate) - strtotime($leaveStartDate)) / (60 * 60 * 24) + 1;
                $newDaysAvailable = $daysAvailable - $daysUsed;

                // Update the leave balance with the new days available
                $queryDeduct = "UPDATE LeaveBalance SET DaysAvailable = ? WHERE EmployeeID = ? AND LeaveType = ?";
                $stmtDeduct = $conn->prepare($queryDeduct);
                $stmtDeduct->bind_param('iis', $newDaysAvailable, $employeeID, $leaveType);
                $stmtDeduct->execute();
                $stmtDeduct->close();

                echo "Leave request approved and balance updated.";
            } else {
                echo "Error: Leave balance for the type '$leaveType' not found.";
            }

            $stmtBalance->close();
        } else {
            echo "Leave request status updated.";
        }
    } else {
        echo "Error updating leave request status.";
    }

    $stmtUpdate->close();
} else {
    echo "Error: Leave request not found.";
}

$stmt->close();
$conn->close();
?>
