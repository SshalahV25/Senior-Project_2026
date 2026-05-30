<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

include("db.php");

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
$data = json_decode(
    file_get_contents("php://input"),
    true
);

$username =
    trim($data['username'] ?? '');

$password =
    trim($data['password'] ?? '');

// =========================
// 🔴 VALIDATION
// =========================
if(
    empty($username)
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
// 🔥 GET ADMIN
// =========================
$sql = "

SELECT *

FROM admin

WHERE User_Name = ?

LIMIT 1

";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "s",
    $username
);

$stmt->execute();

$result =
    $stmt->get_result();

// =========================
// 🔴 NOT FOUND
// =========================
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
// 🔥 VERIFY PASSWORD
// =========================
if(

    !password_verify(
        $password,
        $admin['Password']
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
    $admin['Admin_ID']
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

    // FROM
    $mail->setFrom(
        'medino.system@gmail.com',
        'Medino Security'
    );

    // TO
    $mail->addAddress(
        $admin['Email_Address'],
        $admin['User_Name']
    );

    // EMAIL
    $mail->isHTML(true);

    $mail->Subject =
        "Your OTP Code";

    $mail->Body = '

    <div
        style="
            background:#f1f5f9;
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
                overflow:hidden;
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

                    padding:30px;
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

                <h2>
                    Hello '.$admin['User_Name'].'
                </h2>

                <p>
                    Use this OTP to complete login
                </p>

                <div
                    style="
                        font-size:42px;
                        letter-spacing:10px;
                        color:#1e3a8a;
                        font-weight:bold;
                        margin:35px 0;
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
                    This OTP expires in 5 minutes
                </p>

            </div>

        </div>

    </div>
    ';

    $mail->send();

}catch(Exception $e){

    echo json_encode([

        "status"=>"error",

        "message"=>"Email failed"
    ]);

    exit;
}

// =========================
// ✅ SUCCESS
// =========================
echo json_encode([

    "status"=>"success",

    "admin_id"=>
        $admin['Admin_ID']
]);
?>