<?php
/* for local db
$host = 'localhost';
$db = 'freedb_smartpayrollsystem';  // Your database name
$user = 'root';
$pass = ''; // Your password
*/

// for online db
$host = 'sql.freedb.tech';
$db = 'freedb_smartpayrollsystem';  // Your database name
$user = 'freedb_payrollsystem';
$pass = 'EK*3rfMDyEfUd2X'; // Your password

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
