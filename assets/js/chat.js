function sendMessage() {

    let input = document.getElementById("userInput");
    let message = input.value.trim();

    if(message === "") return;

    let chatBox = document.getElementById("chatBox");

    chatBox.innerHTML += "<div class='user'>" + message + "</div>";

    fetch("../chatbot/chat.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "message=" + encodeURIComponent(message)
    })
    .then(response => response.text())
    .then(data => {

        chatBox.innerHTML += "<div class='bot'>" + data + "</div>";
        input.value = "";
        chatBox.scrollTop = chatBox.scrollHeight;

        autoSelectDoctor(data);
    });
}

function autoSelectDoctor(aiResponse){

    let doctorSelect = document.querySelector("select[name='doctor']");

    if(!doctorSelect) return;

    if(aiResponse.includes("Cardiologist")){
        doctorSelect.value = "Cardiologist";
    }
    else if(aiResponse.includes("Dermatologist")){
        doctorSelect.value = "Dermatologist";
    }
    else if(aiResponse.includes("Neurologist")){
        doctorSelect.value = "Neurologist";
    }
    else if(aiResponse.includes("General Physician")){
        doctorSelect.value = "General Physician";
    }
}
