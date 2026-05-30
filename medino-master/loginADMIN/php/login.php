<?php

header("Content-Type: application/json");

include "db.php";

// =========================
// GET DATA
// =========================
$username =
    trim($_POST['username'] ?? '');

$password =
    trim($_POST['password'] ?? '');

// =========================
// VALIDATION
// =========================
if(
    empty($username)
    ||
    empty($password)
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Missing data"
    ]);

    exit;
}

// =========================
// GET ADMIN
// =========================
$stmt = $conn->prepare("

SELECT *

FROM admin

WHERE User_Name = ?

LIMIT 1

");

$stmt->bind_param(
    "s",
    $username
);

$stmt->execute();

$result =
    $stmt->get_result();

// =========================
// ADMIN NOT FOUND
// =========================
if($result->num_rows === 0){

    echo json_encode([

        "status"=>"error",

        "message"=>"Admin not found"
    ]);

    exit;
}

// =========================
// FETCH ADMIN
// =========================
$admin =
    $result->fetch_assoc();

// =========================
// VERIFY PASSWORD
// =========================
if(
    !password_verify(
        $password,
        $admin['Password']
    )
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Wrong password"
    ]);

    exit;
}

// =========================
// SUCCESS
// =========================
echo json_encode([

    "status"=>"otp_required",

    "admin_id"=>
        $admin['Admin_ID']
]);

?>