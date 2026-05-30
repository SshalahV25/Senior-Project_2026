<?php

header("Content-Type: application/json");

include "db.php";

// =========================
// 🟢 READ DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$doctor_id = $data['doctor_id'] ?? null;
$day       = $data['day'] ?? null;
$start     = $data['start'] ?? null;

// =========================
// 🔴 VALIDATION
// =========================
if(
    !$doctor_id
    ||
    !$day
    ||
    !$start
){

    echo json_encode([
        "status"=>"error"
    ]);

    exit;
}

// =========================
// 🟢 DELETE
// =========================
$sql = "

DELETE FROM work_days

WHERE Doctor_ID=?
AND Work_Day=?
AND Start_Time=?

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iss",
    $doctor_id,
    $day,
    $start
);

if($stmt->execute()){

    echo json_encode([
        "status"=>"success"
    ]);

}else{

    echo json_encode([
        "status"=>"error"
    ]);
}
?>