<?php
session_start();
header('Content-Type: application/json'); // Ensure JSON is returned

require_once __DIR__ . '/db.php';

// Check if the user is logged in and has an employee ID
if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$employeeId = $_SESSION['employee_id'];

// Get month and year from query parameters (if provided)
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year  = isset($_GET['year']) ? $_GET['year'] : '';

// Base query to get payroll and employee information (without Department)
$sql = "SELECT 
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
            Payroll.Maxicare AS Maxicare
        FROM Payroll 
        JOIN Employee ON Payroll.EmployeeID = Employee.EmployeeID
        LEFT JOIN Position ON Employee.PositionID = Position.PositionID
        WHERE Payroll.EmployeeID = ?
          AND Payroll.Flag = TRUE";

// Append additional filtering conditions based on provided month and year
if ($month && $year) {
    $sql .= " AND MONTH(Payroll.PayDate) = ? AND YEAR(Payroll.PayDate) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $employeeId, $month, $year);
} elseif ($year) {
    $sql .= " AND YEAR(Payroll.PayDate) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $employeeId, $year);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employeeId);
}

$stmt->execute();
$result = $stmt->get_result();

$payslips = [];
while ($row = $result->fetch_assoc()) {
    $payslips[] = $row;
}

// Return the data as JSON
echo json_encode($payslips);

$stmt->close();
$conn->close();
?>
