<?php

header("Content-Type: application/json");

include("../php/db.php");

// =========================
// 🔥 GET DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$doctor_id =
    $data['doctor_id'] ?? null;

$otp =
    trim($data['otp'] ?? '');

// =========================
// 🔴 VALIDATION
// =========================
if(
    empty($doctor_id)
    ||
    empty($otp)
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Missing data"
    ]);

    exit;
}

// =========================
// 🔥 GET DOCTOR
// =========================
$sql = "

SELECT *

FROM doctor

WHERE Doctor_ID = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $doctor_id
);

$stmt->execute();

$result =
    $stmt->get_result();

if($result->num_rows === 0){

    echo json_encode([

        "status"=>"error",

        "message"=>"Doctor not found"
    ]);

    exit;
}

$doctor =
    $result->fetch_assoc();

// =========================
// 🔴 INVALID OTP
// =========================
if(
    $doctor['otp_code'] != $otp
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Invalid OTP"
    ]);

    exit;
}

// =========================
// 🔴 EXPIRED OTP
// =========================
if(
    strtotime($doctor['otp_expire'])
    <
    time()
){

    echo json_encode([

        "status"=>"error",

        "message"=>"OTP expired"
    ]);

    exit;
}

// =========================
// 🔥 CLEAR OTP
// =========================
$clear = $conn->prepare("

UPDATE doctor

SET
    otp_code = NULL,
    otp_expire = NULL

WHERE Doctor_ID = ?

");

$clear->bind_param(
    "i",
    $doctor_id
);

$clear->execute();

// =========================
// ✅ SUCCESS
// =========================
echo json_encode([

    "status"=>"success",

    "user"=>[

        "id"=>
            $doctor['Doctor_ID'],

        "name"=>
            $doctor['Name'],

        "phone"=>
            $doctor['Phone'],

        "email"=>
            $doctor['Email_Address']
    ]
]);

?>