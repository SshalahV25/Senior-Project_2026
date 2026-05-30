<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

include "db.php";

$sql = "

SELECT

    b.Booking_ID,
    b.Patient_ID,
    b.Doctor_ID,
    b.Status,
    b.Notes,
    b.Booking_Date,

    p.Name AS Patient_Name,

    d.Name AS Doctor_Name,

    s.Specialty_ID,
    s.Name AS Specialty_Name

FROM booking b

JOIN patient p
ON b.Patient_ID = p.Patient_ID

JOIN doctor d
ON b.Doctor_ID = d.Doctor_ID

JOIN specialty s
ON d.Specialty_ID = s.Specialty_ID

ORDER BY b.Booking_ID DESC

";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()){

    $data[] = $row;
}

echo json_encode($data);

?>