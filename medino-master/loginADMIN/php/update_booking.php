<?php

include "db.php";

$booking_id   = $_POST['booking_id'] ?? '';
$patient_id   = $_POST['patient_id'] ?? '';
$doctor_id    = $_POST['doctor_id'] ?? '';
$booking_date = $_POST['booking_date'] ?? '';
$status       = $_POST['status'] ?? 'Confirmed';
$notes        = $_POST['notes'] ?? '';

/*************************
 VALIDATION
*************************/
if(
    empty($booking_id)
    ||
    empty($patient_id)
    ||
    empty($doctor_id)
    ||
    empty($booking_date)
){
    echo "missing";
    exit;
}

/*************************
 CHECK DUPLICATE
*************************/
$check = "
SELECT *
FROM booking
WHERE

Patient_ID = '$patient_id'

AND

Doctor_ID = '$doctor_id'

AND

Status = 'Confirmed'

AND

Booking_ID != '$booking_id'
";

$result =
    $conn->query($check);

if(
    $result->num_rows > 0
){

    echo "already_booked";

    exit;
}

/*************************
 UPDATE BOOKING
*************************/
$sql = "
UPDATE booking
SET

Patient_ID = '$patient_id',

Doctor_ID = '$doctor_id',

Booking_Date = '$booking_date',

Status = '$status',

Notes = '$notes'

WHERE Booking_ID = '$booking_id'
";

if(
    $conn->query($sql)
){

    echo "updated";

}else{

    echo "error";
}
?>