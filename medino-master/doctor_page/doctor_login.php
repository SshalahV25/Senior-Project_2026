<?php
header("Content-Type: application/json");

include("../php/db.php");

// قراءة البيانات
$data = json_decode(file_get_contents("php://input"), true);

$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';

if(!$phone || !$password){
    echo json_encode([
        "status" => "error",
        "message" => "Missing data"
    ]);
    exit;
}

// البحث في الداتا بيز
$sql = "SELECT * FROM doctor WHERE Phone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $phone);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows > 0){

    $doctor = $result->fetch_assoc();

    // ⚠️ لو انت مش عامل hash
    // - VERIFY HASH PASSWORD
    if(password_verify($password, $doctor['Password'])){

        echo json_encode([
            "status" => "success",

            "user" => [

                "id" =>
                    $doctor['Doctor_ID'],

                "name" =>
                    $doctor['Name'],

                "phone" =>
                    $doctor['Phone'],

                "email" =>
                    $doctor['Email_Address']
            ]
        ]);

    }else{

        echo json_encode([

            "status" => "error",

            "message" => "Wrong password"
        ]);
    }

}else{
    echo json_encode([
        "status" => "error",
        "message" => "Doctor not found"
    ]);
}
?>