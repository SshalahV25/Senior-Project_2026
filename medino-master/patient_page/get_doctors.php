<?php
include "../php/db.php";

$data = json_decode(file_get_contents("php://input"), true);

// 🔥 حل المشكلة هنا
if(!$data || !isset($data['specialty_id'])){
    echo json_encode(["error"=>"No specialty_id"]);
    exit;
}

$specialty_id = $data['specialty_id'];

$sql = "SELECT * FROM doctor WHERE Specialty_ID = '$specialty_id'";
$result = mysqli_query($conn, $sql);

$doctors = [];

while($row = mysqli_fetch_assoc($result)){
    $doctors[] = $row;
}

echo json_encode($doctors);
?>