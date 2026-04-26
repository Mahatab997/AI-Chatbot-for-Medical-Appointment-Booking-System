<?php
include "../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || $_SESSION['role']!="patient"){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* User Info */
$stmt = $conn->prepare("SELECT name FROM users WHERE id=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$userName = $userData['name'] ?? "Patient";

/* Total Appointments */
$count = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE user_id=?");
$count->bind_param("i",$user_id);
$count->execute();
$totalAppointments = $count->get_result()->fetch_assoc()['total'] ?? 0;

/* Upcoming Appointment */
$upcoming = $conn->prepare("SELECT appointment_date, appointment_time 
FROM appointments 
WHERE user_id=? AND appointment_date >= CURDATE()
ORDER BY appointment_date ASC LIMIT 1");
$upcoming->bind_param("i",$user_id);
$upcoming->execute();
$nextAppointment = $upcoming->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smart Dashboard | Medical AI Pro</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}

body{
    display:flex;
    min-height:100vh;
    background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
}

/* Sidebar */
.sidebar{
    width:250px;
    background:rgba(15,32,39,0.95);
    color:white;
    padding:30px 20px;
}
.sidebar h2{
    text-align:center;
    margin-bottom:40px;
    color:#1abc9c;
}
.sidebar a{
    display:block;
    padding:12px;
    margin-bottom:10px;
    text-decoration:none;
    color:#e0f7ff;
    border-radius:8px;
    transition:0.3s;
}
.sidebar a:hover{
    background:#1abc9c;
    color:black;
}

/* Main */
.main{
    flex:1;
    padding:40px;
    color:white;
}

/* Header */
.header{
    background:rgba(26,188,156,0.15);
    backdrop-filter:blur(15px);
    padding:20px;
    border-radius:15px;
    margin-bottom:30px;
    border:1px solid rgba(26,188,156,0.3);
}

/* Cards */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:30px;
}
.card{
    background:rgba(26,188,156,0.12);
    backdrop-filter:blur(15px);
    padding:25px;
    border-radius:16px;
    border:1px solid rgba(26,188,156,0.3);
    transition:0.3s;
}
.card:hover{
    transform:translateY(-5px);
}

/* AI Tips */
.tips{
    background:rgba(0,119,182,0.15);
    backdrop-filter:blur(15px);
    padding:20px;
    border-radius:16px;
    border:1px solid rgba(0,119,182,0.3);
    margin-bottom:30px;
}

/* ===========================
   PROFESSIONAL AI ASSISTANT
=========================== */
.chat{
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(20px);
    border-radius:20px;
    padding:25px;
    border:1px solid rgba(26,188,156,0.3);
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

.chat h3{
    margin-bottom:15px;
    color:#1abc9c;
}

.chat-box{
    height:250px;
    overflow-y:auto;
    padding:15px;
    border-radius:15px;
    background:rgba(15,32,39,0.6);
    margin-bottom:15px;
}

/* Message bubbles */
.message{
    padding:10px 15px;
    border-radius:20px;
    margin-bottom:10px;
    max-width:75%;
    font-size:14px;
    line-height:1.5;
    animation:fadeIn 0.3s ease;
}

.user{
    background:linear-gradient(135deg,#1abc9c,#16a085);
    color:white;
    margin-left:auto;
    border-bottom-right-radius:5px;
}

.bot{
    background:rgba(255,255,255,0.15);
    color:#e0f7fa;
    border:1px solid rgba(26,188,156,0.4);
    border-bottom-left-radius:5px;
}

/* Typing indicator */
.typing{
    display:flex;
    align-items:center;
    gap:5px;
    font-style:italic;
    color:#e0f7fa;
    margin-bottom:10px;
}
.typing span{
    width:6px;
    height:6px;
    background:white;
    border-radius:50%;
    display:inline-block;
    animation:blink 1s infinite;
}
.typing span:nth-child(2){animation-delay:0.2s;}
.typing span:nth-child(3){animation-delay:0.4s;}

@keyframes blink{
0%,80%,100%{opacity:0;}
40%{opacity:1;}
}

/* Chat input */
.chat-input{
    display:flex;
    gap:5px;
}

.chat-input input{
    flex:1;
    padding:12px;
    border:none;
    border-radius:25px 0 0 25px;
    background:rgba(255,255,255,0.1);
    color:white;
    outline:none;
}

.chat-input button{
    padding:12px 20px;
    border:none;
    border-radius:0 25px 25px 0;
    background:linear-gradient(135deg,#1abc9c,#16a085);
    color:white;
    cursor:pointer;
    transition:0.3s;
    display:flex;
    align-items:center;
    gap:8px;
}

.chat-input button:hover{
    background:linear-gradient(135deg,#16a085,#138d75);
}

/* Microphone button */
#voiceBtn{
    background:#3498db;
    border:none;
    color:white;
    border-radius:50%;
    width:45px;
    height:45px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    transition:0.3s;
}
#voiceBtn:hover{background:#1f78b4;}

@keyframes fadeIn{
from{opacity:0;transform:translateY(10px);}
to{opacity:1;transform:translateY(0);}
}

/* Responsive */
@media(max-width:768px){
    body{flex-direction:column}
    .sidebar{width:100%}
    .chat-input{flex-direction:column; gap:10px;}
    .chat-input input{border-radius:25px;}
    .chat-input button{border-radius:25px;}
}
</style>
</head>

<body>

<div class="sidebar">
    <h2>Medical AI Pro</h2>
    <a href="#">Dashboard</a>
    <a href="book.php">Book Appointment</a>
    <a href="my_appointments.php">My Appointments</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<div class="main">

<div class="header">
    <h2>Assalamu Alaikum <?php echo htmlspecialchars($userName); ?></h2>
</div>

<div class="cards">
<div class="card">
    <h3>Total Appointments</h3>
    <p><?php echo $totalAppointments; ?></p>
</div>
<div class="card">
    <h3>Upcoming Visit</h3>
    <p>
    <?php 
    if($nextAppointment){
        echo $nextAppointment['appointment_date']."<br>".$nextAppointment['appointment_time'];
    } else {
        echo "No Upcoming Appointment";
    }
    ?>
    </p>
</div>
<div class="card">
    <h3>AI Status</h3>
    <p style="color:#1abc9c;font-weight:600;">Online 24/7</p>
</div>
</div>

<div class="tips">
<h3>💡 AI Health Tip</h3>
<p>Drink enough water and maintain 7-8 hours sleep for better immunity.</p>
</div>

<div class="chat">
<h1>🤖 AI Assistant</h1>

<div class="chat-box" id="chatBox"></div>

<div class="chat-input">
<input type="text" id="userInput" placeholder="Ask your health question...">
<button onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i> Send</button>
<button id="voiceBtn"><i class="fa-solid fa-microphone"></i></button>
</div>

</div>

</div>

<!-- <script>
// Voice recognition
const voiceBtn = document.getElementById('voiceBtn');
const userInput = document.getElementById('userInput');

let recognition;
if('webkitSpeechRecognition' in window){
    recognition = new webkitSpeechRecognition();
    recognition.lang = 'en-US';
    recognition.continuous = false;

    recognition.onresult = function(event){
        userInput.value = event.results[0][0].transcript;
        sendMessage();
    };
}

voiceBtn.addEventListener('click', () => {
    if(recognition) recognition.start();
});

// Typing indicator, send message & voice reply
function sendMessage(){
    let input=document.getElementById("userInput");
    let chatBox=document.getElementById("chatBox");

    if(input.value.trim()==="") return;

    // User message
    chatBox.innerHTML+=`<div class='message user'>${input.value}</div>`;
    chatBox.scrollTop = chatBox.scrollHeight;

    // Typing indicator
    let typingIndicator = document.createElement('div');
    typingIndicator.className = 'typing';
    typingIndicator.innerHTML = 'AI is typing <span></span><span></span><span></span>';
    chatBox.appendChild(typingIndicator);
    chatBox.scrollTop = chatBox.scrollHeight;

    // Simulate AI response
    setTimeout(()=>{
        typingIndicator.remove();
        const botMessage = "For accurate diagnosis, please consult a licensed doctor.";
        chatBox.innerHTML+=`<div class='message bot'>${botMessage}</div>`;
        chatBox.scrollTop = chatBox.scrollHeight;

        // Voice feedback
        if('speechSynthesis' in window){
            const utterance = new SpeechSynthesisUtterance(botMessage);
            utterance.lang = 'en-US';
            utterance.pitch = 1;
            utterance.rate = 1;
            speechSynthesis.speak(utterance);
        }

    },1200);

    input.value="";
}
 </script> -->

 <script>
const voiceBtn = document.getElementById('voiceBtn');
const userInput = document.getElementById('userInput');

let recognition;
if ('webkitSpeechRecognition' in window) {
    recognition = new webkitSpeechRecognition();
    recognition.lang = 'en-US';
    recognition.continuous = false;

    recognition.onresult = function(event) {
        userInput.value = event.results[0][0].transcript;
        sendMessage();
    };
}

voiceBtn.addEventListener('click', () => {
    if (recognition) recognition.start();
});

async function sendMessage() {
    let input = document.getElementById("userInput");
    let chatBox = document.getElementById("chatBox");

    const userMessage = input.value.trim();
    if (userMessage === "") return;

    chatBox.innerHTML += `<div class='message user'>${escapeHtml(userMessage)}</div>`;
    chatBox.scrollTop = chatBox.scrollHeight;

    let typingIndicator = document.createElement('div');
    typingIndicator.className = 'typing';
    typingIndicator.innerHTML = 'AI is typing <span></span><span></span><span></span>';
    chatBox.appendChild(typingIndicator);

    input.value = "";

    try {
        const response = await fetch("../auth/API/chat.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({ message: userMessage })
        });

        const data = await response.json();
        typingIndicator.remove();

        /* AI TEXT */
        chatBox.innerHTML += `<div class='message bot'>${escapeHtml(data.reply)}</div>`;

        /* DOCTOR LIST WITH BOOK BUTTON */
        if (data.doctors && data.doctors.length > 0) {

            let doctorsHtml = `<div class='message bot'>Recommended Doctors:<br>`;

            data.doctors.forEach(doc => {
                doctorsHtml += `
                <div style="margin-top:8px;">
                    👨‍⚕️ ${doc.name} (${doc.experience} yrs, ⭐ ${doc.rating})<br>
                    <a href="book.php?doctor_id=${doc.id}&spec=${encodeURIComponent(doc.specialization)}">
                        Book Now
                    </a>
                </div><br>`;
            });

            doctorsHtml += `</div>`;
            chatBox.innerHTML += doctorsHtml;
        }

        chatBox.scrollTop = chatBox.scrollHeight;

    } catch (error) {
        typingIndicator.remove();
        chatBox.innerHTML += `<div class='message bot'>Server error. Try again.</div>`;
    }
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.innerText = text;
    return div.innerHTML;
}
</script>

</body>
</html>