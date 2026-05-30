<?php

header("Content-Type: application/json");

include "db.php";

// =========================
// 🟢 GET ID
// =========================
$doctor_id = $_GET['doctor_id'] ?? null;

if(!$doctor_id){

    echo json_encode([]);

    exit;
}

// =========================
// 🟢 LOAD SCHEDULE
// =========================
$sql = "

SELECT

    d.Doctor_ID,

    d.Name AS Doctor_Name,

    w.Work_Day,

    w.Start_Time,

    w.End_Time

FROM doctor d

LEFT JOIN work_days w
ON d.Doctor_ID = w.Doctor_ID

WHERE d.Doctor_ID = '$doctor_id'

ORDER BY FIELD(
    w.Work_Day,
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday'
)

";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()){

    $data[] = $row;
}

echo json_encode($data);

?>