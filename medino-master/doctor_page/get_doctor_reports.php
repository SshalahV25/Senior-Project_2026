<?php

header("Content-Type: application/json");

include "../php/db.php";

// =========================
// 🟢 READ INPUT
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$doctor_id = $data['doctor_id'] ?? null;

$from = $data['from'] ?? null;

$to = $data['to'] ?? null;

if(!$doctor_id){

    echo json_encode([
        "status"=>"error"
    ]);

    exit;
}

// =========================
// 🟢 SQL
// =========================
$sql = "

SELECT

    booking.Booking_ID,

    booking.Booking_Date,

    booking.Status,

    patient.Name AS patient_name

FROM booking

INNER JOIN patient
ON patient.Patient_ID = booking.Patient_ID

WHERE booking.Doctor_ID = ?

";

// =========================
// 🔥 DATE FILTER
// =========================
$params = [$doctor_id];

$types = "i";

if($from && $to){

    $sql .= "
        AND DATE(booking.Booking_Date)
        BETWEEN ? AND ?
    ";

    $params[] = $from;
    $params[] = $to;

    $types .= "ss";
}

// =========================
// 🔥 ORDER
// =========================
$sql .= "
    ORDER BY booking.Booking_Date DESC
";

// =========================
// 🟢 PREPARE
// =========================
$stmt = $conn->prepare($sql);

$stmt->bind_param(
    $types,
    ...$params
);

$stmt->execute();

$result = $stmt->get_result();

$rows = [];

while($row = $result->fetch_assoc()){

    $rows[] = $row;
}

// =========================
// 🔥 STATS
// =========================
$total = count($rows);

$completed = 0;

$canceled = 0;

foreach($rows as $r){

    if($r['Status'] === "Completed"){

        $completed++;
    }

    if($r['Status'] === "Canceled"){

        $canceled++;
    }
}

// =========================
// 🟢 RESPONSE
// =========================
echo json_encode([

    "status"=>"success",

    "total"=>$total,

    "completed"=>$completed,

    "canceled"=>$canceled,

    "bookings"=>$rows
]);

?>