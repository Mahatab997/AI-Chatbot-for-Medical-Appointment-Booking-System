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
*{margin:0;padding:0;box-sizing:border-box;}

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
    font-family:'Poppins',sans-serif;
    background: var(--bg-main);
    margin:0;
    color: var(--text-primary);
    min-height: 100vh;
}

/* Header */
.header{
    background: var(--bg-secondary);
    padding: 22px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.header h1{
    margin:0;
    font-size: 22px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.header h1 i { color: var(--accent); }

.container{
    padding: 35px 8%;
    max-width: 1200px;
    margin: auto;
}

.card{
    background: var(--bg-secondary);
    padding: 25px;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

th{
    text-align:left;
    padding: 14px 12px;
    background: rgba(56, 189, 248, 0.1);
    color: var(--accent);
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

td{
    padding: 14px 12px;
    border-bottom: 1px solid var(--border-color);
    font-size: 14px;
    color: var(--text-primary);
}

/* BADGE */
.badge{
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.pending{background: rgba(251, 191, 36, 0.15); color: #FBBF24;}
.approved{background: rgba(16, 185, 129, 0.15); color: #10B981;}
.cancelled{background: rgba(244, 63, 94, 0.15); color: var(--danger);}

/* ACTION BUTTONS */
.action-cell {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: nowrap;
}

.btn{
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: 0.2s;
    border: 1px solid transparent;
    white-space: nowrap;
}

.reschedule{
    background: transparent;
    color: var(--accent);
    border-color: var(--accent);
}
.reschedule:hover { background: rgba(56, 189, 248, 0.1); }

.cancel{
    background: transparent;
    color: var(--danger);
    border-color: var(--danger);
}
.cancel:hover { background: rgba(244, 63, 94, 0.1); }

.pay{
    background: rgba(251, 191, 36, 0.15);
    color: #FBBF24;
    border-color: rgba(251, 191, 36, 0.3);
    font-weight: 600;
}
.pay:hover { background: rgba(251, 191, 36, 0.25); }

/* FOOTER BUTTONS */
.footer-buttons{
    text-align: center;
    margin-top: 25px;
    display: flex;
    justify-content: center;
    gap: 12px;
}

.footer-buttons a{
    padding: 12px 22px;
    border-radius: 10px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: 0.2s;
}

.footer-buttons a:first-child {
    background: var(--accent);
    color: #0B1120;
}
.footer-buttons a:first-child:hover { background: var(--accent-hover); }

.footer-buttons a:last-child {
    background: transparent;
    color: var(--danger);
    border: 1px solid var(--danger);
}
.footer-buttons a:last-child:hover { background: rgba(244, 63, 94, 0.1); }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-secondary);
}

/* Responsive */
@media(max-width: 768px){
    .header { flex-direction: column; gap: 10px; text-align: center; }
    .container { padding: 20px 4%; }
    table { font-size: 13px; }
    .btn { padding: 5px 8px; font-size: 11px; }
    .footer-buttons { flex-direction: column; align-items: center; }
}
</style>
</head>

<body>

<div class="header">
    <h1><i class="fa-solid fa-list-check"></i> My Appointments</h1>
</div>

<div class="container">
<div class="card">

<table>
<tr>
    <th>Date</th><th>Time</th><th>Doctor</th><th>Department</th><th>Status</th><th>Action</th>
</tr>

<?php if(count($appointments) > 0): ?>
    <?php foreach($appointments as $a): ?>
    <tr>
        <td><?= htmlspecialchars($a['appointment_date']); ?></td>
        <td><?= htmlspecialchars($a['appointment_time']); ?></td>
        <td><?= htmlspecialchars($a['doctor_name'] ?? 'N/A'); ?></td>
        <td><?= htmlspecialchars($a['department_name'] ?? 'N/A'); ?></td>
        <td>
            <span class="badge <?= strtolower($a['status']); ?>">
                <?= ucfirst($a['status']); ?>
            </span>
        </td>

        <td>
            <div class="action-cell">
                <a href="reschedule.php?id=<?= $a['id']; ?>" class="btn reschedule">
                    <i class="fa fa-calendar"></i> Reschedule
                </a>
                <a href="cancel.php?id=<?= $a['id']; ?>" class="btn cancel"
                   onclick="return confirm('Cancel this appointment?')">
                    <i class="fa fa-ban"></i> Cancel
                </a>
                <a href="make_payment.php?id=<?= $a['id']; ?>" class="btn pay">
                    <i class="fa fa-credit-card"></i> Pay
                </a>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="6" class="empty-state">
        <i class="fa-solid fa-calendar-xmark" style="font-size:24px; display:block; margin-bottom:10px; color:var(--accent);"></i>
        No Appointments Found
    </td>
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