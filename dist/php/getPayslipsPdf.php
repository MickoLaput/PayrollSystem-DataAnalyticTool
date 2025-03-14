<?php
// Database connection
require_once __DIR__ . '/db.php';

session_start();
$employeeId = isset($_GET['employeeId']) ? $_GET['employeeId'] : null;
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

if (!$employeeId) {
    echo json_encode([]); // If no employee ID, return empty array
    exit;
}

// Base query (no Department references):
$sql = "
    SELECT 
        Payroll.PayrollID,
        Payroll.PayDate,
        Payroll.TotalHours,
        Payroll.GrossPay,
        Payroll.NetPay,
        Payroll.OvertimePay,
        Payroll.SocialSecuritySystem,
        Payroll.PagIbig,
        Payroll.PhilHealth,
        Payroll.Tax,
        CONCAT(Employee.FirstName, ' ', COALESCE(Employee.MiddleName, ''), ' ', Employee.LastName) AS EmployeeName,
        Position.SalaryPosition AS Salary,
        Position.PositionName AS Position,
        Payroll.OvertimeHours,
        Payroll.SalaryLoan,
        Payroll.Maxicare
    FROM Payroll
    JOIN Employee ON Payroll.EmployeeID = Employee.EmployeeID
    LEFT JOIN Position ON Employee.PositionID = Position.PositionID
    WHERE Payroll.EmployeeID = ?
";

// Build up the WHERE clauses for optional month/year
if ($month && $year) {
    $sql .= " AND MONTH(Payroll.PayDate) = ? AND YEAR(Payroll.PayDate) = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("iii", $employeeId, $month, $year);

} elseif ($year) {
    $sql .= " AND YEAR(Payroll.PayDate) = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $employeeId, $year);

} else {
    // No month/year filter
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $employeeId);
}

$stmt->execute();
$result = $stmt->get_result();

$payslips = [];
while ($row = $result->fetch_assoc()) {
    $payslips[] = $row;
}

echo json_encode($payslips);
$conn->close();
