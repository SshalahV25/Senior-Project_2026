<?php
header("Content-Type: application/json");
include "../php/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$doctor_id = $data['doctor_id'] ?? null;

if(!$doctor_id){
    echo json_encode(["error" => "No doctor_id"]);
    exit;
}

$sql = "SELECT Work_Day FROM work_days WHERE Doctor_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();

$result = $stmt->get_result();

$days = [];

while($row = $result->fetch_assoc()){
    $days[] = $row;
}

echo json_encode($days);
?>