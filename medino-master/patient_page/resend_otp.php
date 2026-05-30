<?php

date_default_timezone_set("Africa/Cairo");

header("Content-Type: application/json");

include "../php/db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

// =========================
// GET DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$email = trim($data['email'] ?? '');

if(empty($email)){

    echo json_encode([
        "status"=>"error",
        "message"=>"Missing email"
    ]);

    exit;
}

// =========================
// CHECK USER IN PENDING
// =========================
$stmt = $conn->prepare("
    SELECT Name
    FROM pending_patients
    WHERE Email_Address=?
");

$stmt->bind_param(
    "s",
    $email
);

$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows === 0){

    echo json_encode([
        "status"=>"error",
        "message"=>"User not found"
    ]);

    exit;
}

$user = $result->fetch_assoc();

// =========================
// NEW OTP
// =========================
$newOTP = rand(1000,9999);

$newExpiry = date(
    "Y-m-d H:i:s",
    strtotime("+5 minutes")
);

// =========================
// UPDATE OTP
// =========================
$update = $conn->prepare("
    UPDATE pending_patients
    SET
        verification_code=?,
        otp_expiry=?
    WHERE Email_Address=?
");

$update->bind_param(
    "sss",
    $newOTP,
    $newExpiry,
    $email
);

$update->execute();

// =========================
// SEND EMAIL
// =========================
$mail = new PHPMailer(true);

try{

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

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
        $email,
        $user['Name']
    );

    $mail->isHTML(true);

    $mail->Subject =
        "New Verification Code";

    $mail->Body = "
    <div style='font-family:Arial;padding:40px;background:#f4f7fb'>
        <div style='max-width:600px;margin:auto;background:#fff;border-radius:20px;padding:40px;text-align:center'>
            <h1 style='color:#1e3a8a'>
                New Verification Code
            </h1>

            <div style='margin-top:30px;font-size:42px;letter-spacing:12px;font-weight:bold;color:#0ea5e9'>
                {$newOTP}
            </div>

            <p style='margin-top:20px;color:#666'>
                This code expires in 5 minutes
            </p>
        </div>
    </div>
    ";

    $mail->send();

    echo json_encode([
        "status"=>"success"
    ]);

}catch(Exception $e){

    echo json_encode([
        "status"=>"error",
        "message"=>"Email failed"
    ]);
}
?>