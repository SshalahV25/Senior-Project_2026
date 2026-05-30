<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

include "db.php";

$specialty_id = $_GET['specialty_id'];

// 🔥 يرجع بس الدكاترة بتوع التخصص
$sql = "SELECT Doctor_ID, Name FROM DOCTOR 
        WHERE Specialty_ID = $specialty_id";

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
?>