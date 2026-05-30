<?php

header("Content-Type: application/json");

include "db.php";

$data = [];

/* =========================
   TOTAL BOOKINGS
========================= */

$totalBookings = $conn->query("

SELECT COUNT(*) AS total
FROM booking

")->fetch_assoc();

$data['totalBookings'] =
    $totalBookings['total'];

/* =========================
   COMPLETED BOOKINGS
========================= */

$completed = $conn->query("

SELECT COUNT(*) AS total
FROM booking
WHERE Status='Completed'

")->fetch_assoc();

$data['completed'] =
    $completed['total'];

/* =========================
   CONFIRMED BOOKINGS
========================= */

$confirmed = $conn->query("

SELECT COUNT(*) AS total
FROM booking
WHERE Status='Confirmed'

")->fetch_assoc();

$data['confirmed'] =
    $confirmed['total'];

/* =========================
   CANCELLED BOOKINGS
========================= */

$cancelled = $conn->query("

SELECT COUNT(*) AS total
FROM booking
WHERE Status='Canceled'

")->fetch_assoc();

$data['cancelled'] =
    $cancelled['total'];

/* =========================
   TOTAL PATIENTS
========================= */

$patients = $conn->query("

SELECT COUNT(*) AS total
FROM patient

")->fetch_assoc();

$data['patients'] =
    $patients['total'];

/* =========================
   TOTAL DOCTORS
========================= */

$doctors = $conn->query("

SELECT COUNT(*) AS total
FROM doctor

")->fetch_assoc();

$data['doctors'] =
    $doctors['total'];

/* =========================
   TOTAL PRESCRIPTIONS
========================= */

$prescriptions = $conn->query("

SELECT COUNT(*) AS total
FROM prescriptions

")->fetch_assoc();

$data['prescriptions'] =
    $prescriptions['total'];

/* =========================
   TODAY BOOKINGS
========================= */

$today = $conn->query("

SELECT COUNT(*) AS total
FROM booking
WHERE DATE(Booking_Date)=CURDATE()

")->fetch_assoc();

$data['today'] =
    $today['total'];

/* =========================
   MONTHLY BOOKINGS
========================= */

$months = [];

$monthly = $conn->query("

SELECT

MONTH(Booking_Date) AS month,

COUNT(*) AS total

FROM booking

GROUP BY MONTH(Booking_Date)

ORDER BY month

");

while($m = $monthly->fetch_assoc()){

    $months[] = $m;
}

$data['monthly'] = $months;

/* =========================
   TOP DOCTORS
========================= */

$topDoctors = [];

$doctorQuery = $conn->query("

SELECT

doctor.Name AS doctor_name,

specialty.Name AS specialty_name,

COUNT(booking.Booking_ID) AS total

FROM booking

INNER JOIN doctor
ON doctor.Doctor_ID =
booking.Doctor_ID

INNER JOIN specialty
ON specialty.Specialty_ID =
doctor.Specialty_ID

GROUP BY doctor.Doctor_ID

ORDER BY total DESC

LIMIT 10

");

while($d = $doctorQuery->fetch_assoc()){

    $topDoctors[] = $d;
}

$data['topDoctors'] =
    $topDoctors;

/* =========================
   TOP SPECIALTY
========================= */

$topSpecialtyQuery = $conn->query("

SELECT

specialty.Name AS specialty_name,

COUNT(booking.Booking_ID) AS total

FROM booking

INNER JOIN doctor
ON doctor.Doctor_ID =
booking.Doctor_ID

INNER JOIN specialty
ON specialty.Specialty_ID =
doctor.Specialty_ID

GROUP BY specialty.Specialty_ID

ORDER BY total DESC

LIMIT 1

");

$topSpecialty =
    $topSpecialtyQuery->fetch_assoc();

$data['topSpecialty'] = [

    "name" =>
        $topSpecialty['specialty_name'] ?? "N/A",

    "total" =>
        $topSpecialty['total'] ?? 0

];

/* =========================
   TOP PATIENT
========================= */

$topPatientQuery = $conn->query("

SELECT

patient.Name AS patient_name,

COUNT(booking.Booking_ID) AS total

FROM booking

INNER JOIN patient
ON patient.Patient_ID =
booking.Patient_ID

GROUP BY patient.Patient_ID

ORDER BY total DESC

LIMIT 1

");

$topPatient =
    $topPatientQuery->fetch_assoc();

$data['topPatient'] = [

    "patient_name" =>
        $topPatient['patient_name'] ?? "N/A",

    "total" =>
        $topPatient['total'] ?? 0

];

/* =========================
   ALL BOOKINGS
========================= */

$bookings = [];

$bookingQuery = $conn->query("

SELECT

booking.Booking_ID,

booking.Status,

booking.Booking_Date,

patient.Name AS patient_name,

doctor.Name AS doctor_name

FROM booking

INNER JOIN patient
ON patient.Patient_ID =
booking.Patient_ID

INNER JOIN doctor
ON doctor.Doctor_ID =
booking.Doctor_ID

ORDER BY booking.Booking_Date DESC

");

while($b = $bookingQuery->fetch_assoc()){

    $bookings[] = $b;
}

$data['bookingsList'] =
    $bookings;

/* =========================
   PATIENT BOOKINGS
========================= */

$patientBookings = [];

$patientQuery = $conn->query("

SELECT

patient.Name AS patient_name,

COUNT(booking.Booking_ID)
AS total_bookings,

MAX(booking.Booking_Date)
AS last_booking

FROM booking

INNER JOIN patient
ON patient.Patient_ID =
booking.Patient_ID

GROUP BY patient.Patient_ID

ORDER BY total_bookings DESC

");

while($p = $patientQuery->fetch_assoc()){

    $patientBookings[] = $p;
}

$data['patientBookings'] =
    $patientBookings;

/* =========================
   RETURN JSON
========================= */

echo json_encode($data);

?>