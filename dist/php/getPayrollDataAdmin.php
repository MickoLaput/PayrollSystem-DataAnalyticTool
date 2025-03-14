<?php
require_once __DIR__ . '/db.php';

// Get the selected year
$year = isset($_GET['year']) ? $_GET['year'] : date('Y'); // Default to the current year if not set

// Query to get the payroll data for all employees based on the selected year
$query = "
    SELECT 
        MONTH(p.PayDate) AS month,
        SUM(p.TotalHours) AS regularHours,
        SUM(p.OvertimeHours) AS overtimeHours,
        (SUM(p.TotalHours) + SUM(p.OvertimeHours)) AS totalWorkedHours,
        SUM(p.NetPay) AS totalWage
    FROM Payroll p
    WHERE YEAR(p.PayDate) = ? AND p.Flag = TRUE
    GROUP BY MONTH(p.PayDate)
    ORDER BY MONTH(p.PayDate)
";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();

// Check if any data was fetched
if ($result->num_rows > 0) {
    $payrollData = [];
    while ($row = $result->fetch_assoc()) {
        $payrollData[] = [
            'month' => date("F", mktime(0, 0, 0, $row['month'], 10)), // Convert month number to month name
            'regularHours' => $row['regularHours'],
            'overtimeHours' => $row['overtimeHours'],
            'totalWorkedHours' => $row['totalWorkedHours'],
            'totalWage' => $row['totalWage']
        ];
    }

    // Return the data as JSON
    echo json_encode(['monthlySummary' => $payrollData]);
} else {
    // If no data is found, return an error message
    echo json_encode(['error' => 'No payroll data found for the selected year.']);
}

// Close the database connection
$stmt->close();
$conn->close();
?>
