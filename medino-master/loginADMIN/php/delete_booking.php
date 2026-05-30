<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

$id = $_GET['id'] ?? '';

if(empty($id)){

    echo "invalid";

    exit;
}

/*************************
 * DELETE BOOKING
 *************************/
$sql = "

DELETE FROM booking

WHERE Booking_ID = '$id'

";

if($conn->query($sql)){

    echo "success";

}else{

    echo "error";
}

?>