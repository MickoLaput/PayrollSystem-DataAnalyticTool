<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Capture form data safely
    $firstName     = isset($_POST['firstName']) ? $conn->real_escape_string($_POST['firstName']) : null;
    $middleName    = !empty($_POST['middleName']) ? $conn->real_escape_string($_POST['middleName']) : null;
    $lastName      = isset($_POST['lastName']) ? $conn->real_escape_string($_POST['lastName']) : null;
    $birthDate     = isset($_POST['birthdate']) ? $conn->real_escape_string($_POST['birthdate']) : null;
    $age           = isset($_POST['age']) ? intval($_POST['age']) : null;
    $status        = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : null;
    $sex           = isset($_POST['sex']) ? $conn->real_escape_string($_POST['sex']) : null;
    $email         = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : null;
    $phone         = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : null;
    $barangay      = isset($_POST['barangay']) ? $conn->real_escape_string($_POST['barangay']) : null;
    $streetNumber  = isset($_POST['streetnumber']) ? $conn->real_escape_string($_POST['streetnumber']) : null;
    $city          = isset($_POST['city']) ? $conn->real_escape_string($_POST['city']) : null;
    $postalZipCode = isset($_POST['postalzipcode']) ? intval($_POST['postalzipcode']) : null;
    $positionID    = isset($_POST['position']) ? intval($_POST['position']) : null;
    $maxicaretype  = isset($_POST['maxicaretype']) ? $conn->real_escape_string($_POST['maxicaretype']) : null;
    $salaryLoanInd = isset($_POST['salaryloanoption']) ? $conn->real_escape_string($_POST['salaryloanoption']) : null;
    $username      = isset($_POST['username']) ? $conn->real_escape_string($_POST['username']) : null;
    $password      = isset($_POST['password']) ? $conn->real_escape_string($_POST['password']) : null;
    $role          = isset($_POST['role']) ? $conn->real_escape_string($_POST['role']) : null;

    // -- Schedule fields
    $timeIn        = isset($_POST['timein']) ? $conn->real_escape_string($_POST['timein']) : null;
    $timeOut       = isset($_POST['timeout']) ? $conn->real_escape_string($_POST['timeout']) : null;
    $shift         = isset($_POST['shift']) ? $conn->real_escape_string($_POST['shift']) : null;

    // Validate that required fields are provided (adjust as needed)
    if (
        $firstName && $lastName && $birthDate && $age && $status && $sex &&
        $email && $username && $password && $positionID && $role &&
        $maxicaretype && $barangay && $streetNumber && $city && $postalZipCode &&
        $timeIn && $timeOut && $shift
    ) {
        // 1) Insert into Employee table
        $stmt = $conn->prepare("
            INSERT INTO Employee (
                FirstName, MiddleName, LastName,
                BirthDate, Age, Status, Sex,
                Email, Phone, Barangay,
                StreetNBuildingHouseNo, City, Postal_zip_code,
                PositionID, MaxicareType, SalaryLoan_ind
            )
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->bind_param(
            "ssssisssssssisss",
            $firstName,
            $middleName,
            $lastName,
            $birthDate,
            $age,
            $status,
            $sex,
            $email,
            $phone,
            $barangay,
            $streetNumber,
            $city,
            $postalZipCode,
            $positionID,
            $maxicaretype,
            $salaryLoanInd
        );

        if ($stmt->execute()) {
            $employeeID = $conn->insert_id;  // newly inserted EmployeeID
            $stmt->close();

            // 2) Insert into Schedule table
            $stmtSchedule = $conn->prepare("
                INSERT INTO schedule (TimeIn, TimeOut, Shift)
                VALUES (?, ?, ?)
            ");
            $stmtSchedule->bind_param("sss", $timeIn, $timeOut, $shift);

            if ($stmtSchedule->execute()) {
                $scheduleID = $conn->insert_id;  // newly inserted ScheduleID
                $stmtSchedule->close();

                // *** Password Hashing ***
                // Hash the password using PHP's built-in password_hash() function
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                // 3) Insert into User table (with date_added set to NOW())
                $stmtUser = $conn->prepare("
                    INSERT INTO User (
                        Username, PasswordHash, Role,
                        EmployeeID, ScheduleID, date_added
                    )
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmtUser->bind_param("sssii", $username, $passwordHash, $role, $employeeID, $scheduleID);

                if ($stmtUser->execute()) {
                    $stmtUser->close();

                    // 4) Insert initial leave balances
                    $leaveTypes = [
                        'Sick Leave'      => 10,
                        'Vacation Leave'  => 10,
                        'Emergency Leave' => 10,
                        'Maternity Leave' => ($sex === 'Female' ? 105 : 0),
                        'Paternity Leave' => ($sex === 'Male'   ? 7   : 0)
                    ];

                    foreach ($leaveTypes as $leaveType => $daysAvailable) {
                        if ($daysAvailable > 0) {
                            $stmtLeave = $conn->prepare("
                                INSERT INTO LeaveBalance (EmployeeID, LeaveType, DaysAvailable)
                                VALUES (?, ?, ?)
                            ");
                            $stmtLeave->bind_param("isi", $employeeID, $leaveType, $daysAvailable);
                            $stmtLeave->execute();
                            $stmtLeave->close();
                        }
                    }

                    echo "Employee registered successfully with appropriate leave balances!";
                } else {
                    echo "Error inserting into User table: " . $stmtUser->error;
                    $stmtUser->close();
                }
            } else {
                echo "Error inserting into Schedule table: " . $stmtSchedule->error;
                $stmtSchedule->close();
            }
        } else {
            echo "Error inserting into Employee table: " . $stmt->error;
            $stmt->close();
        }

    } else {
        echo "Please fill in all required fields.";
    }
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
