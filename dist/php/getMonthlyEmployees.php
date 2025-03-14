<?php
// getMonthlyEmployees.php
//include 'db.php'; // your database connection
require_once __DIR__ . '/db.php';
$currentYear = date('Y');

// Create an array [1..12], each starting at 0
$monthData = array_fill(1, 12, 0);

// Query: Count how many were added in each month of the current year
$sql = "SELECT MONTH(date_added) AS month_num, COUNT(*) AS count
        FROM User
        WHERE YEAR(date_added) = ?
        GROUP BY MONTH(date_added)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $currentYear);
$stmt->execute();
$result = $stmt->get_result();

// Fill the array with actual counts
while ($row = $result->fetch_assoc()) {
    $month = (int)$row['month_num'];
    $count = (int)$row['count'];
    $monthData[$month] = $count;
}

$stmt->close();
$conn->close();

// Return as JSON object, e.g. { "1": 5, "2": 2, ... }
echo json_encode($monthData);
?>
