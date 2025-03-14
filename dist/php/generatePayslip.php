<?php
// Include your database connection file
require_once __DIR__ . '/db.php';

// Get data fields from the frontend request
$data = json_decode(file_get_contents("php://input"), true);


// Ensure that overtimepay has a default value if not set in the frontend
$overtimepay = isset($data['overtimePay']) ? floatval($data['overtimePay']) : 0.0;

// Ensure salaryLoan has a default value if not set
$salaryLoan = isset($data['salaryLoan']) ? floatval($data['salaryLoan']) : 0.0; // Set default to 0 if missing

//maxicare
//$maxicare = isset($data['maxicare']) ? floatval($data['maxicare']) : 0.0;
$maxicare = floatval($data['maxicare']);

// Get other data fields
$employeeId = $data['employeeId'];
$payDate = $data['payDate'];
$totalHours = isset($data['totalHours']) ? floatval($data['totalHours']) : 0.0;  // Default to 0 if missing
$grossPay = floatval($data['grossPay']);
$netPay = floatval($data['netPay']);
$sss = floatval($data['sss']);
$pagibig = floatval($data['pagibig']);
$tax = isset($data['tax']) ? floatval($data['tax']) : NULL; // Ensure tax is nullable
$philhealth = floatval($data['philhealth']);
$overtimehours = isset($data['overtimeHours']) ? intval($data['overtimeHours']) : 0;  // Ensure overtimehours is treated as an integer

// Insert the payslip data into the Payroll table
$query = "INSERT INTO Payroll (EmployeeID, PayDate, TotalHours, GrossPay, NetPay, SocialSecuritySystem, PagIbig, Tax, OvertimePay, OvertimeHours, PhilHealth, SalaryLoan, Maxicare) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";  // Added SalaryLoan in the query
$stmt = $conn->prepare($query);

if ($stmt === false) {
    // If the statement preparation failed, output an error
    die("Error preparing statement: " . $conn->error);
}


error_log("Overtime Pay: " . $overtimepay); 
// Log the overtime pay value using PHP's error_log (instead of console.log)
error_log("Sending Overtime Pay: " . $overtimepay); // PHP log, it will show in your PHP error logs

// Ensure the correct types are passed to bind_param
// We use `d` for decimals and `i` for integers. `s` for string is used for date field
$stmt->bind_param("isdddddddiidd", $employeeId, $payDate, $totalHours, $grossPay, $netPay, $sss, $pagibig, $tax, $overtimepay, $overtimehours, $philhealth, $salaryLoan, $maxicare);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Payslip generated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to generate payslip.']);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
