<?php
include "../config/database.php";

if($_SESSION['role']!="admin"){
    header("Location: ../auth/login.php");
}

$result=$conn->query("SELECT a.*,u.name FROM appointments a 
JOIN users u ON a.user_id=u.id");
?>

<table border="1">
<tr>
<th>Patient</th>
<th>Doctor</th>
<th>Date</th>
<th>Status</th>
</tr>

<?php while($row=$result->fetch_assoc()){ ?>
<tr>
<td><?= $row['name'] ?></td>
<td><?= $row['doctor'] ?></td>
<td><?= $row['appointment_date'] ?></td>
<td><?= $row['status'] ?></td>
</tr>
<?php } ?>
</table>
