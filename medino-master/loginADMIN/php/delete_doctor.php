<?php
include "db.php";

$id = $_GET['id'];

// 1️⃣ هات كل ال appointments الخاصة بالدكتور
$app = $conn->query("SELECT Appointment_ID FROM APPOINTMENT WHERE Doctor_ID=$id");

while($row = $app->fetch_assoc()){

    $app_id = $row['Appointment_ID'];

    // 2️⃣ احذف ال bookings المرتبطة بكل appointment
    $conn->query("DELETE FROM BOOKING WHERE Appointment_ID=$app_id");
}

// 3️⃣ احذف appointments
$conn->query("DELETE FROM APPOINTMENT WHERE Doctor_ID=$id");

// 4️⃣ احذف work_days
$conn->query("DELETE FROM WORK_DAYS WHERE Doctor_ID=$id");

// 5️⃣ احذف الدكتور
$conn->query("DELETE FROM DOCTOR WHERE Doctor_ID=$id");

echo "Deleted Successfully ✅";
?>