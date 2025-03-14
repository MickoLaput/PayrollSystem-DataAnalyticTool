<?php
require_once __DIR__ . '/db.php'; // Ensure the DB connection is included

$data = json_decode(file_get_contents("php://input")); // Get the JSON data sent from JS
$payrollID = $data->payrollID;

if ($payrollID) {
    // Update the 'Flag' field to TRUE for the given PayrollID
    $sql = "UPDATE Payroll SET Flag = TRUE WHERE PayrollID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $payrollID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Payslip released successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to release payslip.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid PayrollID.']);
}

$conn->close();
?>
