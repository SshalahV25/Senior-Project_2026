<?php
include "db.php";

$id   = $_POST['id'];
$name = $_POST['name'];
$desc = $_POST['desc'];

$conn->query("
UPDATE SPECIALTY 
SET Name='$name', Description='$desc'
WHERE Specialty_ID=$id
");

echo "Updated Successfully ✅";
?>