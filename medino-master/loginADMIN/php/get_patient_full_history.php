<?php

header("Content-Type: application/json");

include "db.php";

// =========================
// 🟢 READ INPUT
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$patient_id =
    $data['patient_id'] ?? null;

if(!$patient_id){

    echo json_encode([]);

    exit;
}

// =========================
// 🟢 GET BOOKINGS + PRESCRIPTIONS
// =========================
$sql = "

SELECT

    booking.Booking_ID,
    booking.Booking_Date,
    booking.Status,
    booking.Notes,

    doctor.Name AS doctor_name,

    prescriptions.notes AS prescription_notes,
    prescriptions.file,
    prescriptions.created_at

FROM booking

INNER JOIN doctor
ON doctor.Doctor_ID = booking.Doctor_ID

LEFT JOIN prescriptions
ON prescriptions.booking_id = booking.Booking_ID

WHERE booking.Patient_ID = ?

AND
(
    booking.Status = 'Completed'

    OR

    booking.Status = 'Canceled'
)

ORDER BY booking.Booking_Date DESC

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