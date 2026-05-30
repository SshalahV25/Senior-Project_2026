<?php
header("Content-Type: application/json");
include "../php/db.php";

$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['booking_id'])){
    echo json_encode(["status"=>"error","message"=>"Missing booking_id"]);
    exit;
}

$booking_id = $data['booking_id'];

$sql = "SELECT 
            b.Booking_ID,
            b.Doctor_ID,
            b.Booking_Date,
            d.Name AS doctor_name,
            s.Specialty_ID,
            s.Name AS specialty_name
        FROM booking b
        LEFT JOIN doctor d ON b.Doctor_ID = d.Doctor_ID
        LEFT JOIN specialty s ON d.Specialty_ID = s.Specialty_ID
        WHERE b.Booking_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    echo json_encode($row);
}else{
    echo json_encode(["status"=>"error","message"=>"Booking not found"]);
}
?>