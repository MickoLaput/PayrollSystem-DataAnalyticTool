<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the SQL query to get the user details
    $stmt = $conn->prepare('SELECT * FROM User WHERE Username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $storedPassword = $user['PasswordHash'];

        // First, try to verify the password using password_verify()
        if (password_verify($password, $storedPassword)) {
            $loginValid = true;
        }
        // Otherwise, check if the stored password is plain text and matches the entered one
        else if ($storedPassword === $password) {
            $loginValid = true;
            // Update the password in the database to the hashed version
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE User SET PasswordHash = ? WHERE UserID = ?");
            // Assuming you have a unique identifier for the User, e.g. UserID
            $updateStmt->bind_param("si", $newHash, $user['UserID']);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            $loginValid = false;
        }
        
        if ($loginValid) {
            // Set session variables
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['employee_id'] = $user['EmployeeID']; // Store EmployeeID

            // Determine redirect URL based on Role
            $redirectUrl = ($user['Role'] === 'Admin') ? 'AdminPortal.html' : 'EmployeePortal.html';

            echo json_encode(['success' => true, 'redirect' => $redirectUrl]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid password.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    }
}
?>
