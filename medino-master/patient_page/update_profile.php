<?php
header("Content-Type: application/json");

include "../php/db.php";

// 🔥 إظهار الأخطاء مؤقتًا
ini_set('display_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents("php://input"), true);

// =========================
// 🟢 استقبال البيانات
// =========================
$patient_id = $data['patient_id'] ?? null;
$field      = $data['field'] ?? null;
$value      = $data['value'] ?? null;

if(!$patient_id || !$field){
    echo json_encode([
        "status"=>"error",
        "message"=>"Missing data"
    ]);
    exit;
}

// =========================
// 🟢 Mapping
// =========================
$map = [
    "name"   => "Name",
    "email"  => "Email_Address",
    "phone"  => "Phone",
    "gender" => "Gender"
];

if(!isset($map[$field])){
    echo json_encode([
        "status"=>"error",
        "message"=>"Invalid field"
    ]);
    exit;
}

$column = $map[$field];

// =========================
// 🟢 Get Old Value
// =========================
$getOld = $conn->prepare("
    SELECT $column
    FROM patient
    WHERE Patient_ID = ?
");

$getOld->bind_param("i", $patient_id);
$getOld->execute();

$oldResult = $getOld->get_result();
$oldRow = $oldResult->fetch_assoc();

$oldValue = $oldRow[$column] ?? "";

// =========================
// ❌ لو مفيش تغيير
// =========================
if($oldValue == $value){

    echo json_encode([
        "status"=>"no_change",
        "message"=>"No changes made"
    ]);
    exit;
}

// =========================
// 🟢 UPDATE
// =========================
$sql = "UPDATE patient SET $column=? WHERE Patient_ID=?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("si", $value, $patient_id);

// =========================
// 🟢 Execute
// =========================
if($stmt->execute()){

    // =========================
    // 🔔 أسماء محترمة للحقول
    // =========================
    $fieldNames = [
        "name"   => "name",
        "email"  => "email",
        "phone"  => "phone number",
        "gender" => "gender"
    ];

    $niceField = $fieldNames[$field] ?? $field;

    // =========================
    // 🔔 Notification Message
    // =========================
    $message = "👤 Your {$niceField} has been updated from '{$oldValue}' to '{$value}'";

    // =========================
    // 🔔 Insert Notification
    // =========================
    $notify = $conn->prepare("
        INSERT INTO notifications
        (patient_id, message, type, source)
        VALUES (?, ?, 'success', 'system')
    ");

    $notify->bind_param("is", $patient_id, $message);
    $notify->execute();

    // =========================
    // ✅ Success Response
    // =========================
    echo json_encode([
        "status"=>"success"
    ]);

}else{

    echo json_encode([
        "status"=>"error",
        "message"=>"Update failed"
    ]);
}
?>