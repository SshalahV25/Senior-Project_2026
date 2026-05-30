function login(e){

e.preventDefault();

let username =
    document.getElementById(
        "username"
    ).value.trim();

let password =
    document.getElementById(
        "password"
    ).value.trim();

let msg =
    document.getElementById(
        "msg"
    );

// VALIDATION
if(!username || !password){

    msg.style.color = "red";

    msg.innerText =
        "Enter username and password";

    return;
}

// LOADING
msg.style.color =
    "#2563eb";

msg.innerText =
    "Checking...";

// REQUEST
fetch(
    "php/send_admin_otp.php",
    {

        method:"POST",

        headers:{
            "Content-Type":"application/json"
        },

        body: JSON.stringify({

            username,
            password
        })
    }
)

.then(res=>res.json())

.then(data=>{

    console.log(
        "LOGIN RESPONSE:",
        data
    );

    // SUCCESS
    if(data.status === "success"){

        // SAVE TEMP ADMIN ID
        sessionStorage.setItem(
            "otp_admin_id",
            data.admin_id
        );

        // GO OTP PAGE
        window.location.href =
            "verify_admin_otp.html";

    }else{

        msg.style.color =
            "red";

        msg.innerText =
            data.message;
    }

})

.catch(err=>{

    console.log(
        "LOGIN ERROR:",
        err
    );

    msg.style.color =
        "red";

    msg.innerText =
        "Server Error";
});

}

// =========================
// 🔒 PROTECT PAGES
// =========================
if(

sessionStorage.getItem(
    "admin_logged_in"
) !== "true"

&&

!window.location.pathname.includes(
    "login.html"
)

&&

!window.location.pathname.includes(
    "verify_admin_otp.html"
)

){

window.location.replace(
    "login.html"
);

}

// =========================
// 🔥 GO
// =========================
function go(page){

window.location.href = page;

}

function logout() {

    // 🔥 نمسح تسجيل الدخول
    sessionStorage.removeItem("admin_logged_in");

    // 🔥 نمنع الرجوع
    window.location.replace("login.html");
}

//منع زرار Back (اختياري بس مفيد)
window.history.pushState(null, null, window.location.href);

window.onpopstate = function () {
    if (sessionStorage.getItem("admin_logged_in") !== "true") {
        window.location.replace("login.html");
    }
};

//تحميل التخصصات من الداتا بيز
function loadSpecialties() {

    fetch("php/get_specialties.php")
        .then(res => res.json())
        .then(data => {

            let select = document.getElementById("specialty");

            data.forEach(item => {
                let option = document.createElement("option");
                option.value = item.Specialty_ID;
                option.text = item.Name;
                select.appendChild(option);
            });

        });
}


// ================== MESSAGE ==================
function showMessage(msg, type = "success") {

    let box = document.getElementById("msgBox");

    box.innerText = msg;
    box.className = type;
    box.style.display = "block";

    setTimeout(() => {
        box.style.display = "none";
    }, 3000);
}


// ================== CONFIRM POPUP ==================
let confirmCallback = null;

function showConfirm(message, callback) {

    document.getElementById("confirmText").innerText = message;
    document.getElementById("confirmBox").style.display = "flex";


    confirmCallback = callback;
}

function confirmYes() {
    document.getElementById("confirmBox").style.display = "none";
    if (confirmCallback) confirmCallback(true);
}

function confirmNo() {
    document.getElementById("confirmBox").style.display = "none";
    if (confirmCallback) confirmCallback(false);
}


// ================== LOAD DOCTORS ==================
function loadDoctors() {

    const table = document.getElementById("doctorTable");

    if (!table) return;

    fetch("php/get_doctors.php")

    .then(res => res.json())

    .then(data => {

        table.innerHTML = "";

        data.forEach(doc => {

            // =========================
            // 🔥 CALCULATE AGE
            // =========================
            let age = "";

            if(doc.Date_Of_Birth){

                let birth =
                    new Date(doc.Date_Of_Birth);

                let today =
                    new Date();

                age =
                    today.getFullYear()
                    -
                    birth.getFullYear();

                let month =
                    today.getMonth()
                    -
                    birth.getMonth();

                if(
                    month < 0
                    ||
                    (
                        month === 0
                        &&
                        today.getDate()
                        <
                        birth.getDate()
                    )
                ){

                    age--;
                }
            }

            table.innerHTML += `

            <tr>

                <td>${doc.Name}</td>

                <td>${doc.Email_Address}</td>

                <td>${doc.Phone}</td>

                <td>${doc.Gender ?? ''}</td>

                <td>${doc.specialty_name ?? ''}</td>

                <td>${age}</td>

                <td>
                

                <button onclick="openSchedule(${doc.Doctor_ID})">

                    Manage Schedule

                </button>

                    <button onclick="editDoctor(
                        ${doc.Doctor_ID},
                        '${doc.Name}',
                        '${doc.Email_Address}',
                        '${doc.Phone}',
                        '${doc.Gender}',
                        '${doc.Date_Of_Birth ?? ""}',
                        '${doc.Specialty_ID}',
                    )">

                        Edit

                    </button>


                    <button onclick="deleteDoctor(
                        ${doc.Doctor_ID}
                    )">

                        Delete Doctor

                    </button>

                </td>

            </tr>

            `;
        });

    })

    .catch(() => {

        console.log("Doctors load failed");

        showMessage(
            "Error loading doctors ❌",
            "error"
        );

    });
}


// ================== DELETE DOCTOR ==================
function deleteDoctor(id) {

    showConfirm("Are you sure you want to delete this doctor?", function (result) {

        if (!result) return;

        fetch("php/delete_doctor.php?id=" + id)
            .then(res => res.text())
            .then(() => {
                showMessage("Doctor deleted ✅", "success");
                loadDoctors();
            })
            .catch(() => showMessage("Delete failed ❌", "error"));

    });
}


// ================== DELETE WORK DAY ==================
function deleteWorkDay(doctor_id, day) {

    showConfirm(`Delete ${day}?`, function (result) {

        if (!result) return;

        fetch(`php/delete_workday.php?doctor_id=${doctor_id}&day=${day}`)
            .then(res => res.text())
            .then(() => {
                showMessage("Day deleted ✅", "success");
                loadDoctors();
            })
            .catch(() => showMessage("Delete failed ❌", "error"));

    });
}


// ================== EDIT ==================
let oldDoctorData = {};

function editDoctor(
    id,
    name,
    email,
    phone,
    gender,
    birth_date,
    specialty,
) {

    oldDoctorData = { name, email, phone, gender, birth_date, specialty };

    document.getElementById("doctor_id").value = id;
    document.getElementById("name").value = name;
    document.getElementById("email").value = email;
    document.getElementById("phone").value = phone;
    document.getElementById("gender").value = gender;
    document.getElementById("birth_date").value = birth_date;
    document.getElementById("specialty").value = specialty;
    // 🔥 اخفاء ومسح الباسورد
        const passwordInput =
            document.getElementById("password");

        passwordInput.value = "";

        passwordInput.style.display = "none";

    document.getElementById("saveBtn").innerText = "Update Doctor";
    document.getElementById("saveBtn").classList.add("edit-mode");
}


// ================== LOAD ON START ==================
document.addEventListener("DOMContentLoaded", function () {

    // Doctors page
    if (document.getElementById("doctorTable")) {
        loadSpecialties();
        loadDoctors();
    }

    // Patients page
    if (document.getElementById("PatientsTable")) {
        loadPatients();
    }

});


// ================== SAVE (ADD + UPDATE) ==================
// ================== SAVE (ADD + UPDATE) ==================
function saveDoctor() {

    let id = document.getElementById("doctor_id").value;

    let formData = new FormData();

    formData.append(
        "name",
        document.getElementById("name").value
    );

    formData.append(
        "email",
        document.getElementById("email").value
    );

    formData.append(
        "phone",
        document.getElementById("phone").value
    );

    formData.append(
        "gender",
        document.getElementById("gender").value
    );
    formData.append(
        "password",
        document.getElementById("password").value
    );

    formData.append(
        "birth_date",
        document.getElementById("birth_date").value
    );

    formData.append(
        "specialty",
        document.getElementById("specialty").value
    );



   // =========================
// 🔵 UPDATE
// =========================
if(id){

    let newData = {

        name:
            document.getElementById("name").value,

        email:
            document.getElementById("email").value,

        phone:
            document.getElementById("phone").value,

        gender:
            document.getElementById("gender").value,

        specialty:
            document.getElementById("specialty").value,

        
    };

    let changes = "";

    for(let key in newData){

        if(newData[key] != oldDoctorData[key]){

            changes +=
                `${key}: ${oldDoctorData[key]} ➜ ${newData[key]}\n`;
        }
    }

    if(changes === ""){

        showMessage(
            "No changes made ⚠️",
            "warning"
        );

        return;
    }

    showConfirm(

        "Changes:\n\n" + changes,

        function(result){

            if(!result) return;

            formData.append("id", id);

            fetch(
                "php/update_doctor.php",
                {
                    method: "POST",
                    body: formData
                }
            )

            .then(res => res.text())

            .then(data => {

                console.log(data);

                // =========================
                // 🔥 EMAIL EXISTS
                // =========================
                if(data.trim() === "email_exists"){

                    showMessage(
                        "Email already exists ❌",
                        "error"
                    );

                    return;
                }

                // =========================
                // 🔥 PHONE EXISTS
                // =========================
                if(data.trim() === "phone_exists"){

                    showMessage(
                        "Phone already exists ❌",
                        "error"
                    );

                    return;
                }

                // =========================
                // 🔥 SUCCESS
                // =========================
                if(data.trim() === "success"){

                    showMessage(
                        "Doctor updated ✅",
                        "success"
                    );

                    resetForm();

                    loadDoctors();

                    let btn =
                        document.getElementById("saveBtn");

                    btn.innerText =
                        "Add Doctor";

                    btn.classList.remove(
                        "edit-mode"
                    );

                    document.getElementById(
                        "doctor_id"
                    ).value = "";

                }else{

                    showMessage(
                        "Update failed ❌",
                        "error"
                    );

                    console.log(data);
                }

            })

            .catch(err => {

                console.log(err);

                showMessage(
                    "Server Error ❌",
                    "error"
                );

            });

        }
    );
}

// =========================
// 🟢 ADD
// =========================
else{

    // 🔥 VALIDATION
    if(
        !document.getElementById("password")
            .value.trim()
    ){

        showMessage(
            "Enter password ❌",
            "error"
        );

        return;
    }

    fetch(
        "php/add_doctor.php",
        {
            method: "POST",
            body: formData
        }
    )

    .then(res => res.text())

    .then(data => {

        console.log(
            "ADD RESPONSE:",
            data
        );
        if(data.trim() === "email_exists"){

            showMessage(
                "Email already exists ❌",
                "error"
            );

            return;
        }

        if(data.trim() === "phone_exists"){

            showMessage(
                "Phone already exists ❌",
                "error"
            );

            return;
        }
        if(data.trim() === "success"){

            showMessage(
                "Doctor added ✅",
                "success"
            );

            resetForm();

            loadDoctors();

            let btn =
                document.getElementById(
                    "saveBtn"
                );

            btn.innerText =
                "Add Doctor";

            btn.classList.remove(
                "edit-mode"
            );

        }else{

            showMessage(
                "Add failed ❌",
                "error"
            );

            console.log(data);
        }

    })

    .catch(err => {

        console.log(err);

        showMessage(
            "Server Error ❌",
            "error"
        );

    });
}
}


// ================== RESET ==================
function resetForm() {

    document.getElementById("doctor_id").value = "";
    document.getElementById("name").value = "";
    document.getElementById("email").value = "";
    document.getElementById("phone").value = "";
    document.getElementById("gender").value = "";
    document.getElementById("password").value = "";
    document.getElementById("specialty").value = "";
   // 🔥 رجوع الباسورد
    const passwordInput =
        document.getElementById("password");

    passwordInput.value = "";

    passwordInput.style.display = "block";
    document.getElementById("saveBtn").innerText = "Add Doctor";
}

// البحث عن الدكاتره
function searchDoctor() {

    let input = document.getElementById("search").value.toLowerCase();
    let rows = document.querySelectorAll("#doctorTable tr");

    rows.forEach(row => {

        let name = row.children[0].innerText.toLowerCase();
        let email = row.children[1].innerText.toLowerCase();
        let phone = row.children[2].innerText.toLowerCase();

        if (name.includes(input) || email.includes(input) || phone.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }

    });
}

// =========================
// 🔥 SCHEDULE POPUP
// =========================

let currentDoctorId = null;

// =========================
// 🟢 OPEN POPUP
// =========================
function openSchedule(doctor_id){

    currentDoctorId = doctor_id;

    document.getElementById(
        "schedulePopup"
    ).style.display = "flex";

    loadDoctorSchedule(doctor_id);
}

// =========================
// ❌ CLOSE
// =========================
function closeSchedulePopup(){

    document.getElementById(
        "schedulePopup"
    ).style.display = "none";
}

// =========================
// 🟢 LOAD SCHEDULE
// =========================
function loadDoctorSchedule(doctor_id){

    fetch(
        `php/get_doctor_schedule.php?doctor_id=${doctor_id}`
    )

    .then(res=>res.json())

    .then(data=>{

        console.log(
            "SCHEDULE:",
            data
        );

        let table =
            document.getElementById(
                "scheduleTable"
            );

        table.innerHTML = "";

        // 🔥 اسم الدكتور
        if(data.length > 0){

            document.getElementById(
                "scheduleDoctorName"
            ).innerText =
                data[0].Doctor_Name
                +
                " Schedule";
        }

        // 🔥 مفيش أيام
        if(data.length === 0){

            table.innerHTML = `
                <tr>
                    <td colspan="4">
                        No Schedule Yet
                    </td>
                </tr>
            `;

            return;
        }

        // 🔥 عرض الأيام
        data.forEach(item=>{

            table.innerHTML += `

            <tr>

                <td>${item.Work_Day}</td>

                <td>${item.Start_Time}</td>

                <td>${item.End_Time}</td>

                <td>

                    <button
                        onclick="
                            deleteScheduleDay(
                                ${doctor_id},
                                '${item.Work_Day}',
                                '${item.Start_Time}'
                            )
                        "
                    >

                        Delete

                    </button>

                </td>

            </tr>

            `;
        });

    })

    .catch(err=>{

        console.log(err);

        showMessage(
            "Schedule load failed ❌",
            "error"
        );
    });
}

// =========================
// 🟢 ADD DAY
// =========================
function addScheduleDay(){

    let day =
        document.getElementById(
            "newDay"
        ).value;

    let start =
        document.getElementById(
            "newStart"
        ).value;

    let end =
        document.getElementById(
            "newEnd"
        ).value;

    // 🔴 VALIDATION
    if(
        !day
        ||
        !start
        ||
        !end
    ){

        showMessage(
            "Fill all fields ❌",
            "error"
        );

        return;
    }

    fetch(
        "php/add_schedule_day.php",
        {
            method:"POST",

            headers:{
                "Content-Type":"application/json"
            },

            body: JSON.stringify({

                doctor_id:
                    currentDoctorId,

                day: day,

                start:
                    start + ":00",

                end:
                    end + ":00"
            })
        }
    )

    .then(res=>res.json())

    .then(data=>{

        if(data.status === "success"){

            showMessage(
                "Day added ✅",
                "success"
            );

            // 🔥 reload table
            loadDoctorSchedule(
                currentDoctorId
            );

            // 🔥 reset form
            document.getElementById(
                "newDay"
            ).value = "";

            document.getElementById(
                "newStart"
            ).value = "";

            document.getElementById(
                "newEnd"
            ).value = "";

        }else{

            showMessage(
                data.message || "Add failed ❌",
                "error"
            );
        }

    })

    .catch(err=>{

        console.log(err);

        showMessage(
            "Server Error ❌",
            "error"
        );
    });
}

// =========================
// 🟢 DELETE DAY
// =========================
function deleteScheduleDay(
    doctor_id,
    day,
    start
){

    showConfirm(

        `Delete ${day} ?`,

        function(result){

            if(!result) return;

            fetch(
                "php/delete_schedule_day.php",
                {
                    method:"POST",

                    headers:{
                        "Content-Type":"application/json"
                    },

                    body: JSON.stringify({

                        doctor_id,
                        day,
                        start
                    })
                }
            )

            .then(res=>res.json())

            .then(data=>{

                if(data.status === "success"){

                    showMessage(
                        "Day deleted ✅",
                        "success"
                    );

                    loadDoctorSchedule(
                        doctor_id
                    );

                }else{

                    showMessage(
                        "Delete failed ❌",
                        "error"
                    );
                }

            })

            .catch(err=>{

                console.log(err);

                showMessage(
                    "Server Error ❌",
                    "error"
                );
            });

        }
    );
}

// ===================================================
// 🔥 MESSAGE SYSTEM (موحد زي الدكاترة)
// ===================================================

function showMessage(msg, type = "success") {

    let box = document.getElementById("msgBox") || document.getElementById("messageBox");
    if (!box) return;

    box.innerText = msg;
    box.className = type;
    box.style.display = "block";

    setTimeout(() => {
        box.style.display = "none";
    }, 3000);
}


// ===================================================
// 🔥 CONFIRM SYSTEM (PATIENTS SAFE - NO CONFLICT)
// ===================================================

let patientsConfirmCallback = null;

function showConfirm(message, callback) {

    let text = document.getElementById("confirmText");
    let box = document.getElementById("confirmBox");

    if (!text || !box) return;

    text.innerText = message;
    box.style.display = "flex";

    patientsConfirmCallback = callback;
}

function confirmYes() {
    document.getElementById("confirmBox").style.display = "none";
    if (patientsConfirmCallback) patientsConfirmCallback(true);
}

function confirmNo() {
    document.getElementById("confirmBox").style.display = "none";
    if (patientsConfirmCallback) patientsConfirmCallback(false);
}


// ===================================================
// 🔥 LOAD PATIENTS
// ===================================================

function loadPatients() {

    let table = document.getElementById("PatientsTable");
    if (!table) return;

    fetch("php/get_patients.php")
        .then(res => res.json())
        .then(data => {

            table.innerHTML = "";

            data.forEach(p => {

                table.innerHTML += `
                <tr>
                    <td>${p.Name}</td>
                    <td>${p.Email_Address ?? ''}</td>
                    <td>${p.Phone ?? ''}</td>
                    <td>${p.Age ?? ''} Years</td>

                    <td>

                        <button onclick="editPatient(
                            ${p.Patient_ID},
                            '${p.Name}',
                            '${p.Email_Address}',
                            '${p.Phone}',
                            '${p.Data_of_birth}'
                        )">
                            Edit
                        </button>

                        <button
                            class="history-btn"
                            onclick="openPatientHistory(${p.Patient_ID})"
                        >
                            View History
                        </button>

                    </td>
                </tr>
            `;
            });

        })
        .catch(err => {
            console.error(err);
            showMessage("Error loading patients ❌", "error");
        });
}

function closePatientHistory(){

    document
        .getElementById("historyModal")
        .classList.remove("active");
}

function openPatientHistory(patientId){

    document
        .getElementById("historyModal")
        .classList.add("active");

    const content =
        document.getElementById(
            "historyContent"
        );

    content.innerHTML = `

        <div class="loading">
            Loading...
        </div>

    `;

    fetch(
        "php/get_patient_full_history.php",
        {
            method:"POST",

            headers:{
                "Content-Type":"application/json"
            },

            body: JSON.stringify({
                patient_id: patientId
            })
        }
    )

    .then(res => res.json())

    .then(data => {

        content.innerHTML = "";

        // =========================
        // 🔥 لو مفيش بيانات
        // =========================
        if(!data || data.length === 0){

            content.innerHTML = `

                <div class="loading">
                    No history found
                </div>

            `;

            return;
        }

        data.forEach(item => {

            // =========================
            // 🔥 نظهر Completed + Canceled فقط
            // =========================
            if(
                item.Status !== "Completed"
                &&
                item.Status !== "Canceled"
            ){
                return;
            }

            // =========================
            // 🔥 STATUS CLASS
            // =========================
            let statusClass = "";

            if(item.Status === "Completed"){

                statusClass =
                    "status-completed";

            }else{

                statusClass =
                    "status-canceled";
            }

            // =========================
            // 🔥 SPLIT NOTES
            // =========================
            let diagnosis = "-";
            let prescription = "-";

            if(item.prescription_notes){

                let notesText =
                    item.prescription_notes;

                if(
                    notesText.includes(
                        "Prescription:"
                    )
                ){

                    let parts =
                        notesText.split(
                            "Prescription:"
                        );

                    diagnosis =
                        parts[0]
                        .replace(
                            "Diagnosis:",
                            ""
                        )
                        .trim();

                    prescription =
                        parts[1].trim();

                }else{

                    diagnosis =
                        notesText;
                }
            }

            // =========================
            // 🔥 FILE BUTTONS
            // =========================
            let fileButtons = "";

            if(item.file){

                fileButtons = `

                <div class="history-file">

                    <a
                        class="view-btn"
                        href="/medino-master/uploads/prescriptions/${item.file}"
                        target="_blank"
                    >
                        👁 View Prescription
                    </a>

                    <a
                        class="download-btn"
                        href="/medino-master/uploads/prescriptions/${item.file}"
                        download
                    >
                        ⬇ Download Prescription
                    </a>

                </div>

                `;
            }

            // =========================
            // 🔥 CARD
            // =========================
            content.innerHTML += `

                <div class="history-card">

                    <div class="history-top">

                        <div class="history-doctor">
                            ${item.doctor_name}
                        </div>

                        <div class="history-badges">

                            <div class="history-date">
                                ${item.Booking_Date}
                            </div>

                            <div class="${statusClass}">
                                ${item.Status}
                            </div>

                        </div>

                    </div>

                    <div class="history-section">

                        <div class="history-label">
                            Diagnosis:
                        </div>

                        <div class="history-text">
                            ${diagnosis}
                        </div>

                    </div>

                    <div class="history-section">

                        <div class="history-label">
                            Prescription:
                        </div>

                        <div class="history-text">
                            ${prescription}
                        </div>

                    </div>

                    ${fileButtons}

                </div>

            `;
        });

    })

    .catch(err => {

        console.log(err);

        content.innerHTML = `

            <div class="loading">
                Error loading history
            </div>

        `;
    });
}
// ===================================================
// 🔥 INIT
// ===================================================

document.addEventListener("DOMContentLoaded", function () {

    if (document.getElementById("PatientsTable")) {
        loadPatients();
    }
});


// ===================================================
// 🔥 EDIT PATIENT
// ===================================================
let oldPatientData = {};

function editPatient(id, name, email, phone, dob) {

    document.getElementById("patient_id").value = id;
    document.getElementById("name").value = name;
    document.getElementById("email").value = email;
    document.getElementById("phone").value = phone;
    document.getElementById("Birth_date").value = dob;

    document.getElementById("saveBtn").innerText = "Update Patient";
    document.getElementById("saveBtn").innerText = "Update Patient";
    document.getElementById("saveBtn").classList.add("edit-mode");
    let btn = document.getElementById("saveBtn");

    btn.innerText = "Update Patient";
    btn.classList.add("edit-mode");   // 🔥 ده اللي ناقصك

    // 🔥 حفظ النسخة القديمة للمقارنة
    oldPatientData = {
        name,
        email,
        phone,
        dob
    };
}


// ===================================================
// 🔥 SAVE PATIENT (UPDATE)
// ===================================================


function savePatient() {

    let id = document.getElementById("patient_id").value;

    let newData = {
        name: document.getElementById("name").value,
        email: document.getElementById("email").value,
        phone: document.getElementById("phone").value,
        dob: document.getElementById("Birth_date").value
    };

    // 🔥 مقارنة التغيير
    let changes = "";

    for (let key in newData) {
        if (newData[key] != oldPatientData[key]) {
            changes += `${key}: ${oldPatientData[key]} ➜ ${newData[key]}\n`;
        }
    }

    // 🚨 لو مفيش تغيير
    if (changes === "") {
        showMessage("No changes made ⚠️", "warning");
        return;
    }

    let formData = new FormData();
    formData.append("id", id);
    formData.append("name", newData.name);
    formData.append("email", newData.email);
    formData.append("phone", newData.phone);
    formData.append("dob", newData.dob);

    showConfirm("Changes:\n\n" + changes, function (result) {

        if (!result) return;

        fetch("php/update_patient.php", {
            method: "POST",
            body: formData
        })
            
            .then(res => res.text())

            .then(data => {

                console.log(data);

                if(data.trim() === "email_exists"){

                    showMessage(
                        "Email already exists ❌",
                        "error"
                    );

                    return;
                }

                if(data.trim() === "phone_exists"){

                    showMessage(
                        "Phone already exists ❌",
                        "error"
                    );

                    return;
                }

                if(data.trim() === "success"){

                    showMessage(
                        "Patient updated successfully ✅",
                        "success"
                    );

                    loadPatients();

                    resetPatientForm();

                }else{

                    showMessage(
                        "Update failed ❌",
                        "error"
                    );
                }
})


            .catch(err => {
                console.error(err);
                showMessage("Update failed ❌", "error");
            });
        let btn = document.getElementById("saveBtn");

        btn.innerText = "Add Patient";
        btn.classList.remove("edit-mode");   // 🔥 يرجع أخضر

        document.getElementById("patient_id").value = ""; // مهم جدًا

    });
}


// ===================================================
// 🔥 RESET FORM
// ===================================================

function resetPatientForm() {

    document.getElementById("patient_id").value = "";
    document.getElementById("name").value = "";
    document.getElementById("email").value = "";
    document.getElementById("phone").value = "";
    document.getElementById("Birth_date").value = "";

    document.getElementById("saveBtn").innerText = "Edit Patients";

    oldPatientData = {};
}


// ===================================================
// 🔥 SEARCH PATIENT
// ===================================================

function searchPatient() {

    let input = document.getElementById("search").value.toLowerCase();
    let rows = document.querySelectorAll("#PatientsTable tr");

    rows.forEach(row => {

        let name = row.children[0].innerText.toLowerCase();
        let email = row.children[1].innerText.toLowerCase();
        let phone = row.children[2].innerText.toLowerCase();

        if (name.includes(input) || email.includes(input) || phone.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}


// البحث عن المريض
function searchPatient() {

    let input = document.getElementById("search").value.toLowerCase();
    let rows = document.querySelectorAll("#PatientsTable tr");

    rows.forEach(row => {

        let name = row.children[0].innerText.toLowerCase();
        let email = row.children[1].innerText.toLowerCase();
        let phone = row.children[2].innerText.toLowerCase();

        if (name.includes(input) || email.includes(input) || phone.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }

    });

}






