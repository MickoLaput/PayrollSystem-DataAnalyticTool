<?php
// updateLeaveConversionStatus.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

// decode JSON payload
$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['ConversionID']) || empty($input['Status'])) {
    echo json_encode(['success'=>false, 'message'=>'Invalid input']);
    exit;
}

$convId = (int)$input['ConversionID'];
$status = $conn->real_escape_string($input['Status']);

try {
    // start transaction
    $conn->begin_transaction();

    // 1) update only status & decision date
    $stmt = $conn->prepare("
        UPDATE leaveconversion
        SET Status       = ?,
            DecisionDate = NOW()
        WHERE ConversionID = ?
    ");
    $stmt->bind_param('si', $status, $convId);
    $stmt->execute();
    $stmt->close();

    // 2) if approved, deduct days from leavebalance
    if ($status === 'APPROVED') {
        // fetch the conversion details
        $stmt = $conn->prepare("
            SELECT EmployeeID, LeaveType, DaysRequested
              FROM leaveconversion
             WHERE ConversionID = ?
        ");
        $stmt->bind_param('i', $convId);
        $stmt->execute();
        $details = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($details) {
            $empId         = (int)$details['EmployeeID'];
            $leaveType     = $details['LeaveType'];
            $daysRequested = (int)$details['DaysRequested'];

            // subtract from leavebalance
            $stmt = $conn->prepare("
                UPDATE leavebalance
                   SET DaysAvailable = DaysAvailable - ?
                 WHERE EmployeeID    = ?
                   AND LeaveType     = ?
            ");
            $stmt->bind_param('iis', $daysRequested, $empId, $leaveType);
            $stmt->execute();
            $stmt->close();
        }
    }

    // commit everything
    $conn->commit();
    echo json_encode(['success'=>true]);

} catch (mysqli_sql_exception $e) {
    // rollback on error
    $conn->rollback();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

$conn->close();
?>
