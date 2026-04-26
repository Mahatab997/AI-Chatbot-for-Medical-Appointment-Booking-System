<?php
include "../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "patient") {
    header("Location: ../auth/login.php");
    exit();
}

$uid = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$appointment_id = (int)$_GET['id'];

/* FETCH APPOINTMENT */
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id=? AND user_id=?");
$stmt->execute([$appointment_id, $uid]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    die("Appointment not found.");
}

/* TIME SLOTS */
$all_slots = ["09:00 AM","10:00 AM","11:00 AM","12:00 PM","01:00 PM","02:00 PM","03:00 PM","04:00 PM","05:00 PM"];

/* BOOKED SLOTS */
$booked_slots = [];
$date_to_check = $_POST['appointment_date'] ?? $appointment['appointment_date'];

$stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE doctor_id=? AND appointment_date=? AND id != ?");
$stmt->execute([$appointment['doctor_id'], $date_to_check, $appointment_id]);
$booked_slots = $stmt->fetchAll(PDO::FETCH_COLUMN);

/* DEFAULT SELECTED TIME */
$selected_time = $appointment['appointment_time'];
if (in_array($selected_time, $booked_slots)) {
    foreach($all_slots as $slot) {
        if (!in_array($slot, $booked_slots)) {
            $selected_time = $slot;
            break;
        }
    }
}

/* UPDATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_date = $_POST['appointment_date'];
    $new_time = $_POST['appointment_time'];

    $stmt = $pdo->prepare("UPDATE appointments 
        SET appointment_date=?, appointment_time=?, status='Pending' 
        WHERE id=? AND user_id=?");
    $stmt->execute([$new_date, $new_time, $appointment_id, $uid]);

    header("Location: my_appointments.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reschedule Appointment | Medical AI Pro</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

/* BACKGROUND */
body{
    font-family:'Poppins',sans-serif;
    margin:0;
    background:linear-gradient(-45deg,#0f2027,#203a43,#2c5364,#0077b6);
    background-size:400% 400%;
    animation:gradientBG 12s ease infinite;
}

@keyframes gradientBG{
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

/* HEADER */
.header{
    padding:25px;
    text-align:center;
    color:white;
    font-size:26px;
    font-weight:600;
}

/* CONTAINER */
.container{
    display:flex;
    justify-content:center;
    padding:30px 15px;
}

/* CARD */
.card{
    width:100%;
    max-width:520px;
    padding:40px;
    border-radius:18px;
    background:rgba(255,255,255,0.10);
    backdrop-filter:blur(20px);
    border:1px solid rgba(255,255,255,0.2);
    box-shadow:0 10px 40px rgba(0,0,0,0.4);
    color:white;
}

/* TITLE */
.card h2{
    text-align:center;
    margin-bottom:25px;
}

/* FORM */
.form-group{
    margin-bottom:18px;
}

label{
    display:block;
    margin-bottom:6px;
    font-weight:500;
}

input, select{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    outline:none;
    font-size:14px;
}

/* BUTTON */
.btn-submit{
    width:100%;
    padding:14px;
    background:#00b4d8;
    border:none;
    border-radius:10px;
    color:black;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

.btn-submit:hover{
    background:#0096c7;
}

/* BACK BUTTON (IMPORTANT PART) */
.back-link{
    display:inline-block;
    margin-top:18px;
    text-align:center;
    width:100%;
    padding:10px 18px;
    border-radius:30px;
    text-decoration:none;
    font-weight:600;
    color:white;
    background:linear-gradient(135deg,#1abc9c,#0f4c81,#00b4d8);
    box-shadow:0 6px 15px rgba(0,0,0,0.25);
    transition:0.3s;
}

.back-link:hover{
    transform:translateY(-3px);
    box-shadow:0 10px 20px rgba(0,0,0,0.3);
}

</style>
</head>

<body>

<div class="header">
    Reschedule Appointment
</div>

<div class="container">

<div class="card">

    <h2>Update Schedule</h2>

    <form method="post">

        <div class="form-group">
            <label>Appointment Date</label>
            <input type="date" name="appointment_date"
            value="<?= htmlspecialchars($appointment['appointment_date']); ?>" required>
        </div>

        <div class="form-group">
            <label>Time Slot</label>
            <select name="appointment_time" required>
                <option value="">Select Time</option>
                <?php
                foreach($all_slots as $slot){
                    if(!in_array($slot, $booked_slots) || $slot == $appointment['appointment_time']){
                        $sel = ($slot == $selected_time) ? "selected" : "";
                        echo "<option value='$slot' $sel>$slot</option>";
                    }
                }
                ?>
            </select>
        </div>

        <button class="btn-submit" type="submit">Update Appointment</button>

    </form>

    <a href="my_appointments.php" class="back-link">← Back to My Appointments</a>

</div>

</div>

</body>
</html>