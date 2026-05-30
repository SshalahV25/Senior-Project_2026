<?php
header("Content-Type: application/json");
include "../php/db.php";

$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['patient_id'])){
    echo json_encode(["error"=>"Missing patient_id"]);
    exit;
}

$patient_id = $data['patient_id'];

$sql = "
SELECT 
    b.Booking_ID,
    b.Booking_Date,
    b.Status,
    d.Name as doctor_name,
    s.Name as specialty_name
FROM booking b
JOIN doctor d ON b.Doctor_ID = d.Doctor_ID
JOIN specialty s ON d.Specialty_ID = s.Specialty_ID
WHERE b.Patient_ID = ?
AND b.Status = 'Confirmed'
ORDER BY b.Booking_Date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();

$result = $stmt->get_result();

$appointments = [];

while($row = $result->fetch_assoc()){
    $appointments[] = $row;
}

echo json_encode($appointments);
?>