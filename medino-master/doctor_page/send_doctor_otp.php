<?php

header("Content-Type: application/json");

include("../php/db.php");

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

$phone =
    trim($data['phone'] ?? '');

$password =
    trim($data['password'] ?? '');

// =========================
// 🔴 VALIDATION
// =========================
if(
    empty($phone)
    ||
    empty($password)
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Missing data"
    ]);

    exit;
}

// =========================
// 🔥 GET DOCTOR
// =========================
$sql = "

SELECT *

FROM doctor

WHERE Phone = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "s",
    $phone
);

$stmt->execute();

$result =
    $stmt->get_result();

if($result->num_rows === 0){

    echo json_encode([

        "status"=>"error",

        "message"=>"Doctor not found"
    ]);

    exit;
}

$doctor =
    $result->fetch_assoc();

// =========================
// 🔥 VERIFY PASSWORD
// =========================
if(
    !password_verify(
        $password,
        $doctor['Password']
    )
){

    echo json_encode([

        "status"=>"error",

        "message"=>"Wrong password"
    ]);

    exit;
}

// =========================
// 🔥 GENERATE OTP
// =========================
$otp =
    rand(100000,999999);

// =========================
// 🔥 EXPIRE
// =========================
$expire =
    date(
        "Y-m-d H:i:s",
        strtotime("+5 minutes")
    );

// =========================
// 🔥 SAVE OTP
// =========================
$update = $conn->prepare("

UPDATE doctor

SET
    otp_code = ?,
    otp_expire = ?

WHERE Doctor_ID = ?

");

$update->bind_param(
    "ssi",
    $otp,
    $expire,
    $doctor['Doctor_ID']
);

$update->execute();

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

    $mail->CharSet = 'UTF-8';

    // =========================
    // 🔥 FROM
    // =========================
    $mail->setFrom(
        'medino.system@gmail.com',
        'Medino Security'
    );

    // =========================
    // 🔥 TO
    // =========================
    $mail->addAddress(
        $doctor['Email_Address'],
        $doctor['Name']
    );

    // =========================
    // 🔥 EMAIL
    // =========================
    $mail->isHTML(true);

    $mail->Subject =
        "Your Login OTP Code";

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
                max-width:600px;
                margin:auto;
                background:#fff;
                border-radius:24px;
                overflow:hidden;
                box-shadow:0 10px 40px rgba(0,0,0,.08);
            "
        >

            <div
                style="
                    background:
                    linear-gradient(
                        135deg,
                        #1e3a8a,
                        #0ea5e9
                    );
                    padding:35px;
                    text-align:center;
                "
            >

                <h1
                    style="
                        color:#fff;
                        margin:0;
                    "
                >
                    🔐 OTP Verification
                </h1>

            </div>

            <div
                style="
                    padding:40px;
                    text-align:center;
                "
            >

                <p
                    style="
                        font-size:18px;
                        color:#334155;
                    "
                >
                    Hello '.$doctor['Name'].'
                </p>

                <p
                    style="
                        color:#64748b;
                        line-height:28px;
                    "
                >
                    Use this OTP code to complete login:
                </p>

                <div
                    style="
                        margin:35px auto;
                        background:#eff6ff;
                        color:#1e3a8a;
                        font-size:42px;
                        font-weight:bold;
                        letter-spacing:8px;
                        padding:25px;
                        border-radius:18px;
                        width:max-content;
                    "
                >
                    '.$otp.'
                </div>

                <p
                    style="
                        color:#ef4444;
                        font-weight:600;
                    "
                >
                    This code expires in 5 minutes
                </p>

            </div>

        </div>

    </div>
    ';

    $mail->send();

}catch(Exception $e){

    echo json_encode([

        "status"=>"error",

        "message"=>"Failed to send OTP"
    ]);

    exit;
}
// =========================
// 🔥 SUCCESS
// =========================
echo json_encode([

    "status"=>"success",

    "doctor_id"=>
        $doctor['Doctor_ID']
]);

?>