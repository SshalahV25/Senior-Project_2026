<?php
header("Content-Type: application/json");

// 🧹 اخفاء الأخطاء من الـ output (رجعناه للوضع الطبيعي)
ini_set('display_errors', 0);
error_reporting(0);

include "../php/db.php";

$data = json_decode(file_get_contents("php://input"), true);

// =========================
// 🟢 1. تحقق من البيانات
// =========================
if(!$data){
    echo json_encode([
        "status"=>"error",
        "message"=>"No data received"
    ]);
    exit;
}

$booking_id = $data['booking_id'] ?? null;

if(!$booking_id){
    echo json_encode([
        "status"=>"error",
        "message"=>"Missing booking_id"
    ]);
    exit;
}

// =========================
// 🟢 2. نجيب بيانات الحجز
// =========================
$sql = "SELECT Patient_ID, Status FROM booking WHERE Booking_ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if(!$row){
    echo json_encode([
        "status"=>"error",
        "message"=>"Booking not found"
    ]);
    exit;
}

$patient_id = $row['Patient_ID'];
$current_status = $row['Status'];

// =========================
// 🟢 3. منع التكرار
// =========================
if($current_status === "Completed"){
    echo json_encode([
        "status"=>"error",
        "message"=>"Already completed"
    ]);
    exit;
}

// =========================
// 🟢 4. تحديث الحالة
// =========================
$sql = "UPDATE booking SET Status='Completed' WHERE Booking_ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);

if(!$stmt->execute()){
    echo json_encode([
        "status"=>"error",
        "message"=>"Update failed"
    ]);
    exit;
}

// =========================
// 🟢 5. Notification (UPDATED)
// =========================
$msg = "✅ Your appointment has been completed";

$sql2 = "INSERT INTO notifications (patient_id, message, type, source)
         VALUES (?, ?, 'success', 'system')";

$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("is", $patient_id, $msg);

if(!$stmt2->execute()){
    echo json_encode([
        "status"=>"error",
        "message"=>"Notification failed"
    ]);
    exit;
}

// =========================
// 🟢 6. نجاح
// =========================
echo json_encode([
    "status"=>"success",
    "message"=>"Booking completed successfully"
]);

exit;
?>