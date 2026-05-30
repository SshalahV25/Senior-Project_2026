<?php
include "db.php";

$doctor_id = $_GET['doctor_id'];
$day = $_GET['day'];

$conn->query("
DELETE FROM WORK_DAYS 
WHERE Doctor_ID=$doctor_id AND Work_Day='$day'
");

echo "Work Day Deleted ✅";
?>