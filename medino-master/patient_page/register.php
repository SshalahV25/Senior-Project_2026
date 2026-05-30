<?php

header("Content-Type: application/json");

// =========================
// 🔥 DB
// =========================
require_once("../php/db.php");

include "cleanup_pending.php";
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

$name =
    trim($data['name'] ?? '');

$email =
    trim($data['email'] ?? '');

$phone =
    trim($data['phone'] ?? '');

$birthdate =
    trim($data['birthdate'] ?? '');

$password =
    trim($data['password'] ?? '');

$gender =
    trim($data['gender'] ?? '');

// =========================
// 🔥 VALIDATION
// =========================
if(

    empty($name)
    ||
    empty($email)
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
// 🔥 EMAIL EXISTS
// =========================
$checkPatient = $conn->prepare("
SELECT Email_Address
FROM patient
WHERE Email_Address=?
");

$checkPatient->bind_param(
"s",
$email
);

$checkPatient->execute();

$resPatient =
$checkPatient->get_result();

if($resPatient->num_rows > 0){

echo json_encode([

    "status"=>"error",

    "message"=>"Email already exists"
]);

exit;

}

// =========================
// CHECK PENDING ACCOUNT
// =========================
$checkPending = $conn->prepare("
SELECT Email_Address
FROM pending_patients
WHERE Email_Address=?
");

$checkPending->bind_param(
"s",
$email
);

$checkPending->execute();

$resPending =
$checkPending->get_result();

if($resPending->num_rows > 0){

echo json_encode([

    "status"=>"error",

    "message"=>"Account pending verification. Please verify your email or resend OTP."
]);

exit;

}

// =========================
// 🔥 PHONE EXISTS
// =========================
if(!empty($phone)){

    $check = $conn->prepare("
    SELECT Phone
    FROM patient
    WHERE Phone=?

    UNION

    SELECT Phone
    FROM pending_patients
    WHERE Phone=?
");

$check->bind_param(
    "ss",
    $phone,
    $phone
);

    $check->execute();

    $res = $check->get_result();

    if($res->num_rows > 0){

        echo json_encode([

            "status"=>"error",

            "message"=>"Phone already exists"
        ]);

        exit;
    }
}

// =========================
// 🔥 HASH PASSWORD
// =========================
$hashed =
    password_hash(
        $password,
        PASSWORD_DEFAULT
    );

// =========================
// 🔥 GENERATE OTP
// =========================
$otp = rand(1000,9999);

// OTP expires after 5 minutes
date_default_timezone_set(
    "Africa/Cairo"
);
$otp_expiry = date(
    "Y-m-d H:i:s",
    strtotime("+5 minutes")
);

// INSERT INTO PENDING TABLE
$stmt = $conn->prepare("
    INSERT INTO pending_patients
    (
        Name,
        Email_Address,
        Phone,
        Data_of_birth,
        Gender,
        Password,
        verification_code,
        otp_expiry
    )

    VALUES
    (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(

    "ssssssss",

    $name,
    $email,
    $phone,
    $birthdate,
    $gender,
    $hashed,
    $otp,
    $otp_expiry
);

// =========================
// 🔥 EXECUTE
// =========================
if($stmt->execute()){

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
            $email,
            $name
        );

        // =========================
        // 🔥 CONTENT
        // =========================
        $mail->isHTML(true);

        $mail->Subject =
            "Verify Your Email";

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
                    max-width:600px;
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
                        Medino Verification
                    </h1>

                </div>

                <!-- CONTENT -->
                <div
                    style="
                        padding:40px;
                        text-align:center;
                    "
                >

                    <h2>
                        Hello '
                        .
                        $name
                        .
                        '
                    </h2>

                    <p>
                        Use this code to
                        verify your account
                    </p>

                    <!-- OTP -->
                    <div
                        style="
                            margin:35px auto;

                            width:220px;

                            background:#f1f5ff;

                            padding:25px;

                            border-radius:18px;

                            font-size:42px;

                            font-weight:bold;

                            letter-spacing:12px;

                            color:#1e3a8a;
                        "
                    >
                        '
                        .
                        $otp
                        .
                        '
                    </div>

                    <p
                        style="
                            color:#64748b;
                        "
                    >
                        This code expires soon.
                    </p>

                </div>

            </div>

        </div>
        ';

        $mail->send();

        echo json_encode([

            "status"=>"success",

            "message"=>"Verification code sent",

            "email"=>$email
        ]);

    }catch(Exception $e){

        echo json_encode([

            "status"=>"error",

            "message"=>"Email failed"
        ]);
    }

}else{

    echo json_encode([

        "status"=>"error",

        "message"=>"Insert failed"
    ]);
}
?>