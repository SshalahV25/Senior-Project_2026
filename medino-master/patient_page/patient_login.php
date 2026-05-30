<?php
header("Content-Type: application/json");

require_once("../php/db.php");

$data = json_decode(file_get_contents("php://input"), true);

$login = $data['login'] ?? '';
$password = $data['password'] ?? '';

if(empty($login) || empty($password)){
    echo json_encode(["status"=>"error","message"=>"Missing data"]);
    exit;
}

// البحث عن المستخدم
$stmt = $conn->prepare("
    SELECT * FROM patient 
    WHERE Email_Address = ? OR Phone = ?
");
$stmt->bind_param("ss", $login, $login);
$stmt->execute();

$result = $stmt->get_result();

// =========================
// CHECK PENDING ACCOUNT
// =========================
$pending = $conn->prepare("
    SELECT *
    FROM pending_patients
    WHERE Email_Address=? OR Phone=?
");

$pending->bind_param(
    "ss",
    $login,
    $login
);

$pending->execute();

$pendingResult = $pending->get_result();

if($pendingResult->num_rows > 0){

    $pendingUser =
        $pendingResult->fetch_assoc();

    // تأكد من الباسورد الأول
    if(
        password_verify(
            $password,
            $pendingUser['Password']
        )
    ){

        echo json_encode([
            "status"=>"pending_verification",
            "message"=>"Please verify your email first",
            "email"=>$pendingUser['Email_Address']
        ]);

    }else{

        echo json_encode([
            "status"=>"error",
            "message"=>"Wrong password"
        ]);
    }

    exit;
}

if($result->num_rows === 0){
    echo json_encode(["status"=>"error","message"=>"User not found"]);
    exit;
}

$user = $result->fetch_assoc();

if($user['is_verified'] != 1){

    echo json_encode([
        "status"=>"error",
        "message"=>"Please verify your email first"
    ]);

    exit;
}

// تحقق من الباسورد
if(password_verify($password, $user['Password'])){

    echo json_encode([
        "status"=>"success",
        "user"=>[
            "id"=>$user['Patient_ID'],
            "name"=>$user['Name'],
            "email"=>$user['Email_Address']
        ]
    ]);

}else{
    echo json_encode(["status"=>"error","message"=>"Wrong password"]);
}