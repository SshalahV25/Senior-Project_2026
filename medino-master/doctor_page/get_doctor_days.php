<?php

header("Content-Type: application/json");

include "../php/db.php";

// =========================
// GET DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$doctor_id =
    $data['doctor_id'] ?? null;

if(!$doctor_id){

    echo json_encode([
        "status"=>"error"
    ]);

    exit;
}

// =========================
// GET DAYS
// =========================
$sql = "

SELECT DISTINCT
    Work_Day

FROM work_days

WHERE Doctor_ID = ?

ORDER BY FIELD(
    Work_Day,
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday'
)

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $doctor_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$days = [];

while($row = $result->fetch_assoc()){

    $days[] =
        $row['Work_Day'];
}

echo json_encode([

    "status"=>"success",

    "days"=>$days
]);
?>