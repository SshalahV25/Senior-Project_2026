<?php

header("Content-Type: application/json");

include "../php/db.php";

// =========================
// 🟢 READ INPUT
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$patient_id = $data['patient_id'] ?? null;

if(!$patient_id){

    echo json_encode([]);

    exit;
}

// =========================
// 🟢 GET HISTORY
// =========================
$sql = "

SELECT

    prescriptions.notes,

    prescriptions.file,

    prescriptions.created_at,

    doctor.Name AS doctor_name

FROM prescriptions

INNER JOIN doctor
ON doctor.Doctor_ID = prescriptions.doctor_id

WHERE prescriptions.patient_id = ?

ORDER BY prescriptions.created_at DESC

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $patient_id
);

$stmt->execute();

$result = $stmt->get_result();

$history = [];

while($row = $result->fetch_assoc()){

    $history[] = $row;
}

echo json_encode($history);

?>