<?php
require_once __DIR__ . '/db.php';

// Get today's date in YYYY-MM-DD format
$today = date("Y-m-d");

// Query to count how many are present (on time), late, or absent
$query = "
    SELECT 
        SUM(CASE WHEN Status = 'present' THEN 1 ELSE 0 END) AS onTime,
        SUM(CASE WHEN Status = 'late' THEN 1 ELSE 0 END) AS late,
        SUM(CASE WHEN Status = 'absent' THEN 1 ELSE 0 END) AS absent
    FROM TimeRecord
    WHERE Date = '$today'
";

$result = $conn->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    $onTime = intval($row['onTime']);
    $late   = intval($row['late']);
    $absent = intval($row['absent']);

    // Return JSON response
    echo json_encode([
        "onTime" => $onTime,
        "late"   => $late,
        "absent" => $absent
    ]);
} else {
    echo json_encode(["error" => "Error: " . $conn->error]);
}

$conn->close();
?>
