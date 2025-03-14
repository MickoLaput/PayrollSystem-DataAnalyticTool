<?php
require_once __DIR__ . '/db.php';

if (isset($_GET['id'])) {
    $employeeID = intval($_GET['id']);
    
    $sql = $conn->prepare("SELECT Employee.FirstName, Employee.MiddleName, Employee.LastName, Employee.Email, Employee.BirthDate, Employee.Age, Employee.Status, Employee.Phone,
                                  Employee.Barangay, Employee.StreetNBuildingHouseNo as StreetNumber, Employee.City, Employee.Postal_zip_code as ZipCode,
                                  Position.SalaryPosition AS Salary, Position.PositionName as Position, User.FaceData as FaceData, Employee.MaxicareType as MaxicareType,
                                  Employee.DateAdded, Employee.SalaryLoan_ind
                           FROM Employee 
                           JOIN Position ON Employee.PositionID = Position.PositionID
                           JOIN User ON Employee.EmployeeID = User.EmployeeID
                           WHERE Employee.EmployeeID = ?");

    if ($sql) {
        $sql->bind_param("i", $employeeID);
        $sql->execute();
        
        $result = $sql->get_result();
        
        if ($result->num_rows > 0) {
            $employeeDetails = $result->fetch_assoc();
            
            // Encode FaceData only if it's not null
            if (!is_null($employeeDetails['FaceData'])) {
                $employeeDetails['FaceData'] = base64_encode($employeeDetails['FaceData']);
            }

            echo json_encode($employeeDetails);
        } else {
            echo json_encode(['error' => 'Employee not found']);
        }

        $sql->close();
    } else {
        echo json_encode(['error' => 'SQL preparation failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['error' => 'No employee ID provided']);
}

$conn->close();
?>
