<?php
session_start();
require_once __DIR__ . '/db.php';

// Check if the user is logged in and has an employee ID
if (!isset($_SESSION['employee_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$employeeId = $_SESSION['employee_id'];
$year = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

// Query to get regular hours, overtime hours, and total wage for the year
$query = "
    SELECT 
        MONTH(PayDate) AS month,
        COALESCE(SUM(TotalHours), 0) AS regularHours,
        COALESCE(SUM(OvertimeHours), 0) AS overtimeHours,
        COALESCE(SUM(NetPay), 0) AS totalWage
    FROM Payroll
    WHERE EmployeeID = ? AND YEAR(PayDate) = ? AND Flag = TRUE
    GROUP BY MONTH(PayDate)
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $employeeId, $year);
$stmt->execute();
$result = $stmt->get_result();

$monthlySummary = [];
while ($row = $result->fetch_assoc()) {
    $totalWorkedHours = $row['regularHours'] + $row['overtimeHours'];
    $monthlySummary[] = [
        'month' => date('F', mktime(0, 0, 0, $row['month'], 1)),
        'regularHours' => $row['regularHours'],
        'overtimeHours' => $row['overtimeHours'],
        'totalWage' => $row['totalWage'],
        'totalWorkedHours' => $totalWorkedHours
    ];
}

$totalRegularHours = array_sum(array_column($monthlySummary, 'regularHours'));
$totalOvertimeHours = array_sum(array_column($monthlySummary, 'overtimeHours'));
$totalWage = array_sum(array_column($monthlySummary, 'totalWage'));
$totalWorkedHours = array_sum(array_column($monthlySummary, 'totalWorkedHours'));

echo json_encode([
    'totalRegularHours' => $totalRegularHours,
    'totalOvertimeHours' => $totalOvertimeHours,
    'totalWage' => $totalWage,
    'totalWorkedHours' => $totalWorkedHours,
    'monthlySummary' => $monthlySummary
]);

$stmt->close();
$conn->close();
?>
