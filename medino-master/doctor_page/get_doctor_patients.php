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

$doctor_id = $data['doctor_id'] ?? null;

if(!$doctor_id){

    echo json_encode([]);

    exit;
}

// =========================
// 🟢 GET PATIENTS
// =========================
$sql = "

SELECT

    p.Patient_ID,

    p.Name,

    MAX(pr.created_at) AS last_visit,

    (
        SELECT notes
        FROM prescriptions pr2

        WHERE pr2.patient_id = p.Patient_ID
        AND pr2.doctor_id = ?

        ORDER BY pr2.created_at DESC

        LIMIT 1
    ) AS last_notes

FROM prescriptions pr

INNER JOIN patient p
ON p.Patient_ID = pr.patient_id

WHERE pr.doctor_id = ?

GROUP BY p.Patient_ID

ORDER BY last_visit DESC

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ii",
    $doctor_id,
    $doctor_id
);

$stmt->execute();

$result = $stmt->get_result();

$patients = [];

while($row = $result->fetch_assoc()){

    // =========================
    // - استخراج diagnosis فقط
    // =========================
    $diagnosis = "No Diagnosis";

    if(!empty($row['last_notes'])){

        preg_match(
            '/Diagnosis:\s*(.*?)\s*Prescription:/s',
            $row['last_notes'],
            $matches
        );

        if(isset($matches[1])){

            $diagnosis = trim($matches[1]);
        }
    }

    $patients[] = [

        "patient_id" => $row['Patient_ID'],

        "name" => $row['Name'],

        "last_visit" => $row['last_visit'],

        "diagnosis" => $diagnosis
    ];
}

echo json_encode($patients);

?>