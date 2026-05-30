<?php

header("Content-Type: application/json");

// =========================
// 🔥 HIDE ERRORS
// =========================
ini_set('display_errors', 0);

error_reporting(0);

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
// 🔥 GET DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

// =========================
// 🟢 VALIDATION
// =========================
if(!$data){

    echo json_encode([
        "status"=>"error",
        "message"=>"No data"
    ]);

    exit;
}

$patient_id =
    $data['patient_id'] ?? null;

$doctor_id =
    $data['doctor_id'] ?? null;

$date =
    $data['date'] ?? null;

$time =
    $data['time'] ?? null;

if(
    !$patient_id
    ||
    !$doctor_id
    ||
    !$date
    ||
    !$time
){

    echo json_encode([
        "status"=>"error",
        "message"=>"Missing data"
    ]);

    exit;
}

// =========================
// 🔥 PREVENT FUTURE BOOKING
// WITH SAME DOCTOR
// =========================
$futureBooking = $conn->query("
    SELECT Booking_ID

    FROM booking

    WHERE Patient_ID='$patient_id'

    AND Doctor_ID='$doctor_id'

    AND Status <> 'Canceled'

    AND Status <> 'Completed'

    AND Booking_Date > NOW()
");

if($futureBooking->num_rows > 0){

    echo json_encode([
        "status"=>"error",
        "message"=>"You already have an upcoming appointment with this doctor"
    ]);

    exit;
}

// =========================
// 🔥 PREVENT SAME DAY
// =========================
$checkPatient = $conn->query("
    SELECT *

    FROM booking

    WHERE Patient_ID='$patient_id'

    AND Doctor_ID='$doctor_id'

    AND DATE(Booking_Date)='$date'

    AND Status IN ('Confirmed','Completed')
");

if($checkPatient->num_rows > 0){

    echo json_encode([
        "status"=>"error",
        "message"=>"You already booked in this day"
    ]);

    exit;
}


// =========================
// 🔥 PREVENT SAME TIME
// =========================
$check = $conn->query("
    SELECT *

    FROM booking

    WHERE Doctor_ID='$doctor_id'

    AND Booking_Date='$date $time'

    AND Status IN ('Confirmed','Completed')
");

if($check->num_rows > 0){

    echo json_encode([
        "status"=>"error",
        "message"=>"Time already booked"
    ]);

    exit;
}

// =========================
// 🔥 INSERT BOOKING
// =========================
$sql = "
INSERT INTO booking
(
    Doctor_ID,
    Patient_ID,
    Booking_Date,
    Status
)

VALUES
(
    '$doctor_id',
    '$patient_id',
    '$date $time',
    'Confirmed'
)
";

if($conn->query($sql)){

    // =========================
    // 🔥 GET BOOKING ID
    // =========================
    $booking_id =
        $conn->insert_id;

    // =========================
    // 🔥 NOTIFICATION
    // =========================
    $msg =
        "✅ Booking confirmed successfully";

    $sql2 = "
        INSERT INTO notifications
        (
            patient_id,
            message,
            type,
            source
        )

        VALUES
        (?, ?, 'success', 'system')
    ";

    $stmt = $conn->prepare($sql2);

    $stmt->bind_param(
        "is",
        $patient_id,
        $msg
    );

    $stmt->execute();

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
    // 🔥 GET DOCTOR + SPECIALTY
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

            // 🔥 YOUR GMAIL
            $mail->Username =
                'medino.system@gmail.com';

            // 🔥 APP PASSWORD
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
                $patient['Email_Address'],
                $patient['Name']
            );

            // =========================
            // 🔥 EMAIL CONTENT
            // =========================
            $mail->isHTML(true);

            $mail->Subject =
                "Appointment Confirmed";

            $mail->Body = '

            <div
                style="
                    font-family:Arial;
                    background:#f4f7fb;
                    padding:40px;
                "
            >

                <div
                    style="
                        max-width:650px;
                        margin:auto;
                        background:#fff;
                        border-radius:20px;
                        overflow:hidden;
                        box-shadow:0 10px 30px rgba(0,0,0,.08);
                    "
                >

                    <!-- HEADER -->
                    <div
                        style="
                            background:
                            linear-gradient(
                                90deg,
                                #1e3a8a,
                                #0ea5e9
                            );

                            color:#fff;

                            padding:30px;
                        "
                    >

                        <h1
                            style="
                                margin:0;
                            "
                        >
                            ✅ Booking Confirmed
                        </h1>

                    </div>

                    <!-- CONTENT -->
                    <div
                        style="
                            padding:35px;
                        "
                    >

                        <h2>
                            Hello '
                            .
                            $patient['Name']
                            .
                            '
                        </h2>

                        <p>
                            Your appointment
                            has been booked
                            successfully.
                        </p>

                        <!-- CARD -->
                        <div
                            style="
                                background:#f8fafc;

                                border-radius:16px;

                                padding:25px;

                                margin-top:25px;
                            "
                        >

                            <p>
                                <strong>
                                    Booking ID:
                                </strong>

                                #'
                                .
                                $booking_id
                                .
                                '
                            </p>

                            <p>
                                <strong>
                                    Doctor:
                                </strong>

                                '
                                .
                                $doctor['doctor_name']
                                .
                                '
                            </p>

                            <p>
                                <strong>
                                    Specialty:
                                </strong>

                                '
                                .
                                $doctor['specialty_name']
                                .
                                '
                            </p>

                            <p>
                                <strong>
                                    Date:
                                </strong>

                                '
                                .
                                $date
                                .
                                '
                            </p>

                            <p>
                                <strong>
                                    Time:
                                </strong>

                                '
                                .
                                $time
                                .
                                '
                            </p>

                        </div>

                        <p
                            style="
                                margin-top:30px;
                                color:#64748b;
                            "
                        >
                            Thank you for
                            choosing Medino.
                        </p>

                    </div>

                </div>

            </div>
            ';

            $mail->send();

        }catch(Exception $e){

            error_log(
                $mail->ErrorInfo
            );
        }
    }

    echo json_encode([
        "status"=>"success"
    ]);

}else{

    echo json_encode([
        "status"=>"error",
        "message"=>"DB Error"
    ]);
}
?>