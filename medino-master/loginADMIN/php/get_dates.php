<?php
include "db.php";

$doctor_id = $_GET['doctor_id'];

// 🔥 بنجيب المواعيد من جدول APPOINTMENT
$sql = "SELECT Appointment_Date FROM APPOINTMENT 
        WHERE Doctor_ID = $doctor_id";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
?>