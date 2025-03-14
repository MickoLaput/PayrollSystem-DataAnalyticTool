<?php
require_once __DIR__ . '/db.php';

$data = json_decode(file_get_contents("php://input"));
$payrollID = $data->payrollID;

if ($payrollID) {
    // Delete the payslip record based on PayrollID
    $sql = "DELETE FROM Payroll WHERE PayrollID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $payrollID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Payslip deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete payslip.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid PayrollID.']);
}

$conn->close();
?>
