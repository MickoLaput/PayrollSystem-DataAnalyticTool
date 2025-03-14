<?php
session_start();
require_once __DIR__ . '/db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeID = $_SESSION['employee_id']; // Assuming employee ID is stored in session
    $leaveType = $_POST['leaveType'];
    $leaveStartDate = $_POST['leaveStartDate'];
    $leaveEndDate = $_POST['leaveEndDate'];
    $reason = $_POST['reason']; // Capture the reason for leave

    // Check if a file was uploaded
    if (isset($_FILES['fileAttachment']) && $_FILES['fileAttachment']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['fileAttachment']['tmp_name'];
        $fileName = $_FILES['fileAttachment']['name'];
        $fileSize = $_FILES['fileAttachment']['size'];
        $fileType = $_FILES['fileAttachment']['type'];

        // Validate file type and size
        $allowedFileTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($fileType, $allowedFileTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF, DOC, DOCX, JPEG, and PNG files are allowed.']);
            exit;
        }

        if ($fileSize > $maxFileSize) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds the limit of 5MB.']);
            exit;
        }

        // Read the file content
        $fileContent = file_get_contents($fileTmpPath);
    } else {
        $fileContent = null; // No file uploaded
    }

    // Prepare SQL to insert the leave request with the reason and optional file attachment
    $stmt = $conn->prepare("INSERT INTO LeaveFile (EmployeeID, LeaveType, LeaveStartDate, LeaveEndDate, Reason, Attachment, indicator) 
                            VALUES (?, ?, ?, ?, ?, ?, 'PENDING')");
    $stmt->bind_param("issssb", $employeeID, $leaveType, $leaveStartDate, $leaveEndDate, $reason, $fileContent);

    // Send file content as a BLOB
    $stmt->send_long_data(5, $fileContent);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Leave request submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error submitting leave request.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
