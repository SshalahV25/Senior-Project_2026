<?php

header("Content-Type: application/json");

include "../php/db.php";

// =========================
// 🔥 LOAD PHPMailer
// =========================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/Exception.php';

require '../PHPMailer-master/src/PHPMailer.php';

require '../PHPMailer-master/src/SMTP.php';

// =========================
// 🔥 CURRENT TIME
// =========================
date_default_timezone_set("Africa/Cairo");

$now = new DateTime();

// =========================
// 🔥 GET BOOKINGS
// =========================
$sql = "
SELECT

    booking.Booking_ID,
    booking.Booking_Date,
    booking.reminder_24_sent,
    booking.reminder_1h_sent,

    patient.Patient_ID,
    patient.Name,
    patient.Email_Address,

    doctor.Name AS Doctor_Name

FROM booking

INNER JOIN patient
ON booking.Patient_ID = patient.Patient_ID

INNER JOIN doctor
ON booking.Doctor_ID = doctor.Doctor_ID

WHERE booking.Status = 'Confirmed'
";

$result = $conn->query($sql);

$sent = [];

// =========================
// 🔥 LOOP BOOKINGS
// =========================
while($row = $result->fetch_assoc()){

    $bookingDate = new DateTime(
        $row['Booking_Date']
    );

    // =========================
    // ⏰ TIME DIFFERENCE
    // =========================
    $diffSeconds =
        $bookingDate->getTimestamp()
        -
        $now->getTimestamp();

    $hoursLeft =
        $diffSeconds / 3600;

    // =========================
    // 🔥 24H REMINDER
    // =========================
    if(

        $hoursLeft <= 24
        &&
        $hoursLeft > 23
        &&
        !$row['reminder_24_sent']

    ){

        sendReminder(

            $conn,

            $row,

            "24h"
        );

        // 🔥 UPDATE FLAG
        $update = $conn->prepare("
            UPDATE booking
            SET reminder_24_sent = 1
            WHERE Booking_ID = ?
        ");

        $update->bind_param(
            "i",
            $row['Booking_ID']
        );

        $update->execute();

        $sent[] =
            "24h reminder sent";
    }

    // =========================
    // 🔥 1H REMINDER
    // =========================
    if(

        $hoursLeft <= 1
        &&
        $hoursLeft > 0
        &&
        !$row['reminder_1h_sent']

    ){

        sendReminder(

            $conn,

            $row,

            "1h"
        );

        // 🔥 UPDATE FLAG
        $update = $conn->prepare("
            UPDATE booking
            SET reminder_1h_sent = 1
            WHERE Booking_ID = ?
        ");

        $update->bind_param(
            "i",
            $row['Booking_ID']
        );

        $update->execute();

        $sent[] =
            "1h reminder sent";
    }
}

// =========================
// 🔥 RESPONSE
// =========================
echo json_encode([

    "status"=>"success",

    "sent"=>$sent
]);

// =================================================
// 🔥 SEND REMINDER FUNCTION
// =================================================
function sendReminder(
    $conn,
    $data,
    $type
){

    // =========================
    // 🔥 MESSAGE
    // =========================
    if($type === "24h"){

        $message =
            "🔔 Reminder: "
            .
            "You have an appointment tomorrow with "
            .
            $data['Doctor_Name'];

        $emailTitle =
            "Appointment Reminder - Tomorrow";
    }

    else{

        $message =
            "⏰ Reminder: "
            .
            "Your appointment starts in 1 hour with "
            .
            $data['Doctor_Name'];

        $emailTitle =
            "Appointment Reminder - 1 Hour Left";
    }

    // =========================
    // 🔥 SAVE NOTIFICATION
    // =========================
    $insert = $conn->prepare("
        INSERT INTO notifications
        (
            patient_id,
            message,
            type,
            source
        )

        VALUES
        (?, ?, 'warning', 'system')
    ");

    $insert->bind_param(
        "is",
        $data['Patient_ID'],
        $message
    );

    $insert->execute();

    // =========================
    // 🔥 SEND EMAIL
    // =========================
    if(!empty($data['Email_Address'])){

        $mail = new PHPMailer(true);

        try{

            $mail->isSMTP();

            $mail->Host =
                'smtp.gmail.com';

            $mail->SMTPAuth =
                true;

            // 🔥 YOUR GMAIL
            $mail->Username =
                'medino.system@gmail.com';

            // 🔥 YOUR APP PASSWORD
            $mail->Password =
                'vssv jsoe ifel dggz';

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_STARTTLS;

            $mail->Port = 587;

            // =========================
            // 🔥 EMAIL INFO
            // =========================
            $mail->setFrom(
                'medino.system@gmail.com',
                'Medino System'
            );

            $mail->addAddress(
                $data['Email_Address'],
                $data['Name']
            );

            // =========================
            // 🔥 CONTENT
            // =========================
            $mail->isHTML(true);

            $mail->Subject =
                $emailTitle;

            $mail->Body = '

                <div
                    style="
                        font-family:Arial;
                        padding:20px;
                    "
                >

                    <h2>
                        Medino Reminder
                    </h2>

                    <p>'
                        .
                        $message
                        .
                    '</p>

                    <p>
                        Appointment Time:
                        '
                        .
                        $data['Booking_Date']
                        .
                    '</p>

                </div>
            ';

            $mail->send();

        }catch(Exception $e){

            error_log(
                $mail->ErrorInfo
            );
        }
    }
}
?>