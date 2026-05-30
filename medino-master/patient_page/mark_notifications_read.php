<?php

header("Content-Type: application/json");

include "../php/db.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$patient_id =
    $data['patient_id'] ?? null;

$source =
    $data['source'] ?? null;

if(
    !$patient_id
    ||
    !$source
){

    echo json_encode([
        "status"=>"error",
        "message"=>"Missing data"
    ]);

    exit;
}

/********************************
 * 🔥 SYSTEM + ADMIN
 ********************************/
if($source == "system"){

    $sql = "

    UPDATE notifications

    SET is_read = 1

    WHERE patient_id = ?

    AND
    (
        source = 'system'
        OR
        source = 'admin'
    )

    ";

    $stmt =
        $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $patient_id
    );

}

/********************************
 * 🔥 DOCTOR ONLY
 ********************************/
else{

    $sql = "

    UPDATE notifications

    SET is_read = 1

    WHERE patient_id = ?

    AND source = ?

    ";

    $stmt =
        $conn->prepare($sql);

    $stmt->bind_param(
        "is",
        $patient_id,
        $source
    );
}

/********************************
 * 🔥 EXECUTE
 ********************************/
if($stmt->execute()){

    echo json_encode([
        "status"=>"success"
    ]);

}else{

    echo json_encode([
        "status"=>"error"
    ]);
}

?>