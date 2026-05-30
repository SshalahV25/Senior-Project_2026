<?php

include "db.php";

// =========================
// 🟢 READ DATA
// =========================
$id    = $_POST['id'];
$name  = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$dob   = $_POST['dob'];

// =========================
// 🟢 CHECK EMAIL
// =========================
$checkEmail = $conn->query("

SELECT Patient_ID

FROM patient

WHERE Email_Address='$email'

AND Patient_ID != '$id'

");

if($checkEmail->num_rows > 0){

    echo "email_exists";

    exit;
}

// =========================
// 🟢 CHECK PHONE
// =========================
$checkPhone = $conn->query("

SELECT Patient_ID

FROM patient

WHERE Phone='$phone'

AND Patient_ID != '$id'

");

if($checkPhone->num_rows > 0){

    echo "phone_exists";

    exit;
}

// =========================
// 🟢 UPDATE
// =========================
$conn->query("

UPDATE patient SET

Name='$name',

Email_Address='$email',

Phone='$phone',

Data_of_birth='$dob'

WHERE Patient_ID='$id'

");

echo "success";

?>