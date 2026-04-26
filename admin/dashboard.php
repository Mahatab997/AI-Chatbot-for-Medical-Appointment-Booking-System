<?php
session_start();
include "../config/database.php";

/* SECURITY */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

/* DELETE */
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    header("Location: dashboard.php");
    exit();
}

/* APPROVE */
if (isset($_GET['approve'])) {
    $stmt = $pdo->prepare("UPDATE appointments SET status='Approved' WHERE id=?");
    $stmt->execute([$_GET['approve']]);
    header("Location: dashboard.php");
    exit();
}

/* REJECT */
if (isset($_GET['reject'])) {
    $stmt = $pdo->prepare("UPDATE appointments SET status='Rejected' WHERE id=?");
    $stmt->execute([$_GET['reject']]);
    header("Location: dashboard.php");
    exit();
}

/* SEARCH */
$search = $_GET['search'] ?? '';

$sql = "
SELECT 
    a.id,
    a.appointment_date,
    a.appointment_time,
    a.status,
    u.name AS patient_name,
    d.name AS doctor_name
FROM appointments a
JOIN users u ON a.user_id = u.id
JOIN doctors d ON a.doctor_id = d.id
WHERE u.name LIKE :search OR d.name LIKE :search
ORDER BY a.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<style>
body{
    font-family:'Poppins', Arial;
    background:#f4f7fb;
    margin:0;
}

.header{
    background:linear-gradient(90deg,#0f4c81,#1abc9c);
    color:white;
    padding:18px 8%;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.container{
    padding:30px 8%;
}

.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.search-box input{
    padding:10px 15px;
    width:280px;
    border:1px solid #ddd;
    border-radius:8px;
}

.export-btn{
    padding:10px 16px;
    background:#0f4c81;
    color:white;
    text-decoration:none;
    border-radius:8px;
}

/* TABLE */
table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
    background:white;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 8px 25px rgba(0,0,0,0.08);
}

th{
    background:#1abc9c;
    color:white;
    padding:14px;
    text-align:left;
}

td{
    padding:14px;
    border-bottom:1px solid #eee;
}

tr:hover{
    background:#f2fbff;
}

/* STATUS */
.status{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    color:white;
}

.Pending{ background:orange; }
.Approved{ background:green; }
.Rejected{ background:red; }

/* ACTION */
td.action-cell{
    text-align:center;
    white-space:nowrap;
}

.btn{
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    font-size:12px;
    margin:0 2px;
}

.approve{ background:#2ecc71; color:white; }
.reject{ background:#f39c12; color:white; }
.delete{ background:#e74c3c; color:white; }

</style>

</head>

<body>

<div class="header">
    <h2>Medical AI Pro - Admin Dashboard</h2>
    <a href="logout.php" style="color:white;text-decoration:none;">Logout</a>
</div>

<div class="container">

<div class="top-bar">
    <form class="search-box" method="GET">
        <input type="text" name="search" placeholder="Search patient or doctor..." value="<?= $search ?>">
    </form>

    <a href="#" onclick="window.print()" class="export-btn">📄 Export PDF</a>
</div>

<h3 style="margin-bottom:15px;">All Appointments</h3>

<table>
<tr>
    <th>ID</th>
    <th>Patient</th>
    <th>Doctor</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
    <th style="text-align:center;">Action</th>
</tr>

<?php 
$serial = 1;   // ⭐ SEQUENTIAL NUMBER START
foreach($appointments as $a): 
?>
<tr>
    <!-- FIXED SEQUENTIAL ID -->
    <td><?= $serial++ ?></td>

    <td><?= $a['patient_name'] ?></td>
    <td><?= $a['doctor_name'] ?></td>
    <td><?= $a['appointment_date'] ?></td>
    <td><?= $a['appointment_time'] ?></td>

    <td>
        <span class="status <?= $a['status'] ?>">
            <?= $a['status'] ?>
        </span>
    </td>

    <td class="action-cell">
        <a class="btn approve" href="?approve=<?= $a['id'] ?>">Approve</a>
        <a class="btn reject" href="?reject=<?= $a['id'] ?>">Reject</a>
        <a class="btn delete" href="?delete=<?= $a['id'] ?>" onclick="return confirm('Delete this appointment?')">Delete</a>
    </td>
</tr>
<?php endforeach; ?>

</table>

</div>

</body>
</html>