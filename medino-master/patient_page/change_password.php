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
$patient_id  = $data['patient_id'] ?? null;
$old_password = $data['old_password'] ?? null;
$new_password = $data['new_password'] ?? null;

if(!$patient_id || !$old_password || !$new_password){

    echo json_encode([
        "status"=>"error",
        "message"=>"Missing data"
    ]);
    exit;
}

// =========================
// 🟢 1. Get Current Password
// =========================
$sql = "SELECT Password FROM patient WHERE Patient_ID=?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $patient_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if(!$row){

    echo json_encode([
        "status"=>"error",
        "message"=>"User not found"
    ]);
    exit;
}

$hashedPassword = $row['Password'];

// =========================
// 🟢 2. Verify Old Password
// =========================
if(!password_verify($old_password, $hashedPassword)){

    echo json_encode([
        "status"=>"error",
        "message"=>"Old password is incorrect ❌"
    ]);
    exit;
}

// =========================
// ❌ منع نفس الباسورد القديم
// =========================
if(password_verify($new_password, $hashedPassword)){

    echo json_encode([
        "status"=>"error",
        "message"=>"New password cannot be the same as old password"
    ]);
    exit;
}

// =========================
// 🟢 3. Hash New Password
// =========================
$newHashed = password_hash($new_password, PASSWORD_DEFAULT);

// =========================
// 🟢 4. UPDATE Password
// =========================
$sql = "UPDATE patient SET Password=? WHERE Patient_ID=?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("si", $newHashed, $patient_id);

// =========================
// 🟢 5. Execute
// =========================
if($stmt->execute()){

    // =========================
    // 🔔 Notification
    // =========================
    $message = "🔐 Your password has been changed successfully";

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