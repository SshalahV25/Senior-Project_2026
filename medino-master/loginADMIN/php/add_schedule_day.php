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
$end       = $data['end'] ?? null;

// =========================
// 🔴 VALIDATION
// =========================
if(
    !$doctor_id
    ||
    !$day
    ||
    !$start
    ||
    !$end
){

    echo json_encode([
        "status"=>"error",
        "message"=>"Missing data"
    ]);

    exit;
}

// =========================
// 🟢 INSERT
// =========================
$sql = "

INSERT INTO work_days
(
    Doctor_ID,
    Work_Day,
    Start_Time,
    End_Time
)

VALUES
(
    ?,
    ?,
    ?,
    ?
)

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "isss",
    $doctor_id,
    $day,
    $start,
    $end
);

if($stmt->execute()){

    echo json_encode([
        "status"=>"success"
    ]);

}else{

    echo json_encode([
        "status"=>"error",
        "message"=>"Insert failed"
    ]);
}
?>