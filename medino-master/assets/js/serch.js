document.getElementById("searchForm").addEventListener("submit", function(e){

    e.preventDefault();

    let formData = new FormData(this);

    fetch("php/search_booking.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        let resultDiv = document.getElementById("result");
        resultDiv.innerHTML = "";

        if(data.length === 0){
            resultDiv.innerHTML = "<p>No Booking Found ❌</p>";
            return;
        }

        data.forEach(item => {

            resultDiv.innerHTML += `
                <div style="
                    border:1px solid #ccc;
                    padding:10px;
                    margin:10px;
                ">
                    <p><b>Booking ID:</b> ${item.Booking_ID}</p>
                    <p><b>Patient:</b> ${item.patient_name}</p>
                    <p><b>Doctor:</b> ${item.doctor_name}</p>
                    <p><b>Date:</b> ${item.Appointment_Date}</p>
                    <p><b>Time:</b> ${item.Start_Time} - ${item.End_Time}</p>
                </div>
            `;

        });

    });

});