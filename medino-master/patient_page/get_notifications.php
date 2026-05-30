<?php
header("Content-Type: application/json");
include "../php/db.php";

$data = json_decode(file_get_contents("php://input"), true);
$patient_id = $data['patient_id'] ?? null;

if(!$patient_id){
    echo json_encode([]);
    exit;
}

$sql = "SELECT * FROM notifications 
        WHERE patient_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();

$result = $stmt->get_result();

$output = [];

while($row = $result->fetch_assoc()){
    $output[] = $row;
}

echo json_encode($output);