<?php

header("Content-Type: application/json");

include "../php/db.php";

// =========================
// 🔥 PHPMailer
// =========================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

// =========================
// 🟢 READ DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$booking_id =
    $data['booking_id'] ?? null;

if(!$booking_id){

    echo json_encode([
        "status"=>"error",
        "message"=>"Missing booking id"
    ]);

    exit;
}

// =========================
// 🟢 GET BOOKING INFO
// =========================
$sql = "
SELECT 
    booking.Patient_ID,
    booking.Booking_Date,

    patient.Name AS Patient_Name,
    patient.Email_Address

FROM booking

JOIN patient
ON booking.Patient_ID = patient.Patient_ID

WHERE booking.Booking_ID = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $booking_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$row =
    $result->fetch_assoc();

if(!$row){

    echo json_encode([
        "status"=>"error",
        "message"=>"Booking not found"
    ]);

    exit;
}

$patient_id =
    $row['Patient_ID'];

$patient_name =
    $row['Patient_Name'];

$patient_email =
    $row['Email_Address'];

$booking_date =
    $row['Booking_Date'];

// =========================
// 🟢 UPDATE STATUS
// =========================
$update = $conn->prepare("
    UPDATE booking
    SET Status='Canceled'
    WHERE Booking_ID=?
");

$update->bind_param(
    "i",
    $booking_id
);

// =========================
// ✅ EXECUTE
// =========================
if($update->execute()){

    // =========================
    // 🔔 NOTIFICATION
    // =========================
    $message =
        "❌ Your appointment has been canceled because you were absent";

    $notify = $conn->prepare("
        INSERT INTO notifications
        (patient_id, message, type, source)

        VALUES (?, ?, 'error', 'doctor')
    ");

    $notify->bind_param(
        "is",
        $patient_id,
        $message
    );

    $notify->execute();

    // =========================
    // 🔥 SEND EMAIL
    // =========================
    try{

        $mail = new PHPMailer(true);

        $mail->isSMTP();

        $mail->Host =
            'smtp.gmail.com';

        $mail->SMTPAuth =
            true;

        $mail->Username =
            'medino.system@gmail.com';

        $mail->Password =
            'vssv jsoe ifel dggz';

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;

        // =========================
        // 🔥 FROM
        // =========================
        $mail->setFrom(
            'medino.system@gmail.com',
            'Medino System'
        );

        // =========================
        // 🔥 TO
        // =========================
        $mail->addAddress(
            $patient_email,
            $patient_name
        );

        // =========================
        // 🔥 EMAIL
        // =========================
        $mail->isHTML(true);

        $mail->Subject =
            "Appointment Cancelled";

        $mail->Body = '

        <div
            style="
                background:#f4f7fb;
                padding:40px;
                font-family:Arial;
            "
        >

            <div
                style="
                    max-width:600px;
                    margin:auto;
                    background:#fff;
                    border-radius:24px;
                    padding:40px;
                    text-align:center;
                "
            >

                <div
                    style="
                        width:90px;
                        height:90px;
                        margin:auto;
                        border-radius:50%;
                        background:
                            linear-gradient(
                                135deg,
                                #ef4444,
                                #dc2626
                            );
                        display:flex;
                        justify-content:center;
                        align-items:center;
                        color:#fff;
                        font-size:40px;
                        margin-bottom:25px;
                    "
                >
                    ❌
                </div>

                <h1
                    style="
                        color:#dc2626;
                    "
                >
                    Appointment Cancelled
                </h1>

                <p
                    style="
                        color:#64748b;
                        margin-top:20px;
                    "
                >
                    Hello '.$patient_name.'
                </p>

                <p
                    style="
                        color:#64748b;
                        margin-top:15px;
                        line-height:28px;
                    "
                >
                    Your appointment has been cancelled because you were marked absent.
                </p>

                <div
                    style="
                        margin-top:25px;
                        background:#fef2f2;
                        border-radius:16px;
                        padding:20px;
                    "
                >

                    <strong>Date:</strong>
                    '.$booking_date.'

                </div>

            </div>

        </div>
        ';

        $mail->send();

    }catch(Exception $e){

    }

    echo json_encode([
        "status"=>"success"
    ]);

}else{

    echo json_encode([
        "status"=>"error",
        "message"=>"Failed to update"
    ]);
}

?>