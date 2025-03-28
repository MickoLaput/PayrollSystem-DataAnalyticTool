<?php
session_start();
require_once __DIR__ . '/db.php'; // Include your database connection file

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable detailed error reporting

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture form data
    $employeeID = $_POST['employeeId'];
    if (empty($employeeID)) {
        echo "Error: Employee ID is missing.";
        exit;
    }

    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'] ?? null; // Optional
    $lastName = $_POST['lastName'];
    $birthDate = $_POST['birthDate'];
    $age = (int) $_POST['age'];
    $status = $_POST['status'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $barangay = $_POST['barangay'];
    $streetNumber = $_POST['streetNumber'];
    $city = $_POST['city'];
    $zipCode = (int) $_POST['zipCode'];
    $maxicareType = $_POST['maxicareType']; // Enum: not_applicable/silver/gold/platinum/platinum_plus
    $salaryLoan = $_POST['salaryLoan']; // Enum: No/Yes
    $positionID = (int) $_POST['position'];

    // Validate ENUM values for 'MaxicareType'
    if (!in_array($maxicareType, ['not_applicable', 'silver', 'gold', 'platinum', 'platinum_plus'])) {
        echo "Error: Invalid value for MaxicareType.";
        exit;
    }

    // Validate ENUM values for 'SalaryLoan_ind'
    if (!in_array($salaryLoan, ['No', 'Yes'])) {
        echo "Error: Invalid value for SalaryLoan_ind.";
        exit;
    }

    // Validate PositionID
    if ($positionID) {
        $positionCheck = $conn->prepare("SELECT PositionID FROM `Position` WHERE PositionID = ?");
        $positionCheck->bind_param("i", $positionID);
        $positionCheck->execute();
        if ($positionCheck->get_result()->num_rows == 0) {
            echo "Error: Invalid Position ID.";
            exit;
        }
        $positionCheck->close();
    }

    // Prepare the SQL update statement
    $stmt = $conn->prepare("
        UPDATE Employee
        SET FirstName = ?, 
            MiddleName = ?, 
            LastName = ?, 
            BirthDate = ?, 
            Age = ?, 
            Status = ?, 
            Email = ?, 
            Phone = ?, 
            Barangay = ?, 
            StreetNBuildingHouseNo = ?, 
            City = ?, 
            Postal_zip_code = ?, 
            MaxicareType = ?, 
            SalaryLoan_ind = ?, 
            PositionID = ?
        WHERE EmployeeID = ?
    ");

    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "ssssissssssssssi",
        $firstName,
        $middleName,
        $lastName,
        $birthDate,
        $age,
        $status,
        $email,
        $phone,
        $barangay,
        $streetNumber,
        $city,
        $zipCode,
        $maxicareType,
        $salaryLoan,
        $positionID,
        $employeeID
    );

    // Execute and check for success
    try {
        $stmt->execute();
        echo "Employee updated successfully!";
    } catch (mysqli_sql_exception $e) {
        // Handle duplicate email error
        if ($e->getCode() === 1062) {
            echo "Error: Email address already exists.";
        } else {
            echo "Error updating employee: " . $e->getMessage();
        }
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
