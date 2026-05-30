<?php

include "../php/db.php";

$conn->query("
    DELETE FROM pending_patients
    WHERE otp_expiry < NOW()
");
?>