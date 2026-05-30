<?php
include "db.php";

$name = $_POST['name'];
$desc = $_POST['desc'];

$conn->query("
INSERT INTO SPECIALTY (Name, Description)
VALUES ('$name', '$desc')
");

echo "Added Successfully ✅";
?>