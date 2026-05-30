/* =========================
   PAGE LOAD
========================= */

document.addEventListener(

    "DOMContentLoaded",

    function(){

        loadDashboard();

    }

);

/* =========================
   LOAD DASHBOARD
========================= */

function loadDashboard(){

    fetch("./php/get_analytics.php")

    .then(res => res.json())

    .then(data => {

        /* =========================
           CARDS
        ========================= */

        document.getElementById(
            "totalBookings"
        ).innerText =
            data.totalBookings;

        document.getElementById(
            "completedBookings"
        ).innerText =
            data.completed;

        document.getElementById(
            "confirmedBookings"
        ).innerText =
            data.confirmed;

        document.getElementById(
            "cancelledBookings"
        ).innerText =
            data.cancelled;

        document.getElementById(
            "totalPatients"
        ).innerText =
            data.patients;

        document.getElementById(
            "totalDoctors"
        ).innerText =
            data.doctors;

            animateCounter(
            "totalBookings",
            data.totalBookings
            );

            animateCounter(
                "completedBookings",
                data.completed
            );

            animateCounter(
                "confirmedBookings",
                data.confirmed
            );

            animateCounter(
                "cancelledBookings",
                data.cancelled
            );

            animateCounter(
                "totalPatients",
                data.patients
            );

            animateCounter(
                "totalDoctors",
                data.doctors
            );

            setTimeout(() => {

            document
            .getElementById("pageLoader")
            .classList
            .add("hide");

            },600);
        /* =========================
        SAVE DATA
        ========================= */

        window.analyticsData = data;

    })

    .catch(err => {

        console.log(err);

    });

}

/* =========================
   OPEN REPORT
========================= */

function openReport(type){

    document
    .getElementById("popupOverlay")
    .classList
    .add("active");

    const title =
        document.getElementById(
            "popupTitle"
        );

    const body =
        document.getElementById(
            "popupBody"
        );

    const data =
        window.analyticsData;

/* =========================
   BOOKINGS ANALYTICS
========================= */

if(type === "bookings"){

    title.innerText =
        "Bookings Analytics";

    let bookingRows = "";

    data.bookingsList.forEach(b => {

        let statusColor = "#999";

        if(b.Status === "Completed"){

            statusColor = "#2ecc71";
        }

        if(b.Status === "Confirmed"){

            statusColor = "#f39c12";
        }

        if(b.Status === "Canceled"){

            statusColor = "#e74c3c";
        }

        bookingRows += `

            <tr>

                <td>
                    #${b.Booking_ID}
                </td>

                <td>
                    ${b.patient_name}
                </td>

                <td>
                    ${b.doctor_name}
                </td>

                <td>

                    <span style="
                        background:${statusColor};
                        color:white;
                        padding:8px 14px;
                        border-radius:20px;
                        font-size:13px;
                        font-weight:bold;
                    ">

                        ${b.Status}

                    </span>

                </td>

                <td>
                    ${b.Booking_Date}
                </td>

            </tr>

        `;
    });

    body.innerHTML = `

        <!-- TOP INFO -->

        <div class="analytics-grid">

            <div class="analytics-card">

                <h3>
                    Total Bookings
                </h3>

                <h1 style="
                    font-size:70px;
                    color:#00c6ff;
                ">

                    ${data.totalBookings}

                </h1>

            </div>

            <div class="analytics-card">

                <h3>
                    Completed Rate
                </h3>

                <h1 style="
                    font-size:60px;
                    color:#2ecc71;
                ">

                    ${Math.round(
                        (data.completed /
                        data.totalBookings) * 100
                    ) || 0}%

                </h1>

            </div>

        </div>
        
            <!-- CHART -->

                <div
                    class="analytics-card"
                    style="margin-top:25px;"
                >

                    <h3 style="
                        margin-bottom:20px;
                    ">

                        Booking Status Overview

                    </h3>

                    <div style="
                        height:350px;
                    ">

                        <canvas
                            id="bookingChart"
                        ></canvas>

                    </div>

                </div>

        <!-- SEARCH -->

        <div style="
            margin-top:25px;
            margin-bottom:15px;
        ">

            <input
                type="text"
                id="bookingSearch"
                placeholder="Search patient or doctor..."
                style="
                    width:100%;
                    padding:14px;
                    border-radius:12px;
                    border:1px solid #ddd;
                    font-size:15px;
                "
            >

        </div>

        <!-- TABLE -->

        <div class="analytics-card">

            <table
                id="bookingsTable"
                style="
                    width:100%;
                    border-collapse:collapse;
                "
            >

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Patient</th>

                        <th>Doctor</th>

                        <th>Status</th>

                        <th>Date</th>

                    </tr>

                </thead>

                <tbody>

                    ${bookingRows}

                </tbody>

            </table>

        </div>

    `;

    /* SEARCH */

    setTimeout(() => {

        document
        .getElementById("bookingSearch")

        .addEventListener(
            "keyup",

            function(){

                let value =
                    this.value.toLowerCase();

                let rows =
                    document.querySelectorAll(
                        "#bookingsTable tbody tr"
                    );

                rows.forEach(row => {

                    row.style.display =

                        row.innerText
                        .toLowerCase()
                        .includes(value)

                        ?

                        ""

                        :

                        "none";

                });

            }
        );

    },100);

    /* =========================
    BOOKINGS CHART
    ========================= */

    const chartCtx =
        document
        .getElementById("bookingChart");

    new Chart(chartCtx, {

        type: "bar",

        data: {

            labels: [

                "Completed",

                "Confirmed",

                "Cancelled"

            ],

            datasets: [{

                label: "Bookings",

                data: [

                    data.completed,

                    data.confirmed,

                    data.cancelled

                ],

                backgroundColor: [

                    "#2ecc71",

                    "#f39c12",

                    "#e74c3c"

                ],

                borderRadius: 10

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    display: false

                }

            },

            scales: {

                y: {

                    beginAtZero: true

                }

            }

        }

    });
}
    /* =========================
   PATIENTS ANALYTICS
========================= */

if(type === "patients"){

    title.innerText =
        "Patients Analytics";

    let patientRows = "";

    data.patientBookings.forEach(p => {

        patientRows += `

            <tr>

                <td>
                    ${p.patient_name}
                </td>

                <td>
                    ${p.total_bookings}
                </td>

                <td>
                    ${p.last_booking}
                </td>

            </tr>

        `;
    });

    body.innerHTML = `

        <!-- TOP INFO -->

        <div class="analytics-grid">

            <div class="analytics-card">

                <h3>
                    Total Patients
                </h3>

                <h1 style="
                    font-size:70px;
                    color:#764ba2;
                    margin-bottom:20px;
                ">

                    ${data.patients}

                </h1>

                <p>
                    Registered patients
                </p>

            </div>

            <div class="analytics-card">

                <h3>
                    Most Active Patient
                </h3>

                <h1 style="
                    font-size:45px;
                    color:#2c3e50;
                    margin-bottom:20px;
                ">

                    ${data.topPatient.patient_name}

                </h1>

                <p>

                    ${data.topPatient.total}

                    bookings

                </p>

            </div>

        </div>

        <!-- TABLE -->

        <div
            class="analytics-card"
            style="margin-top:25px;"
        >

            <h3>
                Patients Activity
            </h3>

            <table style="
                width:100%;
                border-collapse:collapse;
                margin-top:20px;
            ">

                <thead>

                    <tr>

                        <th>
                            Patient
                        </th>

                        <th>
                            Total Bookings
                        </th>

                        <th>
                            Last Booking
                        </th>

                    </tr>

                </thead>

                <tbody>

                    ${patientRows}

                </tbody>

            </table>

        </div>

    `;
}


    /* =========================
   DOCTORS ANALYTICS
========================= */

if(type === "doctors"){

    title.innerText =
        "Doctors Analytics";

    let rows = "";

    let doctorNames = [];

    let bookingCounts = [];

    data.topDoctors.forEach(doc => {

        doctorNames.push(
            doc.doctor_name
        );

        bookingCounts.push(
            doc.total
        );

        rows += `

            <tr>

                <td>
                    ${doc.doctor_name}
                </td>

                <td>
                    ${doc.specialty_name}
                </td>

                <td>

                    <span style="
                        background:#4facfe;
                        color:white;
                        padding:8px 14px;
                        border-radius:20px;
                        font-weight:bold;
                    ">

                        ${doc.total}

                    </span>

                </td>

            </tr>

        `;
    });

    body.innerHTML = `

        <!-- TOP INFO -->

        <div class="analytics-grid">

            <div class="analytics-card">

                <h3>
                    Total Doctors
                </h3>

                <h1 style="
                    font-size:70px;
                    color:#2c3e50;
                ">

                    ${data.doctors}

                </h1>

            </div>

            <div class="analytics-card">

                <h3>
                    Top Doctor
                </h3>

                <h1 style="
                    font-size:40px;
                    color:#4facfe;
                ">

                    ${data.topDoctors[0].doctor_name}

                </h1>

                <p>

                    ${data.topDoctors[0].total}

                    bookings

                </p>

            </div>

        </div>

        <!-- CHART -->

        <div
            class="analytics-card"
            style="margin-top:25px;"
        >

            <h3 style="
                margin-bottom:20px;
            ">

                Doctors Performance

            </h3>

            <div style="
                height:350px;
            ">

                <canvas
                    id="doctorChart"
                ></canvas>

            </div>

        </div>

        <!-- TABLE -->

        <div
            class="analytics-card"
            style="margin-top:25px;"
        >

            <h3>
                Doctors Statistics
            </h3>

            <table style="
                width:100%;
                border-collapse:collapse;
                margin-top:20px;
            ">

                <thead>

                    <tr>

                        <th>
                            Doctor
                        </th>

                        <th>
                            Specialty
                        </th>

                        <th>
                            Total Bookings
                        </th>

                    </tr>

                </thead>

                <tbody>

                    ${rows}

                </tbody>

            </table>

        </div>

    `;

    /* =========================
       DOCTOR CHART
    ========================= */

    setTimeout(() => {

        const ctx =
            document
            .getElementById(
                "doctorChart"
            );

        new Chart(ctx, {

            type: "bar",

            data: {

                labels:
                    doctorNames,

                datasets: [{

                    label:
                        "Bookings",

                    data:
                        bookingCounts,

                    backgroundColor: [

                        "#4facfe",

                        "#43e97b",

                        "#fa709a",

                        "#ff758c",

                        "#667eea",

                        "#00c6ff"

                    ],

                    borderRadius: 10

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        display: false

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true

                    }

                }

            }

        });

    },100);

}

/* =========================
   COMPLETED BOOKINGS
========================= */

if(type === "completed"){

    title.innerText =
        "Completed Analytics";

    let completedRows = "";

    data.bookingsList
    .filter(b => b.Status === "Completed")

    .forEach(b => {

        completedRows += `

            <tr>

                <td>
                    #${b.Booking_ID}
                </td>

                <td>
                    ${b.patient_name}
                </td>

                <td>
                    ${b.doctor_name}
                </td>

                <td>
                    ${b.Booking_Date}
                </td>

                <td>

                    <span style="
                        color:#2ecc71;
                        font-weight:bold;
                    ">

                        Completed

                    </span>

                </td>

            </tr>

        `;
    });

    body.innerHTML = `

        <div class="analytics-grid">

            <div class="analytics-card">

                <h3>
                    Completed Bookings
                </h3>

                <h1 style="
                    font-size:70px;
                    color:#2ecc71;
                    margin-bottom:20px;
                ">

                    ${data.completed}

                </h1>

                <p>
                    Successfully completed appointments
                </p>

            </div>

            <div class="analytics-card">

                <h3>
                    Completion Rate
                </h3>

                <h1 style="
                    font-size:60px;
                    color:#4facfe;
                ">

                    ${Math.round(
                        (data.completed /
                        data.totalBookings) * 100
                    ) || 0}%

                </h1>

                <p>
                    From total bookings
                </p>

            </div>

        </div>

        <!-- TABLE -->

        <div
            class="analytics-card"
            style="margin-top:25px;"
        >

            <h3>
                Completed Patients
            </h3>

            <table style="
                width:100%;
                border-collapse:collapse;
                margin-top:20px;
            ">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Patient</th>

                        <th>Doctor</th>

                        <th>Date</th>

                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    ${completedRows}

                </tbody>

            </table>

        </div>

    `;
}

    /* =========================
   CONFIRMED ANALYTICS
========================= */

if(type === "confirmed"){

    title.innerText =
        "Confirmed Analytics";

    let confirmedRows = "";

    data.bookingsList
    .filter(b => b.Status === "Confirmed")

    .forEach(b => {

        confirmedRows += `

            <tr>

                <td>
                    #${b.Booking_ID}
                </td>

                <td>
                    ${b.patient_name}
                </td>

                <td>
                    ${b.doctor_name}
                </td>

                <td>
                    ${b.Booking_Date}
                </td>

                <td>

                    <span style="
                        background:#f39c12;
                        color:white;
                        padding:8px 14px;
                        border-radius:20px;
                        font-weight:bold;
                    ">

                        Confirmed

                    </span>

                </td>

            </tr>

        `;
    });

    body.innerHTML = `

        <!-- TOP INFO -->

        <div class="analytics-grid">

            <div class="analytics-card">

                <h3>
                    Confirmed Bookings
                </h3>

                <h1 style="
                    font-size:70px;
                    color:#f39c12;
                ">

                    ${data.confirmed}

                </h1>

            </div>

            <div class="analytics-card">

                <h3>
                    Confirmation Rate
                </h3>

                <h1 style="
                    font-size:60px;
                    color:#4facfe;
                ">

                    ${Math.round(
                        (data.confirmed /
                        data.totalBookings) * 100
                    ) || 0}%

                </h1>

            </div>

        </div>

        <!-- CHART -->

        <div
            class="analytics-card"
            style="margin-top:25px;"
        >

            <h3 style="
                margin-bottom:20px;
            ">

                Confirmed vs Others

            </h3>

            <div style="
                height:320px;
            ">

                <canvas
                    id="confirmedChart"
                ></canvas>

            </div>

        </div>

        <!-- TABLE -->

        <div
            class="analytics-card"
            style="margin-top:25px;"
        >

            <h3>
                Confirmed Appointments
            </h3>

            <table style="
                width:100%;
                border-collapse:collapse;
                margin-top:20px;
            ">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Patient</th>

                        <th>Doctor</th>

                        <th>Date</th>

                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    ${confirmedRows}

                </tbody>

            </table>

        </div>

    `;

    /* =========================
       CHART
    ========================= */

    setTimeout(() => {

        const ctx =
            document
            .getElementById(
                "confirmedChart"
            );

        new Chart(ctx, {

            type: "doughnut",

            data: {

                labels: [

                    "Confirmed",

                    "Other"

                ],

                datasets: [{

                    data: [

                        data.confirmed,

                        data.totalBookings -
                        data.confirmed

                    ],

                    backgroundColor: [

                        "#f39c12",

                        "#ecf0f1"

                    ]

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false

            }

        });

    },100);

}

    /* =========================
   CANCELLED ANALYTICS
========================= */

if(type === "cancelled"){

    title.innerText =
        "Cancelled Analytics";

    let cancelledRows = "";

    data.bookingsList
    .filter(b => b.Status === "Canceled")

    .forEach(b => {

        cancelledRows += `

            <tr>

                <td>
                    #${b.Booking_ID}
                </td>

                <td>
                    ${b.patient_name}
                </td>

                <td>
                    ${b.doctor_name}
                </td>

                <td>
                    ${b.Booking_Date}
                </td>

                <td>

                    <span style="
                        background:#e74c3c;
                        color:white;
                        padding:8px 14px;
                        border-radius:20px;
                        font-weight:bold;
                    ">

                        Cancelled

                    </span>

                </td>

            </tr>

        `;
    });

    body.innerHTML = `

        <!-- TOP INFO -->

        <div class="analytics-grid">

            <div class="analytics-card">

                <h3>
                    Cancelled Bookings
                </h3>

                <h1 style="
                    font-size:70px;
                    color:#e74c3c;
                ">

                    ${data.cancelled}

                </h1>

            </div>

            <div class="analytics-card">

                <h3>
                    Cancellation Rate
                </h3>

                <h1 style="
                    font-size:60px;
                    color:#4facfe;
                ">

                    ${Math.round(
                        (data.cancelled /
                        data.totalBookings) * 100
                    ) || 0}%

                </h1>

            </div>

        </div>

        <!-- CHART -->

        <div
            class="analytics-card"
            style="margin-top:25px;"
        >

            <h3 style="
                margin-bottom:20px;
            ">

                Cancelled vs Others

            </h3>

            <div style="
                height:320px;
            ">

                <canvas
                    id="cancelledChart"
                ></canvas>

            </div>

        </div>

        <!-- TABLE -->

        <div
            class="analytics-card"
            style="margin-top:25px;"
        >

            <h3>
                Cancelled Appointments
            </h3>

            <table style="
                width:100%;
                border-collapse:collapse;
                margin-top:20px;
            ">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Patient</th>

                        <th>Doctor</th>

                        <th>Date</th>

                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    ${cancelledRows}

                </tbody>

            </table>

        </div>

    `;

    /* =========================
       CHART
    ========================= */

    setTimeout(() => {

        const ctx =
            document
            .getElementById(
                "cancelledChart"
            );

        new Chart(ctx, {

            type: "pie",

            data: {

                labels: [

                    "Cancelled",

                    "Other"

                ],

                datasets: [{

                    data: [

                        data.cancelled,

                        data.totalBookings -
                        data.cancelled

                    ],

                    backgroundColor: [

                        "#e74c3c",

                        "#ecf0f1"

                    ]

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false

            }

        });

    },100);

}

}

/* =========================
   CLOSE POPUP
========================= */

function closePopup(){

    document
    .getElementById("popupOverlay")
    .classList
    .remove("active");

}

/* =========================
   BOOKING CHART
========================= */

function createBookingChart(data){

    const ctx =
        document.getElementById(
            "bookingChart"
        );

    new Chart(ctx, {

        type: "doughnut",

        data: {

            labels: [

                "Completed",

                "Confirmed",

                "Cancelled"

            ],

            datasets: [{

                data: [

                    data.completed,

                    data.confirmed,

                    data.cancelled

                ],

                backgroundColor: [

                    "#2ecc71",

                    "#f39c12",

                    "#e74c3c"

                ]

            }]

        }

    });

}

/* =========================
   DOWNLOAD PDF
========================= */

function downloadPDF(){

    window.print();

}

/* =========================
   LOGOUT
========================= */

function logout(){

    localStorage.removeItem("admin");

    window.location.href =
        "login.html";

}

/* =========================
   NAVIGATION
========================= */

function go(page){

    window.location.href = page;

}

/* =========================
   DOWNLOAD PDF
========================= */

function downloadReport(){

    const popupTitle =
        document.getElementById(
            "popupTitle"
        ).innerHTML;

    const popupContent =
        document.getElementById(
            "popupContent"
        ).innerHTML;

    /* =========================
       OPEN NEW WINDOW
    ========================= */

    let printWindow =
        window.open(
            '',
            '',
            'width=1400,height=900'
        );

    /* =========================
       WRITE HTML
    ========================= */

    printWindow.document.write(`

    <html>

    <head>

        <title>
            Medical Report
        </title>

        <script src="
https://cdn.jsdelivr.net/npm/chart.js
        "><\/script>

        <style>

            body{

                font-family: Arial;

                background:#fff;

                padding:40px;

                color:#222;
            }

            h1{

                font-size:42px;

                margin-bottom:10px;

                color:#2c3e50;
            }

            .date{

                color:#777;

                margin-bottom:40px;
            }

            .section{

                background:#f8f9fc;

                border-radius:20px;

                padding:30px;

                margin-bottom:30px;
            }

            .analytics-grid{

                display:grid;

                grid-template-columns:
                    repeat(2,1fr);

                gap:25px;

                margin-bottom:30px;
            }

            .mini-box{

                background:#fff;

                padding:25px;

                border-radius:18px;

                box-shadow:
                0 5px 15px rgba(0,0,0,0.05);
            }

            .mini-box h2{

                font-size:50px;

                margin:15px 0;
            }

            table{

                width:100%;

                border-collapse:collapse;

                margin-top:20px;
            }

            table th{

                background:#eef2ff;

                padding:15px;
            }

            table td{

                padding:15px;

                border-bottom:
                1px solid #eee;

                text-align:center;
            }

            canvas{

                max-width:100% !important;

                height:350px !important;
            }

            @media print{

                body{

                    zoom:0.9;
                }
            }

        </style>

    </head>

    <body>

        <h1>
            ${popupTitle}
        </h1>

        <div class="date">

            ${new Date().toLocaleString()}

        </div>

        <div class="section">

            ${popupContent}

        </div>

    </body>

    </html>

    `);

    printWindow.document.close();

    /* =========================
       WAIT RENDER
    ========================= */

        setTimeout(() => {

        printWindow.document.body.style.overflow =
            "visible";

        printWindow.print();

    }, 1000);

}

/* =========================
   ANIMATE COUNTER
========================= */

function animateCounter(id, target){

    let element =
        document.getElementById(id);

    let current = 0;

    let increment =
        target / 40;

    let interval = setInterval(() => {

        current += increment;

        if(current >= target){

            current = target;

            clearInterval(interval);
        }

        element.innerText =
            Math.floor(current);

    },20);

}