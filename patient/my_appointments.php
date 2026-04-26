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

if (!isset($pdo)) {
    die("Database connection error. Please check database.php file.");
}

/* FETCH APPOINTMENTS */
$stmt = $pdo->prepare("
    SELECT 
        a.id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        d.name AS doctor_name,
        dep.name AS department_name
    FROM appointments a
    LEFT JOIN doctors d ON a.doctor_id = d.id
    LEFT JOIN departments dep ON a.department_id = dep.id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");

$stmt->execute([$uid]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Appointments | Medical AI Pro</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body{
    font-family:'Poppins',sans-serif;
    background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
    margin:0;
    color:#fff;
}

.header{
    background:rgba(26,188,156,0.9);
    padding:20px;
    text-align:center;
}

.header h1{
    margin:0;
}

.container{
    padding:40px 8%;
}

.card{
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(20px);
    padding:25px;
    border-radius:18px;
    border:1px solid rgba(255,255,255,0.2);
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

th{
    text-align:left;
    padding:12px;
    background:rgba(26,188,156,0.2);
    color:#1abc9c;
}

td{
    padding:12px;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

/* BADGE */
.badge{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
}

.pending{background:orange;}
.approved{background:green;}
.cancelled{background:red;}

/* BUTTONS */
.btn{
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    font-size:12px;
    margin-right:5px;
    display:inline-block;
}

.reschedule{background:#3498db;color:white;}
.cancel{background:#e74c3c;color:white;}
.pay{background:#f1c40f;color:#000;font-weight:600;}

.pay:hover{background:#f39c12;}

/* FOOTER BUTTONS */
.footer-buttons{
    text-align:center;
    margin-top:20px;
}

.footer-buttons a{
    padding:10px 18px;
    border-radius:8px;
    text-decoration:none;
    margin:5px;
    display:inline-block;
    background:#1abc9c;
    color:white;
}
</style>
</head>

<body>

<div class="header">
    <h1>My Appointments</h1>
</div>

<div class="container">
<div class="card">

<table>
<tr>
    <th>Date</th>
    <th>Time</th>
    <th>Doctor</th>
    <th>Department</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php if(count($appointments) > 0): ?>
    <?php foreach($appointments as $a): ?>
    <tr>
        <td><?= htmlspecialchars($a['appointment_date']); ?></td>
        <td><?= htmlspecialchars($a['appointment_time']); ?></td>
        <td>Dr. <?= htmlspecialchars($a['doctor_name'] ?? 'N/A'); ?></td>
        <td><?= htmlspecialchars($a['department_name'] ?? 'N/A'); ?></td>
        <td>
            <span class="badge <?= strtolower($a['status']); ?>">
                <?= ucfirst($a['status']); ?>
            </span>
        </td>

        <td>
            <a href="reschedule.php?id=<?= $a['id']; ?>" class="btn reschedule">
                <i class="fa fa-calendar"></i> Reschedule
            </a>

            <a href="cancel.php?id=<?= $a['id']; ?>" class="btn cancel"
               onclick="return confirm('Cancel this appointment?')">
                <i class="fa fa-ban"></i> Cancel
            </a>

            <!-- NEW PAYMENT BUTTON -->
            <a href="make_payment.php?id=<?= $a['id']; ?>" class="btn pay">
                <i class="fa fa-credit-card"></i> Make Payment
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="6" style="text-align:center;">No Appointments Found</td>
</tr>
<?php endif; ?>

</table>

<div class="footer-buttons">
    <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="../auth/logout.php"><i class="fa fa-right-from-bracket"></i> Logout</a>
</div>

</div>
</div>

</body>
</html>