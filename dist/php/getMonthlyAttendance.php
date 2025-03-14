<?php
// php/getMonthlyTimerecord.php
require_once __DIR__ . '/db.php'; // Ensure this file sets up $conn = new mysqli(...)

$currentYear = date('Y');

// Create an array of 12 months, each starting at 0
$monthData = array_fill(1, 12, 0);

// Query to count records in timerecord table by month for the current year
$sql = "SELECT MONTH(`Date`) AS month_num, COUNT(*) AS count
        FROM timerecord
        WHERE YEAR(`Date`) = ?
        GROUP BY MONTH(`Date`)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $currentYear);
$stmt->execute();
$result = $stmt->get_result();

// Populate $monthData with the counts
while ($row = $result->fetch_assoc()) {
    $month = (int)$row['month_num'];
    $count = (int)$row['count'];
    $monthData[$month] = $count;
}

$stmt->close();
$conn->close();

// Return as JSON object, e.g. { "1": 5, "2": 2, "3": 0, ... }
echo json_encode($monthData);
?>
