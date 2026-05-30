<?php

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

$newPassword =
    trim($data['new_password'] ?? '');

// =========================
// 🔥 VALIDATION
// =========================
if(
    empty($email)
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
// 🔥 HASH PASSWORD
// =========================
$hashedPassword =
    password_hash(
        $newPassword,
        PASSWORD_DEFAULT
    );

// =========================
// 🔥 UPDATE PASSWORD
// =========================
$stmt = $conn->prepare("
    UPDATE patient

    SET
        Password=?,
        reset_code=NULL,
        reset_expires=NULL

    WHERE Email_Address=?
");

$stmt->bind_param(
    "ss",
    $hashedPassword,
    $email
);

// =========================
// 🔥 EXECUTE
// =========================
if($stmt->execute()){

    echo json_encode([

        "status"=>"success",

        "message"=>"Password updated successfully"
    ]);

}else{

    echo json_encode([

        "status"=>"error",

        "message"=>"Failed to update password"
    ]);
}

?>