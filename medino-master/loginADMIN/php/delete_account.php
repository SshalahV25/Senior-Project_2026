<?php

include "db.php";

// =========================
// 🟢 READ DATA
// =========================
$id   = $_GET['id'] ?? '';
$type = $_GET['type'] ?? '';

// =========================
// 🟢 VALIDATION
// =========================
if(empty($id) || empty($type)){

    echo "missing";

    exit;
}

// =========================
// 🟢 DELETE PATIENT
// =========================
if($type == "patients"){

    $sql = "
    DELETE FROM patient
    WHERE Patient_ID = ?
    ";

}else{

    // =========================
    // 🟢 DELETE DOCTOR
    // =========================
    $sql = "
    DELETE FROM doctor
    WHERE Doctor_ID = ?
    ";
}

// =========================
// 🟢 PREPARE
// =========================
$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);

// =========================
// 🟢 EXECUTE
// =========================
if($stmt->execute()){

    echo "success";

}else{

    echo "error";
}
?>