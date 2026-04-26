<?php
include "../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";

// if redirected from logout, show confirmation message
if(isset($_GET['logged_out'])){
    $message = "You have been logged out successfully.";
}

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows>0){
        $user = $result->fetch_assoc();

        if(password_verify($password,$user['password'])){

            $_SESSION['user_id']=$user['id'];
            $_SESSION['role']=$user['role'];

            if(isset($_POST['remember'])){
                setcookie("user_email",$email,time()+(86400*30),"/");
            }

            if($user['role']=="admin"){
                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                header("Location: ../patient/dashboard.php");
                exit();
            }

        } else {
            $message = "Wrong password!";
        }
    } else {
        $message = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medical AI Pro | Login</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter', sans-serif;
}

/* Animated Background */
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

/* Glass Card */
.login-container{
    width:420px;
    padding:45px;
    border-radius:20px;
    backdrop-filter:blur(25px);
    background:rgba(255,255,255,0.08);
    border:1px solid rgba(255,255,255,0.2);
    box-shadow:0 10px 40px rgba(0,0,0,0.4);
    color:white;
}

/* Branding */
.brand-title{
    font-family:'Playfair Display', serif;
    font-size:28px;
    text-align:center;
    margin-bottom:8px;
}

.brand-sub{
    text-align:center;
    font-size:14px;
    opacity:0.8;
    margin-bottom:30px;
}

/* Input Fields */
.input-group{
    margin-bottom:20px;
}

.input-group input{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    outline:none;
    font-size:14px;
}

/* Show/Hide */
.toggle-password{
    position:absolute;
    right:18px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    font-size:14px;
}

/* Options */
.options{
    display:flex;
    justify-content:space-between;
    font-size:13px;
    margin-bottom:25px;
}

/* Primary Button */
button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:#00b4d8;
    color:#000;
    font-weight:600;
    font-size:15px;
    cursor:pointer;
    transition:0.3s;
    position:relative;
}

button:hover{
    background:#0096c7;
}

/* Secondary Button (Create Account) */
.create-btn{
    display:block;
    width:100%;
    text-align:center;
    padding:13px;
    margin-top:15px;
    border-radius:10px;
    border:2px solid #00b4d8;
    color:#00b4d8;
    font-weight:600;
    text-decoration:none;
    transition:0.3s;
}

.create-btn:hover{
    background:#00b4d8;
    color:#000;
    box-shadow:0 0 15px #00b4d8;
}

/* Loader */
.loader{
    display:none;
    width:18px;
    height:18px;
    border:3px solid white;
    border-top:3px solid transparent;
    border-radius:50%;
    animation:spin 1s linear infinite;
    margin:auto;
}

@keyframes spin{
    100%{transform:rotate(360deg);}
}

/* Error */
.error{
    color:#ffb3b3;
    text-align:center;
    margin-bottom:15px;
}

/* Dark Mode */
.dark{
    background:#0d1117 !important;
}

a{
    color:#ffffff;
    text-decoration:none;
}

a:hover{
    text-decoration:underline;
}

@media(max-width:768px){
    .login-container{
        width:90%;
    }
}

</style>
</head>
<body>

<div class="login-container">

    <div class="brand-title">Medical AI Pro</div>
    <div class="brand-sub">Intelligent Medical Appointment System</div>

    <?php if($message!=""): ?>
        <div class="error"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="showLoader()">

        <div class="input-group">
            <input type="email" name="email" placeholder="Email Address" required
            value="<?php echo $_COOKIE['user_email'] ?? ''; ?>">
        </div>

        <div class="input-group" style="position:relative;">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span class="toggle-password" onclick="togglePassword(event)">Show</span>
        </div>

        <div class="options">
            <label><input type="checkbox" name="remember"> Remember Me</label>
            <label><input type="checkbox" onclick="toggleDark()"> Dark Mode</label>
        </div>

        <button type="submit">
            <span id="btnText">Sign In</span>
            <div class="loader" id="loader"></div>
        </button>

    </form>

    <!-- Modern Create Account Button -->
    <a href="register.php" class="create-btn">
        Create New Account
    </a>

</div>

<script>

function togglePassword(e){
    let pass=document.getElementById("password");
    if(pass.type==="password"){
        pass.type="text";
        e.target.innerText="Hide";
    } else {
        pass.type="password";
        e.target.innerText="Show";
    }
}

function toggleDark(){
    document.body.classList.toggle("dark");
}

function showLoader(){
    document.getElementById("btnText").style.display="none";
    document.getElementById("loader").style.display="block";
}

</script>

</body>
</html>