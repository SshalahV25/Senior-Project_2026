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

$newPassword =
    trim($data['new_password'] ?? '');

// =========================
// 🔥 VALIDATION
// =========================
if(
    empty($email)
    ||
    empty($code)
    ||
    empty($newPassword)
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Missing data"
    ]);

    exit;
}

// =========================
// 🔥 GET USER
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

        "message"=>"User not found"
    ]);

    exit;
}

$user =
    $result->fetch_assoc();

// =========================
// 🔥 CHECK CODE
// =========================
if(
    trim($user['reset_code']) !== trim($code)
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Invalid code"
    ]);

    exit;
}

// =========================
// 🔥 CHECK EXPIRE
// =========================
$currentTime = time();

$expireTime =
    strtotime(
        $user['reset_expires']
    );

if($currentTime > $expireTime){

    echo json_encode([

        "status"=>"error",

        "message"=>"Code expired"
    ]);

    exit;
}

// =========================
// 🔥 HASH PASSWORD
// =========================
$hashed =
    password_hash(
        $newPassword,
        PASSWORD_DEFAULT
    );

// =========================
// 🔥 UPDATE PASSWORD
// =========================
$update = $conn->prepare("
    UPDATE patient

    SET
        Password=?,
        reset_code=NULL,
        reset_expires=NULL

    WHERE Email_Address=?
");

$update->bind_param(
    "ss",
    $hashed,
    $email
);

$update->execute();

// =========================
// 🔥 SUCCESS
// =========================
echo json_encode([

    "status"=>"success",

    "message"=>"Password updated successfully"
]);

?>