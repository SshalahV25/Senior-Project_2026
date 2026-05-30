<?php
include "db.php";

$id = $_GET['id'];

// تأكد إن مفيش دكاترة مرتبطين
$check = $conn->query("
SELECT * FROM DOCTOR WHERE Specialty_ID = $id
");

if($check->num_rows > 0){
    echo "Cannot delete, specialty used by doctors ❌";
    exit;
}

$conn->query("DELETE FROM SPECIALTY WHERE Specialty_ID = $id");

echo "Deleted Successfully ✅";
?>