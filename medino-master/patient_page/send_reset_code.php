<?php

date_default_timezone_set(
    "Africa/Cairo"
);

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
// 🔥 GET DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$email =
    trim($data['email'] ?? '');

// =========================
// 🔥 VALIDATION
// =========================
if(empty($email)){

    echo json_encode([

        "status"=>"error",

        "message"=>"Enter your email"
    ]);

    exit;
}

// =========================
// 🔥 CHECK USER
// =========================
$stmt = $conn->prepare("
    SELECT Name

    FROM patient

    WHERE Email_Address=?
");

$stmt->bind_param(
    "s",
    $email
);

$stmt->execute();

$result =
    $stmt->get_result();

if($result->num_rows === 0){

    echo json_encode([

        "status"=>"error",

        "message"=>"Email not found"
    ]);

    exit;
}

$user =
    $result->fetch_assoc();

// =========================
// 🔥 GENERATE OTP
// =========================
$resetCode =
    rand(1000,9999);

// =========================
// 🔥 EXPIRE TIME
// =========================
$expire =
    date(
        "Y-m-d H:i:s",
        time() + 600
    );

// =========================
// 🔥 UPDATE DATABASE
// =========================
$update = $conn->prepare("
    UPDATE patient

    SET
        reset_code=?,
        reset_expires=?

    WHERE Email_Address=?
");

$update->bind_param(

    "sss",

    $resetCode,
    $expire,
    $email
);

$update->execute();

// =========================
// 🔥 SEND EMAIL
// =========================
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

    // =========================
    // 🔥 UTF-8
    // =========================
    $mail->CharSet = 'UTF-8';

    // =========================
    // 🔥 INFO
    // =========================
    $mail->setFrom(
        'medino.system@gmail.com',
        'Medino System'
    );

    $mail->addAddress(
        $email,
        $user['Name']
    );

    // =========================
    // 🔥 EMAIL
    // =========================
    $mail->isHTML(true);

    $mail->Subject =
        "Reset Your Password";

    $mail->Body = '

    <div
        style="
            margin:0;
            padding:40px 20px;
            background:#f1f5f9;
            font-family:Arial,sans-serif;
        "
    >

        <div
            style="
                max-width:600px;
                margin:auto;
                background:#ffffff;
                border-radius:28px;
                overflow:hidden;
                box-shadow:0 10px 35px rgba(0,0,0,.08);
            "
        >

            <!-- HEADER -->
            <div
                style="
                    background:linear-gradient(135deg,#1e3a8a,#0ea5e9);
                    padding:45px 30px;
                    text-align:center;
                "
            >

                <div
                    style="
                        width:85px;
                        height:85px;
                        margin:auto;
                        border-radius:50%;
                        background:rgba(255,255,255,.15);
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        font-size:40px;
                        color:#fff;
                    "
                >
                    🔐
                </div>

                <h1
                    style="
                        color:#fff;
                        margin-top:20px;
                        margin-bottom:0;
                        font-size:34px;
                        font-weight:700;
                    "
                >
                    Password Reset
                </h1>

            </div>

            <!-- CONTENT -->
            <div
                style="
                    padding:45px 35px;
                    text-align:center;
                "
            >

                <h2
                    style="
                        margin:0;
                        color:#0f172a;
                        font-size:28px;
                    "
                >
                    Hello '.$user['Name'].'
                </h2>

                <p
                    style="
                        color:#64748b;
                        font-size:17px;
                        margin-top:18px;
                        line-height:1.7;
                    "
                >
                    Use the verification code below
                    to reset your password
                </p>

                <!-- OTP -->
                <div
                    style="
                        margin:40px auto;
                        background:#eff6ff;
                        border:2px dashed #0ea5e9;
                        border-radius:22px;
                        padding:28px 20px;
                        width:fit-content;
                        min-width:260px;
                    "
                >

                    <div
                        style="
                            font-size:56px;
                            letter-spacing:18px;
                            font-weight:700;
                            color:#0284c7;
                            text-align:center;
                        "
                    >
                        '.$resetCode.'
                    </div>

                </div>

                <p
                    style="
                        color:#ef4444;
                        font-size:15px;
                        margin-top:10px;
                        font-weight:600;
                    "
                >
                    This code will expire in 10 minutes
                </p>

                <div
                    style="
                        margin-top:35px;
                        padding-top:25px;
                        border-top:1px solid #e2e8f0;
                        color:#94a3b8;
                        font-size:14px;
                        line-height:1.7;
                    "
                >
                    If you did not request a password reset,
                    please ignore this email.
                </div>

            </div>

        </div>

    </div>

    ';

    // =========================
    // 🔥 SEND
    // =========================
    $mail->send();

    echo json_encode([

        "status"=>"success",

        "message"=>"Reset code sent successfully"
    ]);

}catch(Exception $e){

    echo json_encode([

        "status"=>"error",

        "message"=>"Email failed"
    ]);
}
?>