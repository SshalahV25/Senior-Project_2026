<?php
include "db.php";

// مهم جدًا 🔥
header('Content-Type: application/json');

$sql = "
SELECT 
Patient_ID,
Name,
Email_Address,
Phone,
Data_of_birth,
TIMESTAMPDIFF(YEAR, Data_of_birth, CURDATE()) AS Age
FROM PATIENT
";

$result = $conn->query($sql);

// 🔥 لو في Error
if(!$result){
    echo json_encode([
        "status" => "error",
        "message" => $conn->error
    ]);
    exit;
}

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

// 🔥 حتى لو فاضي يرجع Array
echo json_encode($data);
?>