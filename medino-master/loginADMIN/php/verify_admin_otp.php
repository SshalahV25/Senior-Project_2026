<?php

header("Content-Type: application/json");

include("db.php");

// =========================
// 🔥 GET DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$admin_id =
    $data['admin_id'] ?? null;

$otp =
    trim($data['otp'] ?? '');

// =========================
// 🔴 VALIDATION
// =========================
if(
    empty($admin_id)
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
// 🔥 GET ADMIN
// =========================
$stmt = $conn->prepare("

SELECT *

FROM admin

WHERE Admin_ID = ?

LIMIT 1

");

$stmt->bind_param(
    "i",
    $admin_id
);

$stmt->execute();

$result =
    $stmt->get_result();

if($result->num_rows === 0){

    echo json_encode([

        "status"=>"error",

        "message"=>"Admin not found"
    ]);

    exit;
}

$admin =
    $result->fetch_assoc();

// =========================
// 🔴 WRONG OTP
// =========================
if(
    $admin['otp_code'] != $otp
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
    strtotime($admin['otp_expire'])
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

UPDATE admin

SET
    otp_code = NULL,
    otp_expire = NULL

WHERE Admin_ID = ?

");

$clear->bind_param(
    "i",
    $admin_id
);

$clear->execute();

// =========================
// ✅ SUCCESS
// =========================
echo json_encode([

    "status"=>"success",

    "user"=>[

        "id"=>
            $admin['Admin_ID'],

        "username"=>
            $admin['User_Name'],

        "email"=>
            $admin['Email_Address']
    ]
]);
?>