<?php

header("Content-Type: application/json");

include("php/db.php");

// =========================
// 🔥 PHPMailer
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

$admin_id =
    $data['admin_id'] ?? null;

if(!$admin_id){

    echo json_encode([

        "status"=>"error",

        "message"=>"Missing ID"
    ]);

    exit;
}

// =========================
// 🔥 GET ADMIN
// =========================
$stmt = $conn->prepare("

SELECT *

FROM admin

WHERE Admin_ID = ?

LIMIT 1

");

$stmt->bind_param(
    "i",
    $admin_id
);

$stmt->execute();

$result =
    $stmt->get_result();

if($result->num_rows === 0){

    echo json_encode([

        "status"=>"error",

        "message"=>"Admin not found"
    ]);

    exit;
}

$admin =
    $result->fetch_assoc();

// =========================
// 🔥 NEW OTP
// =========================
$otp =
    rand(100000,999999);

$expire =
    date(
        "Y-m-d H:i:s",
        strtotime("+5 minutes")
    );

// =========================
// 🔥 SAVE
// =========================
$update = $conn->prepare("

UPDATE admin

SET
    otp_code = ?,
    otp_expire = ?

WHERE Admin_ID = ?

");

$update->bind_param(

    "ssi",

    $otp,
    $expire,
    $admin_id
);

$update->execute();

// =========================
// 🔥 SEND MAIL
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

    $mail->setFrom(
        'medino.system@gmail.com',
        'Medino Security'
    );

    $mail->addAddress(
        $admin['Email_Address'],
        $admin['User_Name']
    );

    $mail->isHTML(true);

    $mail->Subject =
        "New OTP Code";

    $mail->Body = "

    <h2>Your New OTP Code</h2>

    <h1>$otp</h1>

    <p>
        This OTP expires in 5 minutes
    </p>
    ";

    $mail->send();

}catch(Exception $e){

    echo json_encode([

        "status"=>"error",

        "message"=>"Mail failed"
    ]);

    exit;
}

echo json_encode([

    "status"=>"success"
]);
?>