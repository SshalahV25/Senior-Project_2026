<?php

header("Content-Type: application/json");

include "../php/db.php";

// =========================
// 🔥 SHOW ERRORS
// =========================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// =========================
// 🟢 READ JSON
// =========================
$raw = file_get_contents("php://input");

$data = json_decode($raw, true);

// =========================
// 🟢 CHECK BOOKING ID
// =========================
$booking_id = isset($data['booking_id'])
    ? intval($data['booking_id'])
    : 0;

if($booking_id <= 0){

    echo json_encode([
        "status"=>"error",
        "message"=>"Invalid booking id"
    ]);

    exit;
}

// =========================
// 🟢 QUERY
// =========================
$sql = "
SELECT 
    notes,
    file

FROM prescriptions

WHERE booking_id = ?

LIMIT 1
";

// =========================
// 🟢 PREPARE
// =========================
$stmt = $conn->prepare($sql);

if(!$stmt){

    echo json_encode([
        "status"=>"error",
        "message"=>$conn->error
    ]);

    exit;
}

// =========================
// 🟢 EXECUTE
// =========================
$stmt->bind_param("i", $booking_id);

$stmt->execute();

$result = $stmt->get_result();

if(!$result){

    echo json_encode([
        "status"=>"error",
        "message"=>$stmt->error
    ]);

    exit;
}

// =========================
// 🟢 FETCH
// =========================
$prescription = $result->fetch_assoc();

if(!$prescription){

    echo json_encode([
        "status"=>"error",
        "message"=>"Prescription not found"
    ]);

    exit;
}

// =========================
// ✅ RESPONSE
// =========================
echo json_encode([
    "status"=>"success",
    "prescription"=>$prescription
]);

?>