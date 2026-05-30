<?php

date_default_timezone_set(
    "Africa/Cairo"
);

header("Content-Type: application/json");

include "../php/db.php";

// =========================
// 🔥 GET DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$email =
    trim($data['email'] ?? '');

$code =
    trim($data['code'] ?? '');

// =========================
// 🔥 VALIDATION
// =========================
if(
    empty($email)
    ||
    empty($code)
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Missing data"
    ]);

    exit;
}

// =========================
// 🔥 CHECK OTP
// =========================
$stmt = $conn->prepare("
    SELECT
        reset_code,
        reset_expires

    FROM patient

    WHERE Email_Address=?
");

$stmt->bind_param(
    "s",
    $email
);

$stmt->execute();

$result =
    $stmt->get_result();

if($result->num_rows === 0){

    echo json_encode([

        "status"=>"error",

        "message"=>"Email not found"
    ]);

    exit;
}

$user =
    $result->fetch_assoc();

// =========================
// 🔥 CHECK CODE
// =========================
if(
    $user['reset_code']
    !=
    $code
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Invalid OTP"
    ]);

    exit;
}

// =========================
// 🔥 CHECK EXPIRE
// =========================
if(
    strtotime(
        $user['reset_expires']
    )
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
// 🔥 SUCCESS
// =========================
echo json_encode([

    "status"=>"success"
]);

?>