<?php
header("Content-Type: application/json");

include "../php/db.php";

$data = json_decode(file_get_contents("php://input"), true);

// =========================
// 🟢 1. تحقق من البيانات
// =========================
$booking_id = $data['booking_id'] ?? null;
$doctor_id  = $data['doctor_id'] ?? null;
$date       = $data['date'] ?? null;
$time       = $data['time'] ?? null;

if(!$booking_id || !$doctor_id || !$date || !$time){
    echo json_encode([
        "status"=>"error",
        "message"=>"Missing data"
    ]);
    exit;
}

$datetime = $date . " " . $time . ":00";

// =========================
// 🟢 2. نجيب patient_id
// =========================
$sql = "SELECT Patient_ID FROM booking WHERE Booking_ID=?";
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

// =========================
// 🔥 PREVENT MULTIPLE BOOKINGS
// SAME DOCTOR BEFORE VISIT
// =========================
$doctorCheck = $conn->prepare("

SELECT Booking_ID

FROM booking

WHERE Patient_ID = ?

AND Doctor_ID = ?

AND Status IN ('Confirmed','Completed')

AND Booking_ID != ?

");

$doctorCheck->bind_param(
    "iii",
    $patient_id,
    $doctor_id,
    $booking_id
);

$doctorCheck->execute();

$doctorResult =
    $doctorCheck->get_result();

if($doctorResult->num_rows > 0){

    echo json_encode([

        "status"=>"error",

        "message"=>"You already have another appointment with this doctor"

    ]);

    exit;
}


// =========================
// 🔥 3. CHECK (نفس اليوم + نفس الدكتور)
// =========================
$check = $conn->prepare("
    SELECT Booking_ID
    FROM booking

    WHERE Doctor_ID = ?

    AND Booking_Date = ?

    AND Status IN ('Confirmed','Completed')

    AND Booking_ID != ?
");

// =========================
// 🔥 CHECK SAME DOCTOR SAME DAY
// =========================
$checkDay = $conn->prepare("

SELECT Booking_ID

FROM booking

WHERE Doctor_ID = ?

AND Patient_ID = ?

AND DATE(Booking_Date) = DATE(?)

AND Status IN ('Confirmed','Completed')

AND Booking_ID != ?

");

$checkDay->bind_param(
    "iisi",
    $doctor_id,
    $patient_id,
    $datetime,
    $booking_id
);

$checkDay->execute();

$resDay = $checkDay->get_result();

if($resDay->num_rows > 0){

    echo json_encode([

        "status"=>"error",

        "message"=>"You already have an appointment with this doctor on this day"
    ]);

    exit;
}

$check->bind_param("isi", $doctor_id, $datetime, $booking_id);
$check->execute();
$result = $check->get_result();

if($result->num_rows > 0){
    echo json_encode([
        "status"=>"error",
        "message"=>"You already have an appointment with this doctor on this day"
    ]);
    exit;
}

// =========================
// 🔥 4. UPDATE
// =========================
$sql = "UPDATE booking SET Doctor_ID=?, Booking_Date=? WHERE Booking_ID=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $doctor_id, $datetime, $booking_id);

if($stmt->execute()){

    // =========================
    // 🟢 5. Notification (UPDATED)
    // =========================
    $msg = "✏️ Your appointment has been updated";

    $sql2 = "INSERT INTO notifications (patient_id, message, type, source)
            VALUES (?, ?, 'warning', 'system')";

    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("is", $patient_id, $msg);
    $stmt2->execute();

    echo json_encode(["status"=>"success"]);

}else{
    echo json_encode([
        "status"=>"error",
        "message"=>"Update failed"
    ]);
}
?>