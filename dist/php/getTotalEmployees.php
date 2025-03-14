<?php
require_once __DIR__ . '/db.php';

$query = "SELECT COUNT(*) AS total FROM Employee";
$result = $conn->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    $total = intval($row['total']);
    echo json_encode(["total" => $total]);
} else {
    echo json_encode(["error" => "Error: " . $conn->error]);
}

$conn->close();
?>
