<?php
include "db.php";

$result = $conn->query("SELECT * FROM SPECIALTY");

$data = [];

while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);
?>