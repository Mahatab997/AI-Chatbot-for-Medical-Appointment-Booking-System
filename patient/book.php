<?php
include "../config/database.php";

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "patient") {
    header("Location: ../auth/login.php");
    exit();
}

$pre_doctor_id = $_GET['doctor_id'] ?? '';
$pre_spec = $_GET['spec'] ?? '';

$uid = $_SESSION['user_id'];
$appointmentBooked = false;

/* AJAX */
if (isset($_GET['type'])) {
    $stmt = $conn->prepare("SELECT * FROM doctors WHERE specialization=?");
    $stmt->bind_param("s", $_GET['type']);
    $stmt->execute();
    $res = $stmt->get_result();

    $data=[];
    while($row=$res->fetch_assoc()) $data[]=$row;

    echo json_encode($data);
    exit();
}

/* Cancel */
if(isset($_GET['cancel_id'])){
    $stmt=$conn->prepare("DELETE FROM appointments WHERE id=? AND user_id=?");
    $stmt->bind_param("ii",$_GET['cancel_id'],$uid);
    $stmt->execute();
    header("Location: book.php"); exit();
}

/* Specialization */
$typeQuery=$conn->query("SELECT DISTINCT specialization FROM doctors");

/* Book */
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $doctor_id=$_POST['doctor_id'];
    $date=$_POST['appointment_date'];
    $time=$_POST['appointment_time'];

    $check=$conn->prepare("SELECT id FROM appointments WHERE doctor_id=? AND appointment_date=? AND appointment_time=?");
    $check->bind_param("iss",$doctor_id,$date,$time);
    $check->execute();
    $check->store_result();

    if($check->num_rows==0){
        $stmt=$conn->prepare("INSERT INTO appointments(user_id,doctor_id,appointment_date,appointment_time) VALUES(?,?,?,?)");
        $stmt->bind_param("iiss",$uid,$doctor_id,$date,$time);
        $stmt->execute();
        $appointmentBooked=true;
    } else {
        echo "<script>alert('Slot already booked');</script>";
    }
}

/* My Appointments */
$app=$conn->prepare("
SELECT a.id,a.appointment_date,a.appointment_time,d.name,d.specialization
FROM appointments a
JOIN doctors d ON a.doctor_id=d.id
WHERE a.user_id=? ORDER BY a.appointment_date DESC
");
$app->bind_param("i",$uid);
$app->execute();
$result=$app->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Appointment</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}

:root {
    --bg-main: #0B1120;
    --bg-secondary: #1E293B;
    --bg-card: #334155;
    --accent: #38BDF8;
    --accent-hover: #0EA5E9;
    --text-primary: #F8FAFC;
    --text-secondary: #94A3B8;
    --danger: #F43F5E;
    --border-color: rgba(248, 250, 252, 0.08);
}

body{
    background: var(--bg-main);
    color: var(--text-primary);
    min-height: 100vh;
}

/* Layout */
.container{max-width:1100px;margin:auto;padding:30px 20px;}

/* Header */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background: var(--bg-secondary);
    padding:18px 25px;
    border-radius:16px;
    margin-bottom:25px;
    border:1px solid var(--border-color);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.header h2{color: var(--accent); font-size: 20px;}

.nav-btns a{
    text-decoration:none;
    padding:10px 20px;
    border-radius:10px;
    font-size:14px;
    font-weight:500;
    margin-left:10px;
    transition: 0.2s;
}

.btn-dashboard{
    background: var(--accent); color: #0B1120;
}
.btn-dashboard:hover { background: var(--accent-hover); }

.btn-logout{
    background: transparent; color: var(--danger);
    border: 1px solid var(--danger);
}
.btn-logout:hover { background: rgba(244, 63, 94, 0.1); }

/* Card */
.card{
    background: var(--bg-secondary);
    padding:25px;
    border-radius:16px;
    border:1px solid var(--border-color);
    margin-bottom:25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.card h3 {
    font-size: 18px;
    margin-bottom: 15px;
    color: var(--text-primary);
}

/* Doctor Cards */
.doctor-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
    margin-top:12px;
}

.doctor-card{
    border:1px solid var(--border-color);
    padding:15px;
    border-radius:12px;
    cursor:pointer;
    transition:0.2s;
    background: var(--bg-main);
}

.doctor-card:hover{
    border-color: rgba(56, 189, 248, 0.3);
    transform:translateY(-3px);
    box-shadow:0 8px 20px rgba(0,0,0,0.2);
}

.doctor-card.active{
    border:2px solid var(--accent);
    background: rgba(56, 189, 248, 0.08);
}

.doctor-name{font-weight:600;font-size:15px; color: var(--text-primary);}
.doctor-spec{font-size:13px;color: var(--accent); opacity: 0.85; margin-top: 3px;}
.rating{color:#f1c40f;font-size:13px;margin-top:5px;}

/* Form Labels */
label {
    display: block;
    margin-top: 18px;
    margin-bottom: 4px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-secondary);
}

/* Form Inputs & Selects — FIXED FOR VISIBILITY */
input, select {
    width:100%;
    padding:12px 15px;
    margin-top:6px;
    border-radius:10px;
    border: 1px solid var(--border-color);
    outline:none;
    background: var(--bg-main);
    color: var(--text-primary);
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    transition: 0.2s;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

/* Custom dropdown arrow for selects */
select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394A3B8' d='M6 8.825L1.175 4 2.238 2.938 6 6.7 9.763 2.937 10.825 4z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 36px;
}

/* CRITICAL: Make dropdown options readable on ALL platforms */
select option {
    background: #1E293B;
    color: #F8FAFC;
    padding: 10px;
}

input:focus, select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.15);
}

input::placeholder{color: var(--text-secondary);}

/* Date input calendar icon fix */
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0.7);
    cursor: pointer;
}

button{
    width:100%;
    margin-top:20px;
    padding:14px;
    background: var(--accent);
    color: #0B1120;
    border:none;
    border-radius:12px;
    font-weight:600;
    font-size: 15px;
    cursor:pointer;
    transition:0.2s;
    font-family: 'Poppins', sans-serif;
}

button:hover{
    background: var(--accent-hover);
    transform:translateY(-2px);
}

/* Table */
table{width:100%;border-collapse:collapse;margin-top:15px;}
th{
    background: rgba(56, 189, 248, 0.1);
    padding:12px 10px;
    text-align: left;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--accent);
    font-weight: 600;
}
td{
    padding:12px 10px;
    border-bottom:1px solid var(--border-color);
    font-size: 14px;
    color: var(--text-primary);
}

.cancel-btn{
    background: transparent;
    color: var(--danger);
    border: 1px solid var(--danger);
    padding:6px 14px;
    border-radius:8px;
    text-decoration:none;
    font-size: 13px;
    font-weight: 500;
    transition:0.2s;
}

.cancel-btn:hover{
    background: rgba(244, 63, 94, 0.1);
}

/* Success */
.success{
    background: rgba(16, 185, 129, 0.1);
    color: #10B981;
    padding:18px;
    border-radius:12px;
    border: 1px solid rgba(16, 185, 129, 0.2);
    font-weight: 500;
}

/* Responsive */
@media(max-width:600px){
    .header{flex-direction:column;gap:10px;}
}
</style>
</head>

<body>

<div class="container">

<div class="header">
<h2>🏥 Medical AI Pro</h2>

<div class="nav-btns">
<a href="dashboard.php" class="btn-dashboard">Dashboard</a>
<a href="../auth/logout.php" class="btn-logout">Logout</a>
</div>
</div>

<?php if($appointmentBooked): ?>
<div class="card success">✅ Appointment booked successfully</div>
<?php endif; ?>

<div class="card">
<h3>📅 Book Appointment</h3>

<form method="POST">

<label>Specialization</label>
<select id="specialization" required>
<option value="" disabled selected>Select specialization</option>
<?php while($row=$typeQuery->fetch_assoc()): ?>
<option value="<?= $row['specialization'] ?>" <?= ($pre_spec==$row['specialization'])?"selected":"" ?>>
<?= $row['specialization'] ?>
</option>
<?php endwhile; ?>
</select>

<div class="doctor-grid" id="doctorGrid"></div>

<input type="hidden" name="doctor_id" id="doctor_id" required>

<label>Date</label>
<input type="date" name="appointment_date" required>

<label>Time</label>
<select name="appointment_time" required>
<option>09:00 AM</option>
<option>10:00 AM</option>
<option>11:00 AM</option>
<option>02:00 PM</option>
<option>03:00 PM</option>
<option>04:00 PM</option>
</select>

<button>Confirm Appointment</button>

</form>
</div>

<div class="card">
<h3>📋 My Appointments</h3>

<table>
<tr>
<th>Date</th><th>Time</th><th>Doctor</th><th>Spec</th><th>Action</th>
</tr>

<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td><?= $row['appointment_date'] ?></td>
<td><?= $row['appointment_time'] ?></td>
<td><?= $row['name'] ?></td>
<td><?= $row['specialization'] ?></td>
<td><a class="cancel-btn" href="?cancel_id=<?= $row['id'] ?>">Cancel</a></td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div>

<script>
function loadDoctors(type, selected=""){
fetch("book.php?type="+type)
.then(res=>res.json())
.then(data=>{
let grid=document.getElementById("doctorGrid");
grid.innerHTML="";

data.forEach(doc=>{
let active=(doc.id==selected)?"active":"";

grid.innerHTML+=`
<div class="doctor-card ${active}" onclick="selectDoctor(${doc.id},this)">
<div class="doctor-name">👨‍⚕️ ${doc.name}</div>
<div class="doctor-spec">${doc.specialization}</div>
<div class="rating">⭐ ${doc.rating}</div>
</div>`;
});

if(selected){
document.getElementById("doctor_id").value=selected;
}
});
}

function selectDoctor(id,el){
document.getElementById("doctor_id").value=id;
document.querySelectorAll(".doctor-card").forEach(c=>c.classList.remove("active"));
el.classList.add("active");
}

document.getElementById("specialization").addEventListener("change",function(){
loadDoctors(this.value);
});

window.onload=function(){
let spec="<?= $pre_spec ?>";
let doc="<?= $pre_doctor_id ?>";
if(spec){loadDoctors(spec,doc);}
}
</script>

</body>
</html>