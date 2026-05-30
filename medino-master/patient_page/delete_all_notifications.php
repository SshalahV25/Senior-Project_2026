<?php
header("Content-Type: application/json");
include "../php/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$type = $data['type'] ?? null;
$patient_id = $data['patient_id'] ?? null;

if(!$type || !$patient_id){
    echo json_encode([
        "status"=>"error",
        "message"=>"Missing data"
    ]);
    exit;
}

// 🔥 system أو doctor
$sql = "DELETE FROM notifications WHERE source=? AND patient_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $type, $patient_id);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
}else{
    echo json_encode(["status"=>"error"]);
}
?>