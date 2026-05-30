<?php

include "db.php";

// =========================
// 🔥 PHPMailer
// =========================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer-master/src/Exception.php';
require '../../PHPMailer-master/src/PHPMailer.php';
require '../../PHPMailer-master/src/SMTP.php';

// =========================
// 🔥 GET DATA
// =========================
$patient_id   = $_POST['patient_id'] ?? '';
$doctor_id    = $_POST['doctor_id'] ?? '';
$booking_date = $_POST['booking_date'] ?? '';
$status       = $_POST['status'] ?? 'Confirmed';
$notes        = $_POST['notes'] ?? '';

// =========================
// 🔥 VALIDATION
// =========================
if(
    empty($patient_id)
    ||
    empty($doctor_id)
    ||
    empty($booking_date)
){

    echo "missing";
    exit;
}

// =========================
// 🔥 PREVENT SAME DAY
// Confirmed + Completed
// =========================
$date_only = date(
    "Y-m-d",
    strtotime($booking_date)
);

$check = $conn->query("

SELECT *

FROM booking

WHERE
Patient_ID = '$patient_id'

AND
DATE(Booking_Date) = '$date_only'

AND
(
    Status = 'Confirmed'
    OR
    Status = 'Completed'
)

");

if($check->num_rows > 0){

    echo "already_booked";

    exit;
}

// =========================
// 🔥 INSERT BOOKING
// =========================
$sql = "

INSERT INTO booking
(
    Patient_ID,
    Doctor_ID,
    Booking_Date,
    Status,
    Notes
)

VALUES
(
    '$patient_id',
    '$doctor_id',
    '$booking_date',
    '$status',
    '$notes'
)

";

if($conn->query($sql)){

    // =========================
    // 🔥 BOOKING ID
    // =========================
    $booking_id =
        $conn->insert_id;

    // =========================
    // 🔥 NOTIFICATION
    // =========================
    $message =
        "✅ Your booking has been confirmed";

    $notif = "

    INSERT INTO notifications
    (
        patient_id,
        message,
        type,
        source
    )

    VALUES
    (
        '$patient_id',
        '$message',
        'success',
        'admin'
    )

    ";

    $conn->query($notif);

    // =========================
    // 🔥 GET PATIENT
    // =========================
    $patientQuery = $conn->query("

    SELECT
        Name,
        Email_Address

    FROM patient

    WHERE Patient_ID='$patient_id'

    ");

    $patient =
        $patientQuery->fetch_assoc();

    // =========================
    // 🔥 GET DOCTOR
    // =========================
    $doctorQuery = $conn->query("

    SELECT
        doctor.Name AS doctor_name,
        specialty.Name AS specialty_name

    FROM doctor

    INNER JOIN specialty
    ON doctor.Specialty_ID =
       specialty.Specialty_ID

    WHERE doctor.Doctor_ID='$doctor_id'

    ");

    $doctor =
        $doctorQuery->fetch_assoc();

    // =========================
    // 🔥 DATE + TIME
    // =========================
    $date =
        date(
            "Y-m-d",
            strtotime($booking_date)
        );

    $time =
        date(
            "H:i",
            strtotime($booking_date)
        );

    // =========================
    // 🔥 SEND EMAIL
    // =========================
    if(!empty($patient['Email_Address'])){

        $mail = new PHPMailer(true);

        try{

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

            $mail->setFrom(
                'medino.system@gmail.com',
                'Medino System'
            );

            $mail->addAddress(
                $patient['Email_Address'],
                $patient['Name']
            );

            $mail->isHTML(true);

            $mail->Subject =
                "Appointment Confirmed";

            $mail->Body = "

            <h2>
                Booking Confirmed ✅
            </h2>

            <p>
                Hello {$patient['Name']}
            </p>

            <p>
                Your appointment has been booked successfully.
            </p>

            <hr>

            <p>
                <strong>Doctor:</strong>
                {$doctor['doctor_name']}
            </p>

            <p>
                <strong>Specialty:</strong>
                {$doctor['specialty_name']}
            </p>

            <p>
                <strong>Date:</strong>
                {$date}
            </p>

            <p>
                <strong>Time:</strong>
                {$time}
            </p>

            ";

            $mail->send();

        }catch(Exception $e){

        }
    }

    echo "success";

}else{

    echo "error";
}
?>