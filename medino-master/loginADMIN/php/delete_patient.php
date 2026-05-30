<?php
include "db.php";

$id = $_GET['id'];

$conn->query("DELETE FROM PATIENT WHERE Patient_ID=$id");

echo "Deleted Successfully ❌";
?>