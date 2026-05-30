<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

include "db.php";

$doctor_id = $_GET['doctor_id'];

$sql = "
SELECT 
    d.Doctor_ID,
    d.Name AS Doctor_Name,

    w.Work_Day,
    w.Start_Time,
    w.End_Time

FROM DOCTOR d
JOIN WORK_DAYS w 
ON d.Doctor_ID = w.Doctor_ID

WHERE d.Doctor_ID = $doctor_id
";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
?>