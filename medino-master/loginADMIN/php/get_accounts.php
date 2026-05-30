<?php

header("Content-Type: application/json");

include "db.php";

$type = $_GET['type'] ?? '';

if($type == "patients"){

    $sql = "
    SELECT
    Patient_ID as id,
    Name as name,
    Email_Address as email,
    Phone as phone,
    Data_of_birth as birthdate,
    Gender as gender

    FROM patient
    ";

}else{

    $sql = "
    SELECT
    Doctor_ID as id,
    Name as name,
    Email_Address as email,
    Phone as phone,
    Gender as gender,
    Date_Of_Birth as birthdate

    FROM doctor
    ";
}

$result = $conn->query($sql);

$data = [];

while($row = $result->fetch_assoc()){

    $data[] = $row;
}

echo json_encode($data);

?>