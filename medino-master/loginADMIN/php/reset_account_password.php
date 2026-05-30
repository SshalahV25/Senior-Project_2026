<?php

include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? '';
$type = $data['type'] ?? '';
$password = $data['password'] ?? '';

if(!$id || !$type || !$password){

    echo json_encode([
        "status"=>"error"
    ]);

    exit;
}

// 🔥 HASH
$hashed = password_hash($password, PASSWORD_DEFAULT);

if($type == "patients"){

    $sql = "
    UPDATE patient
    SET Password = ?
    WHERE Patient_ID = ?
    ";

}else{

    $sql = "
    UPDATE doctor
    SET Password = ?
    WHERE Doctor_ID = ?
    ";
}

$stmt = $conn->prepare($sql);

$stmt->bind_param("si", $hashed, $id);

if($stmt->execute()){

    echo json_encode([
        "status"=>"success"
    ]);

}else{

    echo json_encode([
        "status"=>"error"
    ]);
}

?>