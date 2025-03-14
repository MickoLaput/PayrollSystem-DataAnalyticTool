<?php
// Include the database connection file
//require_once 'db.php'; // Make sure this path is correct
require_once __DIR__ . '/db.php';

// Prepare and execute the query to fetch positions
$sql = "SELECT PositionID, PositionName FROM Position";
$result = $conn->query($sql);

$positions = [];
if ($result->num_rows > 0) {
    // Fetch each row and add to the positions array
    while ($row = $result->fetch_assoc()) {
        $positions[] = $row;
    }
}

// Return the result as a JSON object
header('Content-Type: application/json');
echo json_encode($positions);

// Close the connection
$conn->close();
?>
