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
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

:root {
    --bg-main: #0B1120;
    --bg-secondary: #1E293B;
    --bg-card: #334155;
    --accent: #38BDF8;
    --accent-hover: #0EA5E9;
    --text-primary: #F8FAFC;
    --text-secondary: #94A3B8;
    --danger: #F43F5E;
    --danger-bg: rgba(244, 63, 94, 0.1);
    --border-color: rgba(248, 250, 252, 0.08);
}

body {
    display: flex;
    min-height: 100vh;
    background: var(--bg-main);
    color: var(--text-primary);
    overflow-x: hidden;
}

/* ===========================
   SIDEBAR
=========================== */
.sidebar {
    width: 260px;
    background: var(--bg-secondary);
    padding: 40px 20px;
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--border-color);
    box-shadow: 4px 0 20px rgba(0,0,0,0.3);
    z-index: 10;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 50px;
    color: var(--accent);
    font-size: 22px;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    margin-bottom: 8px;
    text-decoration: none;
    color: var(--text-secondary);
    border-radius: 10px;
    transition: all 0.2s ease;
    font-weight: 500;
    font-size: 15px;
}

.sidebar a:hover, .sidebar a.active {
    background: rgba(56, 189, 248, 0.1);
    color: var(--accent);
    transform: translateX(4px);
}

/* ===========================
   MAIN CONTENT
=========================== */
.main {
    flex: 1;
    padding: 40px 50px;
    height: 100vh;
    overflow-y: auto;
}

/* Header */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
    animation: fadeInDown 0.5s ease forwards;
}

.header h2 {
    font-size: 26px;
    font-weight: 600;
    color: var(--text-primary);
}

.header h2 span {
    color: var(--accent);
}

/* Dashboard Cards */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 25px;
    margin-bottom: 35px;
}

.card {
    background: var(--bg-secondary);
    padding: 25px;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    border-color: rgba(56, 189, 248, 0.2);
}

.card-icon {
    font-size: 22px;
    color: var(--accent);
    margin-bottom: 5px;
}

.card h3 {
    font-size: 13px;
    text-transform: uppercase;
    color: var(--text-secondary);
    letter-spacing: 0.5px;
    font-weight: 600;
}

.card p {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
}

/* General Tip */
.global-tip {
    background: linear-gradient(90deg, rgba(56, 189, 248, 0.1), transparent);
    border-left: 4px solid var(--accent);
    padding: 20px 25px;
    border-radius: 0 12px 12px 0;
    margin-bottom: 35px;
    display: flex;
    align-items: center;
    gap: 15px;
    color: var(--text-primary);
    font-size: 15px;
}

/* ===========================
   CHAT ASSISTANT
=========================== */
.chat-container {
    background: var(--bg-secondary);
    border-radius: 20px;
    border: 1px solid var(--border-color);
    box-shadow: 0 20px 40px rgba(0,0,0,0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-header {
    padding: 20px 25px;
    background: rgba(0,0,0,0.2);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-header h1 {
    font-size: 18px;
    color: var(--text-primary);
    font-weight: 600;
}

.chat-header .status-dot {
    width: 10px;
    height: 10px;
    background: var(--accent);
    border-radius: 50%;
    box-shadow: 0 0 10px var(--accent);
    animation: pulse 2s infinite;
}

.chat-box {
    height: 420px;
    overflow-y: auto;
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    scroll-behavior: smooth;
    background: var(--bg-main);
}

.chat-box::-webkit-scrollbar {
    width: 6px;
}
.chat-box::-webkit-scrollbar-thumb {
    background: var(--bg-card);
    border-radius: 10px;
}

/* Chat Bubbles */
.message {
    padding: 14px 18px;
    border-radius: 16px;
    max-width: 80%;
    font-size: 15px;
    line-height: 1.5;
    animation: fadeInUp 0.3s ease forwards;
    word-wrap: break-word;
}

.user {
    background: var(--accent);
    color: #0B1120;
    font-weight: 500;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
    box-shadow: 0 4px 12px rgba(56, 189, 248, 0.1);
}

.bot {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}

/* Structured Bot Output */
.bot-issue {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 16px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.bot-tips {
    color: var(--text-secondary);
    margin-bottom: 15px;
}

/* Urgency Styling */
.urgency-high {
    border-left: 4px solid var(--danger);
    background: var(--danger-bg);
}
.urgency-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 12px;
    background: var(--danger);
    color: #fff;
    letter-spacing: 0.5px;
}

/* Doctor Recommendations */
.doc-section-title {
    font-size: 13px;
    text-transform: uppercase;
    color: var(--accent);
    letter-spacing: 1px;
    margin-bottom: 15px;
    font-weight: 600;
    border-top: 1px solid var(--border-color);
    padding-top: 15px;
}

.doctor-recommendations {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.doctor-card {
    background: var(--bg-main);
    border-radius: 12px;
    padding: 15px;
    border: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: 0.2s;
}

.doctor-card:hover {
    border-color: rgba(56, 189, 248, 0.3);
}

.doctor-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.doctor-name {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 15px;
}

.doctor-spec {
    font-size: 12px;
    color: var(--accent);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
    opacity: 0.85;
}

.doctor-stats {
    font-size: 13px;
    color: var(--text-secondary);
    display: flex;
    gap: 15px;
}

.doctor-book-btn {
    padding: 8px 18px;
    background: transparent;
    color: var(--accent);
    border: 1px solid var(--accent);
    text-decoration: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    transition: 0.2s;
}

.doctor-book-btn:hover {
    background: rgba(56, 189, 248, 0.1);
}

/* Typing Indicator */
.typing {
    align-self: flex-start;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 15px 20px;
    border-radius: 20px;
    background: rgba(56, 189, 248, 0.05);
    border: 1px solid rgba(56, 189, 248, 0.1);
}
.typing span {
    width: 6px;
    height: 6px;
    background: var(--accent);
    border-radius: 50%;
    animation: blink 1.4s infinite both;
}
.typing span:nth-child(1) { animation-delay: 0s; }
.typing span:nth-child(2) { animation-delay: 0.2s; }
.typing span:nth-child(3) { animation-delay: 0.4s; }

/* Chat Input */
.chat-input-area {
    padding: 18px 25px;
    background: rgba(0,0,0,0.2);
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 12px;
    align-items: center;
}

.chat-input-area input {
    flex: 1;
    padding: 14px 20px;
    border: 1px solid var(--border-color);
    border-radius: 30px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    outline: none;
    font-size: 15px;
    transition: 0.2s;
}

.chat-input-area input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.15);
}

.chat-input-area button.btn-send {
    padding: 14px 26px;
    border: none;
    border-radius: 30px;
    background: var(--accent);
    color: #0B1120;
    cursor: pointer;
    transition: 0.2s;
    font-weight: 600;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chat-input-area button.btn-send:hover {
    background: var(--accent-hover);
    transform: translateY(-2px);
}

#voiceBtn {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    color: var(--text-secondary);
    border-radius: 50%;
    width: 48px;
    height: 48px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: 0.2s;
}

#voiceBtn:hover {
    color: var(--accent);
    border-color: var(--accent);
    background: rgba(56, 189, 248, 0.1);
}

/* Animations */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.5); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(56, 189, 248, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(56, 189, 248, 0); }
}
@keyframes blink {
    0%, 80%, 100% { opacity: 0.2; transform: scale(0.8); }
    40% { opacity: 1; transform: scale(1.2); }
}

@media(max-width: 900px) {
    body { flex-direction: column; }
    .sidebar { width: 100%; padding: 20px; border-right: none; border-bottom: 1px solid var(--border-color); }
    .sidebar h2 { margin-bottom: 20px; }
    .main { padding: 20px; }
    .chat-input-area { flex-wrap: wrap; }
    .chat-input-area input { width: 100%; order: -1; }
}
</style>
</head>

<body>

<div class="sidebar">
    <h2><i class="fa-solid fa-notes-medical"></i> Medical AI</h2>
    <a href="#" class="active"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a href="book.php"><i class="fa-solid fa-calendar-plus"></i> Book Appointment</a>
    <a href="my_appointments.php"><i class="fa-solid fa-list-check"></i> My Appointments</a>
    <div style="flex:1"></div>
    <a href="../auth/logout.php" style="color:var(--danger);"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main">

    <div class="header">
        <h2>Welcome back, <span><?php echo htmlspecialchars($userName); ?></span></h2>
    </div>

    <div class="cards">
        <div class="card">
            <div class="card-icon"><i class="fa-solid fa-calendar-check"></i></div>
            <h3>Total Appointments</h3>
            <p><?php echo $totalAppointments; ?></p>
        </div>
        <div class="card">
            <div class="card-icon"><i class="fa-regular fa-clock"></i></div>
            <h3>Upcoming Visit</h3>
            <p>
            <?php 
            if($nextAppointment){
                echo "<strong>" . date("M d", strtotime($nextAppointment['appointment_date'])) . "</strong> at " . date("h:i A", strtotime($nextAppointment['appointment_time']));
            } else {
                echo "<span style='font-size:16px; font-weight:400; color:var(--text-secondary);'>No upcoming visits</span>";
            }
            ?>
            </p>
        </div>
        <div class="card">
            <div class="card-icon"><i class="fa-solid fa-robot"></i></div>
            <h3>AI System</h3>
            <p style="color:var(--accent);">Online 24/7</p>
        </div>
    </div>

    <div class="global-tip">
        <i class="fa-regular fa-lightbulb" style="font-size:24px; color:var(--accent);"></i>
        <div>
            <strong style="display:block; color:var(--text-primary); margin-bottom:4px;">Daily Health Tip</strong>
            Drink enough water and maintain 7-8 hours of sleep for optimal immunity.
        </div>
    </div>

    <div class="chat-container">
        <div class="chat-header">
            <div class="status-dot"></div>
            <h1>AI Assistant</h1>
            <div style="flex:1"></div>
            <button onclick="clearChatHistory()" style="background:transparent; border:1px solid var(--border-color); color:var(--text-secondary); padding:6px 14px; border-radius:8px; cursor:pointer; font-size:12px; font-family:inherit; transition:0.2s;" onmouseover="this.style.color='var(--danger)';this.style.borderColor='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)';this.style.borderColor='var(--border-color)'"><i class="fa-solid fa-trash-can"></i> Clear</button>
        </div>

        <div class="chat-box" id="chatBox">
            <div class="message bot">
                <div class="bot-issue"><i class="fa-solid fa-hand-holding-medical"></i> How can I help?</div>
                <div class="bot-tips">Please describe your symptoms in detail so I can analyze the issue and recommend the best specialist for you.</div>
            </div>
        </div>

        <div class="chat-input-area">
            <input type="text" id="userInput" placeholder="E.g., I have been experiencing severe headaches..." onkeypress="handleEnter(event)">
            <button class="btn-send" onclick="sendMessage()">Send <i class="fa-solid fa-paper-plane"></i></button>
            <button id="voiceBtn"><i class="fa-solid fa-microphone"></i></button>
        </div>
    </div>

</div>

<script>
const voiceBtn = document.getElementById('voiceBtn');
const userInput = document.getElementById('userInput');
const chatBox = document.getElementById('chatBox');
const HISTORY_KEY = 'medai_chat_history_<?php echo $user_id; ?>';

// ====== Chat History (localStorage) ======
function saveChatHistory() {
    localStorage.setItem(HISTORY_KEY, chatBox.innerHTML);
}

function loadChatHistory() {
    const saved = localStorage.getItem(HISTORY_KEY);
    if (saved) {
        chatBox.innerHTML = saved;
        scrollToBottom(chatBox);
    }
}

function clearChatHistory() {
    localStorage.removeItem(HISTORY_KEY);
    chatBox.innerHTML = `
        <div class="message bot">
            <div class="bot-issue"><i class="fa-solid fa-hand-holding-medical"></i> How can I help?</div>
            <div class="bot-tips">Please describe your symptoms in detail so I can analyze the issue and recommend the best specialist for you.</div>
        </div>`;
}

// Load previous chat on page load
loadChatHistory();

// ====== Voice Recognition ======
let recognition;
if ('webkitSpeechRecognition' in window) {
    recognition = new webkitSpeechRecognition();
    recognition.lang = 'en-US';
    recognition.continuous = false;

    recognition.onstart = function() {
        voiceBtn.style.color = 'var(--danger)';
        voiceBtn.style.borderColor = 'var(--danger)';
    };

    recognition.onend = function() {
        voiceBtn.style.color = 'var(--text-secondary)';
        voiceBtn.style.borderColor = 'var(--border-color)';
    };

    recognition.onresult = function(event) {
        userInput.value = event.results[0][0].transcript;
        sendMessage();
    };
}

voiceBtn.addEventListener('click', () => {
    if (recognition) recognition.start();
});

function handleEnter(e) {
    if(e.key === 'Enter') {
        sendMessage();
    }
}

async function sendMessage() {
    const userMessage = userInput.value.trim();
    if (userMessage === "") return;

    // Render User Message
    chatBox.innerHTML += `<div class='message user'>${escapeHtml(userMessage)}</div>`;
    scrollToBottom(chatBox);

    // Render Typing Indicator
    let typingIndicator = document.createElement('div');
    typingIndicator.className = 'typing';
    typingIndicator.innerHTML = '<span></span><span></span><span></span>';
    chatBox.appendChild(typingIndicator);
    scrollToBottom(chatBox);

    userInput.value = "";

    try {
        const response = await fetch("../auth/API/chat.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({ message: userMessage })
        });

        const data = await response.json();
        typingIndicator.remove();

        let botMessageClass = 'message bot';
        let botContent = '';

        if (data.urgency === 'high') {
            botMessageClass += ' urgency-high';
            botContent += `<span class="urgency-badge"><i class="fa-solid fa-triangle-exclamation"></i> High Urgency</span>`;
        }

        // Render Issue and Tips
        botContent += `
            <div class="bot-issue"><i class="fa-solid fa-stethoscope"></i> ${escapeHtml(data.issue)}</div>
            <div class="bot-tips">${escapeHtml(data.tips)}</div>
        `;

        /* AI DOCTOR RECOMMENDATIONS */
        if (data.doctors && data.doctors.length > 0) {
            botContent += `<div class="doc-section-title">Recommended Specialists</div>`;
            botContent += `<div class="doctor-recommendations">`;

            data.doctors.forEach(doc => {
                botContent += `
                <div class="doctor-card">
                    <div class="doctor-info">
                        <div class="doctor-name">${escapeHtml(doc.name)}</div>
                        <div class="doctor-spec"><i class="fa-solid fa-user-doctor"></i> ${escapeHtml(doc.specialization)}</div>
                        <div class="doctor-stats">
                            <span><i class="fa-solid fa-briefcase"></i> ${escapeHtml(doc.experience)} yrs</span>
                            <span><i class="fa-solid fa-star" style="color:#f1c40f;"></i> ${escapeHtml(doc.rating)}</span>
                        </div>
                    </div>
                    <a class="doctor-book-btn" href="book.php?doctor_id=${doc.id}&spec=${encodeURIComponent(doc.specialization)}">
                        Book
                    </a>
                </div>`;
            });

            botContent += `</div>`;
        } else if (data.specialization) {
            botContent += `<div style="font-size:13px; color:var(--text-secondary); margin-top:10px;"><i class="fa-solid fa-info-circle"></i> No exact matches found for ${escapeHtml(data.specialization)} right now.</div>`;
        }

        chatBox.innerHTML += `<div class='${botMessageClass}'>${botContent}</div>`;
        scrollToBottom(chatBox);
        saveChatHistory();

    } catch (error) {
        typingIndicator.remove();
        chatBox.innerHTML += `<div class='message bot urgency-high'>
            <span class="urgency-badge">Error</span><br>
            Server error connecting to diagnostic engine. Try again later.
        </div>`;
        scrollToBottom(chatBox);
        saveChatHistory();
    }
}

function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.innerText = text;
    return div.innerHTML;
}

function scrollToBottom(el) {
    el.scrollTop = el.scrollHeight;
}
</script>

</body>
</html>