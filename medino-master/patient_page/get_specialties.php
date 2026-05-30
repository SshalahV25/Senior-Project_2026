<?php
include "../php/db.php";

$sql = "SELECT * FROM specialty";
$result = mysqli_query($conn, $sql);

$data = [];

while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

echo json_encode($data);
?>