<?php

include "db.php";

// =========================
// 🟢 READ DATA
// =========================
$id         = $_POST['id'];
$name       = $_POST['name'];
$email      = $_POST['email'];
$phone      = $_POST['phone'];
$gender     = $_POST['gender'];
$birth_date = $_POST['birth_date'];
$specialty  = $_POST['specialty'];

// =========================
// 🟢 CHECK EMAIL
// =========================
$checkEmail = $conn->query("

SELECT Doctor_ID

FROM doctor

WHERE Email_Address='$email'

AND Doctor_ID != '$id'

");

if($checkEmail->num_rows > 0){

    echo "email_exists";

    exit;
}

// =========================
// 🟢 CHECK PHONE
// =========================
$checkPhone = $conn->query("

SELECT Doctor_ID

FROM doctor

WHERE Phone='$phone'

AND Doctor_ID != '$id'

");

if($checkPhone->num_rows > 0){

    echo "phone_exists";

    exit;
}

// =========================
// 🟢 UPDATE DOCTOR
// =========================
$conn->query("

UPDATE doctor SET

Name='$name',

Email_Address='$email',

Phone='$phone',

Gender='$gender',

Date_Of_Birth='$birth_date',

Specialty_ID='$specialty'

WHERE Doctor_ID='$id'

");

echo "success";

?>