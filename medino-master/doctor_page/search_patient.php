<?php

header("Content-Type: application/json");

include "../php/db.php";

// =========================
// 🔥 GET SEARCH
// =========================
$search =
    trim($_GET['search'] ?? '');

// =========================
// 🔥 VALIDATION
// =========================
if(empty($search)){

    echo json_encode([]);
    exit;
}

// =========================
// 🔥 SEARCH QUERY
// =========================
$sql = "
SELECT
    Patient_ID,
    Name,
    Phone,
    Email_Address

FROM patient

WHERE
    Name LIKE ?
    OR Phone LIKE ?

LIMIT 10
";

$stmt = $conn->prepare($sql);

$like =
    "%" . $search . "%";

$stmt->bind_param(
    "ss",
    $like,
    $like
);

$stmt->execute();

$result =
    $stmt->get_result();

// =========================
// 🔥 RESULTS
// =========================
$patients = [];

while($row = $result->fetch_assoc()){

    $patients[] = $row;
}

echo json_encode($patients);

?>