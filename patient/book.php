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

body{
background:linear-gradient(135deg,#0a1f44,#1b4f72,#5dade2);
color:white;
}

/* Layout */
.container{max-width:1100px;margin:auto;padding:20px;}

/* Header */
.header{
display:flex;
justify-content:space-between;
align-items:center;
background:rgba(255,255,255,0.1);
backdrop-filter:blur(10px);
padding:15px 20px;
border-radius:15px;
margin-bottom:20px;
border:1px solid rgba(255,255,255,0.2);
}

.header h2{color:#85c1e9;}

.nav-btns a{
text-decoration:none;
padding:8px 16px;
border-radius:8px;
font-size:14px;
font-weight:500;
margin-left:10px;
}

.btn-dashboard{
background:#3498db;color:white;
}
.btn-logout{
background:#e74c3c;color:white;
}

/* Card */
.card{
background:rgba(255,255,255,0.08);
backdrop-filter:blur(15px);
padding:20px;
border-radius:15px;
border:1px solid rgba(255,255,255,0.2);
margin-bottom:20px;
}

/* Doctor Cards */
.doctor-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:15px;
margin-top:10px;
}

.doctor-card{
border:1px solid rgba(255,255,255,0.2);
padding:15px;
border-radius:12px;
cursor:pointer;
transition:0.3s;
background:rgba(255,255,255,0.05);
}

.doctor-card:hover{
border-color:#5dade2;
transform:translateY(-5px);
box-shadow:0 5px 15px rgba(0,0,0,0.3);
}

.doctor-card.active{
border:2px solid #5dade2;
background:rgba(93,173,226,0.2);
}

.doctor-name{font-weight:600;font-size:15px;}
.doctor-spec{font-size:13px;color:#d6eaf8;}
.rating{color:#f1c40f;font-size:13px;margin-top:5px;}

/* Form */
input,select{
width:100%;
padding:10px;
margin-top:8px;
border-radius:8px;
border:none;
outline:none;
background:rgba(255,255,255,0.1);
color:white;
}

input::placeholder{color:#ccc;}

button{
width:100%;
margin-top:15px;
padding:12px;
background:linear-gradient(135deg,#3498db,#5dade2);
color:white;
border:none;
border-radius:10px;
font-weight:600;
cursor:pointer;
transition:0.3s;
}

button:hover{
background:linear-gradient(135deg,#2e86c1,#3498db);
transform:scale(1.02);
}

/* Table */
table{width:100%;border-collapse:collapse;margin-top:10px;}
th{
background:rgba(93,173,226,0.2);
padding:10px;
}
td{
padding:10px;
border-bottom:1px solid rgba(255,255,255,0.2);
}

.cancel-btn{
background:#e74c3c;
color:white;
padding:6px 12px;
border-radius:6px;
text-decoration:none;
transition:0.3s;
}

.cancel-btn:hover{
background:#c0392b;
}

/* Success */
.success{
background:rgba(46,204,113,0.2);
color:#2ecc71;
padding:15px;
border-radius:10px;
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