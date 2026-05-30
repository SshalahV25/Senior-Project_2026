<?php

header("Content-Type: application/json");

include "../php/db.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

// =========================
// 🟢 1. CHECK DATA
// =========================
$booking_id =
    $data['booking_id'] ?? null;

if(!$booking_id){

    echo json_encode([
        "status"=>"error",
        "message"=>"Missing booking_id"
    ]);

    exit;
}

// =========================
// 🟢 2. GET PATIENT ID
// =========================
$sql = "
SELECT Patient_ID
FROM booking
WHERE Booking_ID=?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $booking_id
);

$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

if(!$row){

    echo json_encode([
        "status"=>"error",
        "message"=>"Booking not found"
    ]);

    exit;
}

$patient_id =
    $row['Patient_ID'];

// =========================
// 🟢 3. UPDATE STATUS
// =========================
$sql = "
UPDATE booking
SET Status='Canceled'
WHERE Booking_ID=?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $booking_id
);

// =========================
// 🟢 4. EXECUTE
// =========================
if($stmt->execute()){

    // =========================
    // 🟢 NOTIFICATION
    // =========================
    $msg =
        "Appointment cancelled successfully";

    $sql2 = "
    INSERT INTO notifications
    (
        patient_id,
        message,
        type,
        source
    )
    VALUES
    (
        ?,
        ?,
        'error',
        'system'
    )
    ";

    $stmt2 =
        $conn->prepare($sql2);

    $stmt2->bind_param(
        "is",
        $patient_id,
        $msg
    );

    $stmt2->execute();

    echo json_encode([
        "status"=>"success"
    ]);

}else{

    echo json_encode([
        "status"=>"error",
        "message"=>"Cancel failed"
    ]);
}

?>