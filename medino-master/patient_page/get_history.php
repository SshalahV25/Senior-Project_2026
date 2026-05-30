<?php

include "../php/db.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$patient_id =
    $data['patient_id'] ?? null;

if(!$patient_id){

    echo json_encode([]);
    exit;
}

// =========================
// 🔥 LOAD HISTORY
// =========================
$sql = "

SELECT

    b.Booking_ID,
    b.Booking_Date,
    b.Status,

    d.Name AS doctor_name,

    s.Name AS specialty_name

FROM booking b

JOIN doctor d
ON b.Doctor_ID = d.Doctor_ID

JOIN specialty s
ON d.Specialty_ID = s.Specialty_ID

WHERE b.Patient_ID = ?

AND
(
    b.Status = 'Completed'
    OR
    b.Status = 'Canceled'
)

ORDER BY b.Booking_Date DESC

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $patient_id
);

$stmt->execute();

$result = $stmt->get_result();

$data = [];

while($row = $result->fetch_assoc()){

    $data[] = $row;
}

echo json_encode($data);

?>