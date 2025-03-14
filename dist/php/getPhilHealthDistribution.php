<?php
// getPhilHealthRates.php

// 1. Include your database connection file
// (Adjust the path to match your project structure.)
require_once __DIR__ . '/db.php';

// 2. Set the header so the client knows it's receiving JSON
header('Content-Type: application/json');

// 3. Write a query to select all columns from your philhealth_distribution table
$query = "SELECT id, coverage_year, min_monthly_salary, max_monthly_salary, premium_rate, min_monthly_premium, max_monthly_premium
          FROM philhealth_contribution";

// 4. Execute the query
$result = $conn->query($query);

// 5. Initialize an array to hold the results
$data = array();

if ($result) {
    // 6. Loop through each row, add to $data array
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    // 7. Free result set (optional)
    $result->free();
} else {
    // If query fails or returns nothing, you might set an error message
    // but you can also just return an empty array or an "error" object
    echo json_encode(["error" => "No data found or query error"]);
    $conn->close();
    exit;
}

// 8. Return the results as JSON
echo json_encode($data);

// 9. Close the database connection
$conn->close();
