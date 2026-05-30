<?php

header("Content-Type: application/json");

include "db.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$doctor_id =
    $data['doctor_id'] ?? '';

$date =
    $data['date'] ?? '';

if(
    empty($doctor_id)
    ||
    empty($date)
){

    echo json_encode([]);

    exit;
}

/********************************
 * 🟢 اليوم الحالي
 ********************************/
$dayName =
    date(
        'l',
        strtotime($date)
    );

/********************************
 * 🟢 مواعيد الدكتور
 ********************************/
$sql = "

SELECT *

FROM work_days

WHERE Doctor_ID = '$doctor_id'

AND Work_Day = '$dayName'

";

$result =
    $conn->query($sql);

if($result->num_rows == 0){

    echo json_encode([]);

    exit;
}

$row =
    $result->fetch_assoc();

$start =
    $row['Start_Time'];

$end =
    $row['End_Time'];

/********************************
 * 🟢 Generate Slots
 ********************************/
$times = [];

$current =
    strtotime($start);

$finish =
    strtotime($end);

while($current < $finish){

    $times[] =
        date(
            "H:i",
            $current
        );

    $current =
        strtotime(
            "+15 minutes",
            $current
        );
}

/********************************
 * 🟢 الحجوزات المحجوزة
 * Confirmed + Completed
 ********************************/
$booked = [];

$bookingSql = "

SELECT Booking_Date

FROM booking

WHERE Doctor_ID = '$doctor_id'

AND DATE(Booking_Date) = '$date'

AND (
    Status = 'Confirmed'
    OR
    Status = 'Completed'
)

";

$bookingResult =
    $conn->query($bookingSql);

while(
    $b =
    $bookingResult->fetch_assoc()
){

    $booked[] =
        date(
            "H:i",
            strtotime(
                $b['Booking_Date']
            )
        );
}

/********************************
 * 🟢 إزالة المحجوز
 ********************************/
$available = [];

foreach($times as $t){

    if(
        !in_array(
            $t,
            $booked
        )
    ){

        $available[] = $t;
    }
}

/********************************
 * 🟢 لو اليوم خلص
 ********************************/
if(count($available) == 0){

    echo json_encode([

        "next_week" => true

    ]);

    exit;
}

/********************************
 * 🟢 Return
 ********************************/
echo json_encode($available);

?>