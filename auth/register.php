<?php
include "../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";
$success = false;

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($name) || empty($email) || empty($password)){
        $message = "All fields are required!";
    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s",$email);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $message = "Email already registered!";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
            $stmt->bind_param("sss",$name,$email,$hashed_password);

            if($stmt->execute()){
                $success = true;
                $message = "Registration successful! You can login now.";
            } else {
                $message = "Registration failed!";
            }
        }
    }
}

$logoPath = "../uploads/logo.png";
$logoExists = file_exists($logoPath);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medical AI Pro | Register</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter', sans-serif;
}

body{
    height:100vh;
    display:flex;
    background:#0f172a;
    overflow:hidden;
}

/* LEFT BRANDING PANEL */
.left-panel{
    width:50%;
    background:linear-gradient(135deg,#0f2027,#1e3c72,#2a5298);
    display:flex;
    justify-content:center;
    align-items:center;
    flex-direction:column;
    text-align:center;
    color:white;
    padding:40px;
}

.left-panel img{
    max-width:160px;
    margin-bottom:25px;
}

.left-panel h1{
    font-size:36px;
    font-weight:700;
    margin-bottom:15px;
}

.left-panel p{
    font-size:18px;
    opacity:0.9;
}

/* RIGHT REGISTER PANEL */
.right-panel{
    width:50%;
    display:flex;
    justify-content:center;
    align-items:center;
}

.register-card{
    width:380px;
    padding:40px;
    border-radius:16px;
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(25px);
    border:1px solid rgba(255,255,255,0.1);
    color:white;
}

.register-card h2{
    text-align:center;
    margin-bottom:25px;
    font-weight:600;
}

.input-group{
    margin-bottom:18px;
}

.input-group input{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    outline:none;
}

.toggle-password{
    position:absolute;
    right:15px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    font-size:13px;
}

button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#00b4d8;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    background:#0096c7;
}

.message{
    text-align:center;
    margin-bottom:15px;
    font-size:14px;
}

.success{ color:#00ffae; }
.error{ color:#ff6b6b; }

a{
    color:#00b4d8;
    text-decoration:none;
}

a:hover{
    text-decoration:underline;
}

/* Password Strength */
.strength{
    height:5px;
    border-radius:5px;
    margin-top:5px;
}

/* Responsive */
@media(max-width:900px){
    .left-panel{ display:none; }
    .right-panel{ width:100%; }
}

</style>
</head>
<body>

<div class="left-panel">
    <?php if($logoExists): ?>
        <img src="<?php echo $logoPath; ?>" alt="Hospital Logo">
    <?php endif; ?>
    <h1>Medical AI Pro</h1>
    <p>Create your secure healthcare account today.</p>
</div>

<div class="right-panel">
    <div class="register-card">

        <h2>Create Account</h2>

        <?php if($message!=""): ?>
            <div class="message <?php echo $success ? 'success':'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="input-group">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>

            <div class="input-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="input-group" style="position:relative;">
                <input type="password" name="password" id="password" placeholder="Password" required onkeyup="checkStrength()">
                <span class="toggle-password" onclick="togglePassword()">Show</span>
                <div class="strength" id="strengthBar"></div>
            </div>

            <button type="submit">Register</button>

        </form>

        <p style="text-align:center;margin-top:15px;font-size:14px;">
            Already have an account? <a href="login.php">Login</a>
        </p>

    </div>
</div>

<script>

function togglePassword(){
    let pass=document.getElementById("password");
    pass.type = pass.type==="password" ? "text":"password";
}

function checkStrength(){
    let password=document.getElementById("password").value;
    let bar=document.getElementById("strengthBar");

    if(password.length < 6){
        bar.style.background="red";
        bar.style.width="30%";
    } else if(password.length < 10){
        bar.style.background="orange";
        bar.style.width="60%";
    } else {
        bar.style.background="limegreen";
        bar.style.width="100%";
    }
}

</script>

</body>
</html>