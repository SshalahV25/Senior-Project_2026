<?php

header("Content-Type: application/json");

include "../php/db.php";

// =========================
// - GET DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$doctor_id =
    $data['doctor_id'] ?? null;

$old_password =
    trim($data['old_password'] ?? '');

$new_password =
    trim($data['new_password'] ?? '');


// =========================
// - VALIDATION
// =========================
if(
    !$doctor_id
    ||
    empty($old_password)
    ||
    empty($new_password)
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Fill all fields"
    ]);

    exit;
}


// =========================
// - GET CURRENT PASSWORD
// =========================
$stmt = $conn->prepare("
    SELECT Password

    FROM doctor

    WHERE Doctor_ID=?
");

$stmt->bind_param(
    "i",
    $doctor_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$doctor =
    $result->fetch_assoc();

if(!$doctor){

    echo json_encode([

        "status"=>"error",

        "message"=>"Doctor not found"
    ]);

    exit;
}


// =========================
// - CHECK OLD PASSWORD
// =========================
if(
    !password_verify(
        $old_password,
        $doctor['Password']
    )
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Old password is incorrect"
    ]);

    exit;
}

$hashedPassword =
    password_hash(
        $new_password,
        PASSWORD_DEFAULT
    );

// =========================
// - UPDATE PASSWORD
// =========================
$update = $conn->prepare("
    UPDATE doctor

    SET Password=?

    WHERE Doctor_ID=?
");

$update->bind_param(
    "si",
    $hashedPassword,
    $doctor_id
);


// =========================
// - EXECUTE
// =========================
if($update->execute()){

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