<?php
session_start();
include "../config/database.php";

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && $password == $admin['password']) {

        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['role'] = 'admin';

        header("Location: dashboard.php");
        exit();

    } else {
        $error = "Invalid admin email or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Login | Medical AI Pro</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>

/* RESET */
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

/* SAME BACKGROUND AS USER LOGIN */
body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(-45deg,#0f2027,#203a43,#2c5364,#0077b6);
    background-size:400% 400%;
    animation:gradientBG 12s ease infinite;
}

@keyframes gradientBG {
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

/* GLASS CARD */
.box{
    width:380px;
    padding:40px;
    border-radius:20px;
    backdrop-filter:blur(25px);
    background:rgba(255,255,255,0.08);
    border:1px solid rgba(255,255,255,0.2);
    box-shadow:0 10px 40px rgba(0,0,0,0.4);
    color:white;
}

/* TITLE */
.box h2{
    text-align:center;
    font-family:'Playfair Display',serif;
    margin-bottom:20px;
}

/* INPUTS */
input{
    width:100%;
    padding:14px;
    margin:10px 0;
    border:none;
    border-radius:10px;
    outline:none;
}

/* BUTTON */
button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:#00b4d8;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    background:#0096c7;
}

/* ERROR */
.error{
    color:#ffb3b3;
    text-align:center;
    margin-bottom:10px;
}

</style>

</head>

<body>

<div class="box">

    <h2>Admin Login</h2>

    <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Admin Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>

</div>

</body>
</html>