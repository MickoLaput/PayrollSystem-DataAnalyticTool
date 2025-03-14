<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $employeeID = intval($_GET['id']);
    
    // Update query to set Active_ind to FALSE instead of deleting the record
    $sql = "UPDATE Employee SET Active_ind = FALSE WHERE EmployeeID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employeeID);
    
    if ($stmt->execute()) {
        echo "Employee set to inactive successfully.";
    } else {
        echo "Error setting employee to inactive: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
