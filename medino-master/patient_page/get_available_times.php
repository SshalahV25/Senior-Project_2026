<?php

header("Content-Type: application/json");

include "../php/db.php";

// =========================
// 🟢 READ DATA
// =========================
$data = json_decode(
    file_get_contents("php://input"),
    true
);

if (
    !isset($data['doctor_id'])
    ||
    !isset($data['date'])
) {
    echo json_encode([
        "error" => "Missing data"
    ]);
    exit;
}

$doctor_id = $data['doctor_id'];
$date = $data['date'];

// =========================
// 🟢 GET DAY NAME
// =========================
$dayName = date(
    "l",
    strtotime($date)
);

// =========================
// 🟢 GET WORK DAYS
// =========================
$sql = "
SELECT
    Start_Time,
    End_Time

FROM work_days

WHERE Doctor_ID = ?
AND Work_Day = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "is",
    $doctor_id,
    $dayName
);

$stmt->execute();

$result = $stmt->get_result();

// =========================
// 🟢 GET BOOKED TIMES
// =========================
$bookedTimes = [];

$sql2 = "
SELECT
    TIME(Booking_Date) as booked_time

FROM booking

WHERE Doctor_ID = ?
AND DATE(Booking_Date) = ?
AND Status IN ('Confirmed','Completed')
";

$stmt2 = $conn->prepare($sql2);

$stmt2->bind_param(
    "is",
    $doctor_id,
    $date
);

$stmt2->execute();

$res2 = $stmt2->get_result();

while ($row2 = $res2->fetch_assoc()) {

    $bookedTimes[] =
        substr(
            $row2['booked_time'],
            0,
            5
        );
}

// =========================
// 🟢 GENERATE SLOTS
// =========================
$slots = [];

while ($row = $result->fetch_assoc()) {

    $startTime = $row['Start_Time'];
    $endTime = $row['End_Time'];

    // 🟢 تاريخ البداية
    $start = strtotime(
        $date . " " . $startTime
    );

    // 🟢 تاريخ النهاية
    $end = strtotime(
        $date . " " . $endTime
    );

    // =========================
    // 🔥 لو النهاية أقل من البداية
    // معناها اليوم اللي بعده
    // مثال:
    // 23:00 → 00:00
    // =========================
    if ($end <= $start) {

        $end = strtotime(
            "+1 day",
            $end
        );
    }

    // =========================
    // 🟢 CREATE 15 MIN SLOTS
    // =========================
    while ($start < $end) {

        $time = date(
            "H:i",
            $start
        );

        // 🟢 استبعاد المحجوز
        if (
            !in_array(
                $time,
                $bookedTimes
            )
        ) {

            $slots[] = $time;
        }

        $start = strtotime(
            "+15 minutes",
            $start
        );
    }
}

// =========================
// 🟢 REMOVE DUPLICATES
// =========================
$slots = array_values(
    array_unique($slots)
);

// =========================
// 🟢 SORT
// =========================
sort($slots);

// =========================
// 🟢 لو مفيش أوقات
// =========================
if (empty($slots)) {

    $nextDate = date(
        "Y-m-d",
        strtotime(
            "+7 days",
            strtotime($date)
        )
    );

    echo json_encode([

        "next_week" => true,

        "next_date" => $nextDate
    ]);

    exit;
}

// =========================
// 🟢 OUTPUT
// =========================
echo json_encode($slots);

exit;

?>