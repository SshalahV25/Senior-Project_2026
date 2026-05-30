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
// 🟢 CHECK DATA
// =========================
$booking_id = $_POST['booking_id'] ?? null;

$diagnosis = trim($_POST['diagnosis'] ?? '');

$notes = trim($_POST['notes'] ?? '');

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
    booking.Doctor_ID,
    booking.Patient_ID,
    booking.Booking_Date,

    patient.Name AS patient_name,
    patient.Email_Address

FROM booking

JOIN patient
ON booking.Patient_ID = patient.Patient_ID

WHERE Booking_ID = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $booking_id);

$stmt->execute();

$result = $stmt->get_result();

$booking = $result->fetch_assoc();

if(!$booking){

    echo json_encode([
        "status"=>"error",
        "message"=>"Booking not found"
    ]);

    exit;
}

$doctor_id =
    $booking['Doctor_ID'];

$patient_id =
    $booking['Patient_ID'];

$patient_name =
    $booking['patient_name'];

$patient_email =
    $booking['Email_Address'];

$booking_date =
    $booking['Booking_Date'];


// =========================
// 🟢 FILE UPLOAD
// =========================
$fileName = null;

if(
    isset($_FILES['file'])
    &&
    $_FILES['file']['error'] === 0
){

    $fileTmp =
        $_FILES['file']['tmp_name'];

    $original =
        $_FILES['file']['name'];

    $ext = strtolower(
        pathinfo(
            $original,
            PATHINFO_EXTENSION
        )
    );

    $allowed = [
        "pdf",
        "jpg",
        "jpeg",
        "png",
        "webp"
    ];

    if(!in_array($ext, $allowed)){

        echo json_encode([
            "status"=>"error",
            "message"=>"Invalid file type"
        ]);

        exit;
    }

    $newName =
        time() .
        "_" .
        rand(1000,9999) .
        "." .
        $ext;

    $uploadPath =
        "../uploads/prescriptions/" .
        $newName;

    if(
        move_uploaded_file(
            $fileTmp,
            $uploadPath
        )
    ){

        $fileName = $newName;
    }
}


// =========================
// 🟢 INSERT PRESCRIPTION
// =========================
$insert = $conn->prepare("
    INSERT INTO prescriptions
    (
        doctor_id,
        patient_id,
        booking_id,
        notes,
        file
    )

    VALUES (?, ?, ?, ?, ?)
");

$fullNotes =
    "Diagnosis:\n"
    . $diagnosis
    . "\n\nPrescription:\n"
    . $notes;

$insert->bind_param(
    "iiiss",
    $doctor_id,
    $patient_id,
    $booking_id,
    $fullNotes,
    $fileName
);


// =========================
// 🟢 EXECUTE
// =========================
if($insert->execute()){

    // =========================
    // 🔥 UPDATE STATUS
    // =========================
    $update = $conn->prepare("
        UPDATE booking
        SET Status='Completed'
        WHERE Booking_ID=?
    ");

    $update->bind_param(
        "i",
        $booking_id
    );

    $update->execute();

    // =========================
    // 🔔 NOTIFICATION
    // =========================
    $message =
        "📋 Your prescription is ready";

    $fullMessage =
        $message . "||" . $booking_id;

    $notify = $conn->prepare("
        INSERT INTO notifications
        (
            patient_id,
            message,
            type,
            source
        )

        VALUES
        (?, ?, 'success', 'doctor')
    ");

    $notify->bind_param(
        "is",
        $patient_id,
        $fullMessage
    );

    $notify->execute();

    // =========================
    // ⚡ FAST RESPONSE
    // =========================
    ignore_user_abort(true);

    ob_start();

    echo json_encode([
        "status"=>"success"
    ]);

    $size = ob_get_length();

    header("Content-Encoding: none");
    header("Content-Length: " . $size);
    header("Connection: close");

    ob_end_flush();
    flush();

    if(function_exists('fastcgi_finish_request')){

        fastcgi_finish_request();
    }

    // =========================
    // 🔥 SEND EMAIL
    // =========================
    try{

        $mail = new PHPMailer(true);

        // SMTP
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

        $mail->CharSet = 'UTF-8';

        // FROM
        $mail->setFrom(
            'medino.system@gmail.com',
            'Medino System'
        );

        // TO
        $mail->addAddress(
            $patient_email,
            $patient_name
        );

        // FILE ATTACHMENT
        $filePath =
            "../uploads/prescriptions/" .
            $fileName;

        if(
            !empty($fileName)
            &&
            file_exists($filePath)
        ){

            $mail->addAttachment(
                $filePath
            );
        }

        // EMAIL
        $mail->isHTML(true);

        $mail->Subject =
            "Medical Report & Prescription";

        $mail->Body = '

        <div
            style="
                background:#f1f5f9;
                padding:40px;
                font-family:Arial,sans-serif;
            "
        >

            <div
                style="
                    max-width:650px;
                    margin:auto;
                    background:#ffffff;
                    border-radius:24px;
                    overflow:hidden;
                    box-shadow:0 10px 40px rgba(0,0,0,.08);
                "
            >

                <!-- HEADER -->
                <div
                    style="
                        background:
                        linear-gradient(
                            135deg,
                            #1e3a8a,
                            #0ea5e9
                        );
                        padding:40px;
                        text-align:center;
                    "
                >

                    <div
                        style="
                            width:90px;
                            height:90px;
                            background:#ffffff;
                            border-radius:50%;
                            margin:auto;
                            line-height:90px;
                            font-size:42px;
                        "
                    >
                        ✅
                    </div>

                    <h1
                        style="
                            color:#ffffff;
                            margin-top:25px;
                            font-size:34px;
                        "
                    >
                        Appointment Completed
                    </h1>

                    <p
                        style="
                            color:#dbeafe;
                            margin-top:10px;
                            font-size:16px;
                        "
                    >
                        Your medical report is ready
                    </p>

                </div>

                <!-- CONTENT -->
                <div
                    style="
                        padding:40px;
                    "
                >

                    <p
                        style="
                            font-size:18px;
                            color:#334155;
                        "
                    >
                        Hello '.$patient_name.',
                    </p>

                    <p
                        style="
                            margin-top:20px;
                            color:#64748b;
                            line-height:30px;
                            font-size:16px;
                        "
                    >
                        Your appointment has been completed successfully.
                        Please find your diagnosis and prescription below.
                    </p>

                    <!-- INFO -->
                    <div
                        style="
                            margin-top:35px;
                            background:#f8fafc;
                            border-radius:18px;
                            padding:25px;
                            border:1px solid #e2e8f0;
                        "
                    >

                        <p
                            style="
                                margin-bottom:12px;
                                color:#0f172a;
                            "
                        >
                            <strong>Appointment Date:</strong>
                            '.$booking_date.'
                        </p>

                        <p
                            style="
                                margin-top:25px;
                                margin-bottom:12px;
                            "
                        >
                            <strong>Diagnosis:</strong>
                        </p>

                        <div
                            style="
                                background:#ffffff;
                                border-radius:14px;
                                padding:22px;
                                border:1px solid #dbeafe;
                                line-height:30px;
                                color:#475569;
                            "
                        >
                            '.nl2br(htmlspecialchars($diagnosis)).'
                        </div>

                        <p
                            style="
                                margin-top:25px;
                                margin-bottom:12px;
                            "
                        >
                            <strong>Prescription:</strong>
                        </p>

                        <div
                            style="
                                background:#ffffff;
                                border-radius:14px;
                                padding:22px;
                                border:1px solid #dbeafe;
                                line-height:30px;
                                color:#475569;
                            "
                        >
                            '.nl2br(htmlspecialchars($notes)).'
                        </div>

                    </div>

                    <!-- ATTACHMENT -->
                    <div
                        style="
                            margin-top:35px;
                            background:#eff6ff;
                            border-radius:18px;
                            padding:22px;
                            text-align:center;
                            border:1px solid #bfdbfe;
                        "
                    >

                        <p
                            style="
                                color:#1d4ed8;
                                font-size:15px;
                                margin:0;
                            "
                        >
                            📎 Prescription attachment included
                        </p>

                    </div>

                    <!-- FOOTER -->
                    <p
                        style="
                            margin-top:40px;
                            text-align:center;
                            color:#94a3b8;
                            font-size:13px;
                            line-height:24px;
                        "
                    >
                        Thank you for choosing Medino System 💙
                    </p>

                </div>

            </div>

        </div>
        ';

        // SEND EMAIL
        $mail->send();

    }catch(Exception $e){

        file_put_contents(
            "mail_error_log.txt",
            date("Y-m-d H:i:s") .
            " => " .
            $mail->ErrorInfo .
            PHP_EOL,
            FILE_APPEND
        );
    }

    exit;

}else{

    echo json_encode([
        "status"=>"error",
        "message"=>"Failed to save"
    ]);
}
?>