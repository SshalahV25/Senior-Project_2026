<?php

include "db.php";

// =========================
// 🟢 READ DATA
// =========================
$name       = $_POST['name'];
$email      = $_POST['email'];
$phone      = $_POST['phone'];
$gender     = $_POST['gender'];
$password   = $_POST['password'];


// =========================
// 🔥 HASH PASSWORD
// =========================
$hashedPassword =
    password_hash(
        $password,
        PASSWORD_DEFAULT
    );

$birth_date = $_POST['birth_date'];
$specialty  = $_POST['specialty'];


// =========================
// 🟢 VALIDATION
// =========================
if(
    empty($name)
    ||
    empty($email)
    ||
    empty($phone)
    ||
    empty($gender)
    ||
    empty($password)
    ||
    empty($birth_date)
    ||
    empty($specialty)
){
    echo "missing";
    exit;
}


// =========================
// 🟢 CHECK EMAIL
// =========================
$checkEmail = $conn->query("

SELECT Doctor_ID

FROM doctor

WHERE Email_Address='$email'

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

");

if($checkPhone->num_rows > 0){

    echo "phone_exists";

    exit;
}


// =========================
// 🟢 INSERT DOCTOR
// =========================
$conn->query("

INSERT INTO doctor
(
    Name,
    Email_Address,
    Phone,
    Gender,
    Specialty_ID,
    Password,
    Date_Of_Birth
)

VALUES
(
    '$name',
    '$email',
    '$phone',
    '$gender',
    '$specialty',
    '$hashedPassword',
    '$birth_date'
)

");

echo "success";

?>