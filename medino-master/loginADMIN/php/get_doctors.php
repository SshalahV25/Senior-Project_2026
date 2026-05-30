<?php

include "db.php";

$sql = "

SELECT 
    d.Doctor_ID,
    d.Name,
    d.Email_Address,
    d.Phone,
    d.Gender,
    d.Specialty_ID,
    d.Date_Of_Birth,

    s.Name AS specialty_name

FROM doctor d

LEFT JOIN specialty s
ON d.Specialty_ID = s.Specialty_ID

ORDER BY d.Doctor_ID DESC

";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()){

    $data[] = $row;
}

echo json_encode($data);

?>