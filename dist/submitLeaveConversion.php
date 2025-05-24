<?php
// submitLeaveConversion.php
session_start();
require_once __DIR__.'/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['employee_id'])) {
  echo json_encode(['success'=>false,'message'=>'Not logged in']);
  exit;
}

$emp = (int)$_SESSION['employee_id'];
$lt  = $_POST['leaveType']      ?? '';
$dr  = (int)$_POST['daysRequested'];
$rs  = $_POST['reason']         ?? '';

if (!$lt || $dr < 1 || !$rs) {
  echo json_encode(['success'=>false,'message'=>'Missing fields']);
  exit;
}

$sql = "
  INSERT INTO leaveconversion
    (EmployeeID, RequestDate, LeaveType, DaysRequested, Reason, Status)
  VALUES
    (?, NOW(), ?, ?, ?, 'PENDING')
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo json_encode(['success'=>false,'message'=>'Prepare failed: '.$conn->error]);
  exit;
}
// four placeholders → four PHP vars → four type chars: i (int), s (string), i (int), s (string)
$stmt->bind_param('isis', $emp, $lt, $dr, $rs);

try {
  $stmt->execute();
  echo json_encode(['success'=>true]);
} catch (mysqli_sql_exception $e) {
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

$stmt->close();
$conn->close();
