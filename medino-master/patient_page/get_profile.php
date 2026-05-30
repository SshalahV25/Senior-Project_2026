<?php
header("Content-Type: application/json");
include "../php/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$patient_id = $data['patient_id'] ?? null;

if(!$patient_id){
    echo json_encode(["status"=>"error"]);
    exit;
}

// 🔥 عملنا alias عشان نوحد الاسماء
$sql = "SELECT 
            Name AS name,
            Email_Address AS email,
            Phone AS phone,
            Gender AS gender
        FROM patient 
        WHERE Patient_ID=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode($row);
?>