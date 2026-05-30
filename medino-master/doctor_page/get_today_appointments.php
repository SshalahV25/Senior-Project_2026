<?php

header("Content-Type: application/json");

include "../php/db.php";

// =========================
// 🟢 READ DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$doctor_id =
    $data['doctor_id'] ?? null;

// =========================
// 🟢 VALIDATION
// =========================
if(!$doctor_id){

    echo json_encode([
        "status"=>"error",
        "message"=>"Missing doctor id"
    ]);

    exit;
}

// =========================
// 🟢 GET ALL CONFIRMED APPOINTMENTS
// =========================
$sql = "

SELECT 
    booking.Booking_ID,
    booking.Booking_Date,
    booking.Status,
    patient.Name AS Patient_Name

FROM booking

JOIN patient 
ON booking.Patient_ID =
   patient.Patient_ID

WHERE booking.Doctor_ID = ?

AND booking.Status = 'Confirmed'

AND DAYNAME(
    booking.Booking_Date
) = ?

ORDER BY booking.Booking_Date ASC

";

// =========================
// 🟢 PREPARE
// =========================
$selected_day =
    $data['day'] ?? date("l");

$stmt =
    $conn->prepare($sql);

$stmt->bind_param(
    "is",
    $doctor_id,
    $selected_day
);

$stmt->execute();

$result =
    $stmt->get_result();

// =========================
// 🟢 FETCH DATA
// =========================
$appointments = [];

while(
    $row =
    $result->fetch_assoc()
){

    $appointments[] =
        $row;
}

// =========================
// ✅ RESPONSE
// =========================
echo json_encode([

    "status"=>"success",

    "appointments"=>$appointments

]);

?>