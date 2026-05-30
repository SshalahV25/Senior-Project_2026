<?php
include "db.php";

header('Content-Type: application/json');

$sql = "
SELECT 
    A.Appointment_ID,
    A.Doctor_ID,
    D.Name AS Doctor_Name,
    A.Appointment_Date,
    A.Start_Time,
    A.End_Time
FROM APPOINTMENT A
JOIN DOCTOR D ON A.Doctor_ID = D.Doctor_ID
ORDER BY A.Appointment_Date DESC
";

$result = $conn->query($sql);

if(!$result){
    echo json_encode([
        "status" => "error",
        "message" => $conn->error
    ]);
    exit;
}

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
?>