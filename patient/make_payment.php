<?php 
include "../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "patient") {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid Request");
}

$appointment_id = $_GET['id'];
$uid = $_SESSION['user_id'];

/* FETCH APPOINTMENT */
$stmt = $pdo->prepare("
    SELECT a.*, d.name AS doctor_name, d.specialization
    FROM appointments a
    LEFT JOIN doctors d ON a.doctor_id = d.id
    WHERE a.id = ? AND a.user_id = ?
");
$stmt->execute([$appointment_id, $uid]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    die("Appointment not found");
}

/* BILL CALCULATION (STATIC HOSPITAL MODEL) */
$doctor_fee = 500;
$service_fee = 50;
$total = $doctor_fee + $service_fee;

/* PAYMENT PROCESS */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE appointments SET status='Paid' WHERE id=?");
    $stmt->execute([$appointment_id]);

    header("Location: my_appointments.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Medical Payment Invoice</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Poppins',sans-serif;
    background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
    color:white;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    margin:0;
}

/* MAIN CARD */
.card{
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(20px);
    padding:30px;
    border-radius:18px;
    width:420px;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
    border:1px solid rgba(255,255,255,0.2);
}

/* TITLE */
.card h2{
    text-align:center;
    margin-bottom:20px;
    color:#1abc9c;
}

/* DETAILS */
.info{
    margin-bottom:15px;
    font-size:14px;
}

.label{
    color:#1abc9c;
    font-weight:500;
}

/* BILL BOX */
.bill{
    background:rgba(255,255,255,0.05);
    padding:15px;
    border-radius:12px;
    margin-top:15px;
    font-size:14px;
}

.bill div{
    display:flex;
    justify-content:space-between;
    margin:8px 0;
}

.total{
    border-top:1px solid rgba(255,255,255,0.2);
    margin-top:10px;
    padding-top:10px;
    font-weight:bold;
    color:#f1c40f;
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    margin-top:20px;
    background:linear-gradient(120deg,#1abc9c,#16a085);
    border:none;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
    color:white;
    font-size:15px;
    transition:0.3s;
}

button:hover{
    transform:scale(1.03);
}

/* SMALL NOTE */
.note{
    text-align:center;
    font-size:12px;
    margin-top:10px;
    opacity:0.7;
}
</style>
</head>

<body>

<div class="card">

    <h2>💳 Payment Invoice</h2>

    <div class="info">
        <span class="label">Doctor:</span> Dr. <?= htmlspecialchars($appointment['doctor_name']) ?>
    </div>

    <div class="info">
        <span class="label">Specialization:</span> <?= htmlspecialchars($appointment['specialization']) ?>
    </div>

    <div class="info">
        <span class="label">Appointment Date:</span> <?= $appointment['appointment_date'] ?>
    </div>

    <div class="info">
        <span class="label">Appointment Time:</span> <?= $appointment['appointment_time'] ?>
    </div>

    <!-- BILL SECTION -->
    <div class="bill">

        <div>
            <span>Doctor Fee</span>
            <span>৳ <?= $doctor_fee ?></span>
        </div>

        <div>
            <span>Service Charge</span>
            <span>৳ <?= $service_fee ?></span>
        </div>

        <div class="total">
            <span>Total Amount</span>
            <span>৳ <?= $total ?></span>
        </div>

    </div>

    <!-- PAYMENT BUTTON -->
    <form method="POST">
        <button type="submit">Pay Securely Now</button>
    </form>

    <div class="note">
        Secure hospital billing system • Medical AI Pro
    </div>

</div>

</body>
</html>