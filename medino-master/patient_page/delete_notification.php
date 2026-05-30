<?php
header("Content-Type: application/json");
include "../php/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$patient_id = $data['patient_id'] ?? null;

if(!$id || !$patient_id){
    echo json_encode([
        "status"=>"error",
        "message"=>"Missing data"
    ]);
    exit;
}

// 🔥 أمان: يحذف بس إشعارات المريض ده
$sql = "DELETE FROM notifications WHERE id=? AND patient_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $patient_id);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
}else{
    echo json_encode(["status"=>"error"]);
}
?>