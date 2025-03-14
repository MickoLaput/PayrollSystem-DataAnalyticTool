<?php
// Include FPDF library using an absolute path relative to this file
require_once __DIR__ . '/fpdf/fpdf.php';

// Database connection
require_once __DIR__ . '/db.php';

session_start();
if (!isset($_SESSION['employee_id']) || !isset($_GET['payrollID'])) {
    die("Invalid request.");
}
$employeeId = $_SESSION['employee_id'];
$payrollID = $_GET['payrollID'];

// Fetch the payslip and employee details; note that the Department join has been removed
// and DateAdded has been added from the Employee table
$sql = "SELECT 
            p.PayDate, 
            p.TotalHours, 
            p.GrossPay, 
            p.NetPay, 
            p.SocialSecuritySystem, 
            p.PagIbig, 
            p.PhilHealth, 
            p.Tax, 
            p.OvertimePay, 
            p.OvertimeHours, 
            e.FirstName, 
            e.MiddleName, 
            e.LastName,
            e.DateAdded, 
            pos.SalaryPosition, 
            pos.PositionName, 
            p.SalaryLoan, 
            e.MaxicareType, 
            p.Maxicare
        FROM Payroll p
        JOIN Employee e ON p.EmployeeID = e.EmployeeID
        LEFT JOIN `Position` pos ON e.PositionID = pos.PositionID
        WHERE p.PayrollID = ? AND p.EmployeeID = ?";
        
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing SQL statement: " . $conn->error);
}
$stmt->bind_param("ii", $payrollID, $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No record found.");
}

$payslip = $result->fetch_assoc();

// Create a new PDF document
$pdf = new FPDF();
$pdf->AddPage();

// Set font and add header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Payslip', 0, 1, 'C');
$pdf->Ln(5);

// Company and address
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Company Inc', 0, 1, 'C');
$pdf->Cell(0, 10, '<Company Address>', 0, 1, 'C');
$pdf->Cell(0, 10, '<Company Address>', 0, 1, 'C');
$pdf->Ln(10);

// Date and employee details header
// Use DateAdded as the Date of Joining
$pdf->Cell(95, 10, 'Date of Joining: ' . date('M d, Y', strtotime($payslip['DateAdded'])), 0, 0, 'L');
$pdf->Cell(95, 10, 'Employee Name: ' . $payslip['FirstName'] . ' ' . $payslip['MiddleName'] . ' ' . $payslip['LastName'], 0, 1, 'L');
$pdf->Cell(95, 10, 'Pay Period: ' . $payslip['PayDate'], 0, 0, 'L');
$pdf->Cell(95, 10, 'Designation: ' . $payslip['PositionName'], 0, 1, 'L');
// Since Department is removed, we don't output it here.
$pdf->Cell(95, 10, 'Overtime Hours: ' . $payslip['OvertimeHours'], 0, 0, 'L');
$pdf->Cell(95, 10, 'MaxicareType: ' . $payslip['MaxicareType'], 0, 1, 'L');
$pdf->Ln(10);

// Earnings and deductions section header
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(90, 10, 'Earnings', 1, 0, 'C');
$pdf->Cell(90, 10, 'Amount', 1, 1, 'C');
$pdf->SetFont('Arial', '', 12);

// Earnings - Amount
$pdf->Cell(90, 10, 'Gross Pay', 1, 0, 'L');
$pdf->Cell(90, 10, 'PHP ' . number_format($payslip['GrossPay'], 2), 1, 1, 'L');
$pdf->Cell(90, 10, 'Overtime Pay', 1, 0, 'L');
$pdf->Cell(90, 10, 'PHP ' . number_format($payslip['OvertimePay'], 2), 1, 1, 'L');

// Add "DEDUCTIONS" row (merged)
$pdf->Cell(180, 10, 'DEDUCTIONS', 1, 1, 'C');

// Deductions - Amount
$pdf->Cell(90, 10, 'SSS', 1, 0, 'L');
$pdf->Cell(90, 10, 'PHP ' . number_format($payslip['SocialSecuritySystem'], 2), 1, 1, 'L');
$pdf->Cell(90, 10, 'Pag-IBIG', 1, 0, 'L');
$pdf->Cell(90, 10, 'PHP ' . number_format($payslip['PagIbig'], 2), 1, 1, 'L');
$pdf->Cell(90, 10, 'PhilHealth', 1, 0, 'L');
$pdf->Cell(90, 10, 'PHP ' . number_format($payslip['PhilHealth'], 2), 1, 1, 'L');
$pdf->Cell(90, 10, 'Tax', 1, 0, 'L');
$pdf->Cell(90, 10, 'PHP ' . number_format($payslip['Tax'], 2), 1, 1, 'L');
$pdf->Cell(90, 10, 'Salary Loan', 1, 0, 'L');
$pdf->Cell(90, 10, 'PHP ' . number_format($payslip['SalaryLoan'], 2), 1, 1, 'L');
$pdf->Cell(90, 10, 'Maxicare', 1, 0, 'L');
$pdf->Cell(90, 10, 'PHP ' . number_format($payslip['Maxicare'], 2), 1, 1, 'L');

// Add an empty merged row after Tax
$pdf->Cell(180, 10, '', 0, 1, 'C');

// Total Earnings and Total Deductions
$pdf->Cell(90, 10, 'Total Earnings', 1, 0, 'L');
$pdf->Cell(90, 10, 'PHP ' . number_format($payslip['GrossPay'] + $payslip['OvertimePay'], 2), 1, 1, 'L');
$pdf->Cell(90, 10, 'Total Deductions', 1, 0, 'L');
$totalDeductions = $payslip['SocialSecuritySystem'] + $payslip['PagIbig'] + $payslip['PhilHealth'] + $payslip['Tax'] + $payslip['SalaryLoan'] + $payslip['Maxicare'];
$pdf->Cell(90, 10, 'PHP ' . number_format($totalDeductions, 2), 1, 1, 'L');

$pdf->Ln(5);
$pdf->Cell(90, 10, 'Net Pay', 0, 0, 'L');
$pdf->Cell(90, 10, 'PHP ' . number_format($payslip['NetPay'], 2), 0, 1, 'L');
$pdf->Ln(10);

$pdf->Cell(90, 10, 'Employer Signature', 'B', 0, 0, 'L');
$pdf->Cell(90, 10, 'Employee Signature', 'B', 0, 1, 'L');
$pdf->Ln(10);
$pdf->Cell(0, 10, 'This is system generated payslip', 0, 1, 'C');

// Output the PDF as a download
$pdf->Output('D', 'Payslip_' . $payslip['PayDate'] . '-' . $payslip['FirstName'] . $payslip['LastName'] . '.pdf');

$stmt->close();
$conn->close();
?>
