<?php
// getDataTableData.php
header('Content-Type: application/json');

// Include the database connection
require_once __DIR__ . '/db.php';

/*
  We will return data for a table with headings:
  [ "Name", "Position", "Office", "Age", "Start date", "Salary" ]

  Assumptions:
  1. 'Name' is the concatenation of FirstName, MiddleName, and LastName.
  2. 'Position' comes from a joined 'position' table with a field like 'PositionName'.
  3. 'Office' will be the employee's 'City'.
  4. 'Age' is from the 'employee' table's 'Age' field.
  5. 'Start date' will be the 'DateAdded' field (or any date field you consider a "start date").
  6. 'Salary' is assumed to come from the 'position' table (e.g., 'BaseSalary'). 
     If your salary is stored elsewhere, adjust accordingly.
*/

// Prepare the response array
$response = array(
    "headings" => ["Name", "Position", "Office", "Age", "Start date", "Salary"],
    "data" => array()
);

// Example SQL query (adjust to match your actual column and table names)
$query = "
    SELECT 
        CONCAT(e.FirstName, ' ', IFNULL(e.MiddleName, ''), ' ', e.LastName) AS Name,
        p.PositionName AS Position,
        e.City AS Office,
        e.Age,
        e.DateAdded AS StartDate,
        p.SalaryPosition AS Salary
    FROM employee e
    LEFT JOIN position p ON e.PositionID = p.PositionID
";

// Execute the query
$result = $conn->query($query);

// Check results and build the data array
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response["data"][] = [
            $row["Name"],
            $row["Position"],
            $row["Office"],
            $row["Age"],
            $row["StartDate"],
            $row["Salary"]
        ];
    }
} else {
    // No rows found or error
    $response["data"] = [];
}

// Close the database connection
$conn->close();

// Return the response in JSON format
echo json_encode($response);
