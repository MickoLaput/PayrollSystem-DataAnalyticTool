<?php
// getSSSContributionRates.php
header('Content-Type: application/json');

// Include your database connection file
require_once __DIR__ . '/db.php';

// Prepare and execute the SQL query
$sql = "
    SELECT 
        year,
        total_contribution_rate,
        employer_share,
        employee_share,
        min_monthly_salary_credit,
        max_monthly_salary_credit
    FROM sss_contribution
    ORDER BY year ASC
";

$result = $conn->query($sql);

// Initialize an empty array for results
$data = [];

// If there are rows, fetch them into $data
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Each row will have keys matching your column names
        $data[] = $row;
    }
}

// Close the DB connection (optional)
$conn->close();

// Output the data as JSON
echo json_encode($data);
