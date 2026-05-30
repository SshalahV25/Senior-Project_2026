<?php
include "db.php";

$doctor_id = $_POST['doctor_id'];
$date = $_POST['date'];
$start = $_POST['start_time'];
$end = $_POST['end_time'];

$id = isset($_POST['id']) ? $_POST['id'] : null;

if($id){

    // ================= UPDATE =================
    $sql = "
    UPDATE APPOINTMENT SET
        Doctor_ID = '$doctor_id',
        Appointment_Date = '$date',
        Start_Time = '$start',
        End_Time = '$end'
    WHERE Appointment_ID = $id
    ";

    if($conn->query($sql)){
        echo "updated";
    } else {
        echo "error: " . $conn->error;
    }

} else {

    // ================= INSERT =================
    $sql = "
    INSERT INTO APPOINTMENT (Doctor_ID, Appointment_Date, Start_Time, End_Time)
    VALUES ('$doctor_id', '$date', '$start', '$end')
    ";

    if($conn->query($sql)){
        echo "inserted";
    } else {
        echo "error: " . $conn->error;
    }
}
?>