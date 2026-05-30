/************* تحميل الصفحة *************/
document.addEventListener("DOMContentLoaded", function(){
    loadSpecialties();
});

/************* تحميل التخصصات *************/
function loadSpecialties(){

    fetch("php/get_all_specialties.php")
    .then(res => res.json())
    .then(data => {

        let table = document.getElementById("specialtyTable");
        table.innerHTML = "";

        data.forEach(s => {

            table.innerHTML += `
            <tr>
                <td>${s.Specialty_ID}</td>
                <td>${s.Name}</td>
                <td>${s.Description ?? ''}</td>

                <td>
                    <button onclick='editSpecialty(${JSON.stringify(s)})'>Edit</button>
                    <button onclick="deleteSpecialty(${s.Specialty_ID})">Delete</button>
                </td>
            </tr>
            `;
        });

    });
}

/************* Edit *************/
function editSpecialty(s){

    document.getElementById("specialty_id").value = s.Specialty_ID;
    document.getElementById("name").value = s.Name;
    document.getElementById("desc").value = s.Description ?? "";

    document.getElementById("saveBtn").innerText = "Edit Specialty";
    document.getElementById("saveBtn").classList.add("edit-mode");

    window.scrollTo({top:0, behavior:"smooth"});
}

/************* Save (Add + Edit) *************/
function saveSpecialty(){

    let id   = document.getElementById("specialty_id").value;
    let name = document.getElementById("name").value;
    let desc = document.getElementById("desc").value;

    let formData = new FormData();
    formData.append("name", name);
    formData.append("desc", desc);

    // 🔥 لو فيه ID → Edit
    if(id){

        formData.append("id", id);

        fetch("php/update_specialty.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(msg => {
            alert(msg);

            resetForm();
            loadSpecialties();

            // ✅ 🔥 الحل هنا
            let btn = document.getElementById("saveBtn");
            btn.innerText = "Add Specialty";
            btn.classList.remove("edit-mode");   // يرجع أخضر

            document.getElementById("specialty_id").value = ""; // مهم
        });

    }else{

        // 🟢 Add
        fetch("php/insert_specialty.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(msg => {
            alert(msg);

            resetForm();
            loadSpecialties();

            // ✅ برضه تأكد يرجع طبيعي
            let btn = document.getElementById("saveBtn");
            btn.innerText = "Add Specialty";
            btn.classList.remove("edit-mode");
        });

    }
}

/************* Delete *************/
function deleteSpecialty(id){

    if(!confirm("Delete this specialty?")) return;

    fetch("php/remove_specialty.php?id=" + id)
    .then(res => res.text())
    .then(msg => {
        alert(msg);
        loadSpecialties();
    });
}

/************* Reset *************/
function resetForm(){

    document.getElementById("specialty_id").value = "";
    document.getElementById("name").value = "";
    document.getElementById("desc").value = "";

    document.getElementById("saveBtn").innerText = "Add Specialty";
}


function go(page){
    window.location.href = page;
}

function logout(){
    window.location.href = "index.html";
}

