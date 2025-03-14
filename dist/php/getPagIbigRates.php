<?php
// getPagIbigRates.php
require_once __DIR__ . '/db.php'; // Make sure this points to your database connection script

$sql = "SELECT id, min_income, max_income, employee_rate, employer_rate FROM pagibig_contribution";
$result = $conn->query($sql);

$data = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
$conn->close();
?>
