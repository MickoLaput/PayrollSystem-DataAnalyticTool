<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

// 1. Check if employee_id is stored in session (the login.php code stores $_SESSION['employee_id'])
if (!isset($_SESSION['employee_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No employee_id found in session. Please log in again.'
    ]);
    exit;
}

$employeeID = $_SESSION['employee_id'];

// 2. Retrieve the posted form data
$currentPassword = isset($_POST['currentPassword']) ? $_POST['currentPassword'] : '';
$newPassword     = isset($_POST['newPassword']) ? $_POST['newPassword'] : '';

// Basic validation: ensure both fields are filled
if (empty($currentPassword) || empty($newPassword)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields.'
    ]);
    exit;
}

// 3. First, find the user record via EmployeeID so we can get the UserID & stored password
$sql = "SELECT UserID, PasswordHash 
        FROM user 
        WHERE EmployeeID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $employeeID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows < 1) {
    // No user record found for this EmployeeID
    echo json_encode([
        'success' => false,
        'message' => 'User account not found for this employee.'
    ]);
    exit;
}

$row = $result->fetch_assoc();
$userID     = $row['UserID'];
$storedHash = $row['PasswordHash'];

// 4. Verify the current password
//    (It may be hashed or plain text. We'll attempt password_verify first.)
if (password_verify($currentPassword, $storedHash)) {
    // Current password is correct, continue
} elseif ($storedHash === $currentPassword) {
    // The stored password was still plain text, but matches
    // Optionally re-hash it now to remove plain text usage
    // but user wants to change anyway, so we’ll do the final update below
} else {
    // Current password is incorrect
    echo json_encode([
        'success' => false,
        'message' => 'Current password is incorrect.'
    ]);
    exit;
}

// 5. Hash the new password
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

// 6. Update the user’s password in the DB
$updateSql = "UPDATE user
              SET PasswordHash = ?
              WHERE UserID = ?";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param('si', $newHash, $userID);

if ($updateStmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update password.'
    ]);
}

$stmt->close();
$updateStmt->close();
$conn->close();
?>
