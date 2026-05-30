<?php
include "db.php";

$sql = "
SELECT 
d.Name AS Doctor_Name,
s.Name AS Specialty_Name,
COUNT(*) AS total

FROM BOOKING b
JOIN APPOINTMENT a ON b.Appointment_ID = a.Appointment_ID
JOIN DOCTOR d ON a.Doctor_ID = d.Doctor_ID
JOIN SPECIALTY s ON d.Specialty_ID = s.Specialty_ID

GROUP BY d.Doctor_ID
ORDER BY total DESC
";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
?>