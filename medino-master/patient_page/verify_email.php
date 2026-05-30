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

$email = trim($data['email'] ?? '');
$code  = trim($data['code'] ?? '');

// =========================
// 🔥 VALIDATION
// =========================
if(empty($email) || empty($code)){

    echo json_encode([
        "status"=>"error",
        "message"=>"Missing data"
    ]);

    exit;
}

// =========================
// 🔥 CHECK OTP IN PENDING
// =========================
$stmt = $conn->prepare("
    SELECT *
    FROM pending_patients
    WHERE Email_Address=?
    AND verification_code=?
    AND otp_expiry > NOW()
");

$stmt->bind_param(
    "ss",
    $email,
    $code
);

$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows === 0){

    echo json_encode([
        "status"=>"error",
        "message"=>"Invalid or expired code"
    ]);

    exit;
}

$user = $result->fetch_assoc();

// =========================
// 🔥 INSERT INTO PATIENT
// =========================
$insert = $conn->prepare("
    INSERT INTO patient
    (
        Name,
        Email_Address,
        Phone,
        Data_of_birth,
        Gender,
        Password,
        verification_code,
        is_verified
    )
    VALUES
    (?, ?, ?, ?, ?, ?, NULL, 1)
");

$insert->bind_param(
    "ssssss",
    $user['Name'],
    $user['Email_Address'],
    $user['Phone'],
    $user['Data_of_birth'],
    $user['Gender'],
    $user['Password']
);

if(!$insert->execute()){

    echo json_encode([
        "status"=>"error",
        "message"=>"Failed to create account"
    ]);

    exit;
}

// =========================
// 🔥 DELETE FROM PENDING
// =========================
$delete = $conn->prepare("
    DELETE FROM pending_patients
    WHERE Email_Address=?
");

$delete->bind_param(
    "s",
    $email
);

$delete->execute();

// =========================
// 🔥 SUCCESS
// =========================
echo json_encode([
    "status"=>"success",
    "message"=>"Email verified successfully"
]);

?>