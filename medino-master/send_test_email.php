<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// =========================
// 🔥 LOAD PHPMailer
// =========================
require 'PHPMailer-master/src/Exception.php';

require 'PHPMailer-master/src/PHPMailer.php';

require 'PHPMailer-master/src/SMTP.php';

// =========================
// 🔥 CREATE MAIL
// =========================
$mail = new PHPMailer(true);

try{

    // =========================
    // 🔥 SERVER SETTINGS
    // =========================
    $mail->isSMTP();

    $mail->Host = 'smtp.gmail.com';

    $mail->SMTPAuth = true;

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

    // 🔥 SEND TO
    $mail->addAddress(
        'm01019501566m@gmail.com'
    );

    // =========================
    // 🔥 CONTENT
    // =========================
    $mail->isHTML(true);

    $mail->Subject =
        'Medino Test Email';

    $mail->Body = '
        <h2>
            ✅ Email Working Successfully
        </h2>

        <p>
            Your Medino reminder system
            is now connected.
        </p>
    ';

    // =========================
    // 🔥 SEND
    // =========================
    $mail->send();

    echo "
        ✅ Email Sent Successfully
    ";

}catch(Exception $e){

    echo "
        ❌ Email Failed:
        {$mail->ErrorInfo}
    ";
}
?>