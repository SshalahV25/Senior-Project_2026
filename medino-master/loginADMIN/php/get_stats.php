<?php
include "db.php";

$data = [];

$data['bookings'] = $conn->query("SELECT COUNT(*) c FROM BOOKING")->fetch_assoc()['c'];
$data['patients'] = $conn->query("SELECT COUNT(*) c FROM PATIENT")->fetch_assoc()['c'];
$data['doctors'] = $conn->query("SELECT COUNT(*) c FROM DOCTOR")->fetch_assoc()['c'];

$data['today'] = $conn->query("
SELECT COUNT(*) c FROM APPOINTMENT 
WHERE DATE(Appointment_Date) = CURDATE()
")->fetch_assoc()['c'];

echo json_encode($data);
?>