<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Medical AI Pro | Enterprise AI Appointment System</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --primary:#0f4c81;
    --secondary:#1abc9c;
    --dark:#0f2027;
    --light:#f4f7fb;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Poppins',sans-serif;
    background:var(--light);
    overflow-x:hidden;
    color:#333;
}

/* ================= NAVBAR ================= */
.navbar{
    position:fixed;
    width:100%;
    top:0;
    left:0;
    padding:18px 8%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:rgba(15,76,129,0.85);
    backdrop-filter:blur(12px);
    z-index:1000;
}

.logo h1{
    font-size:20px;
    font-weight:600;
    color:#fff;
}

.nav-buttons a{
    text-decoration:none;
    padding:10px 24px;
    border-radius:30px;
    font-weight:500;
    margin-left:10px;
    transition:0.3s ease;
}

.btn-primary{
    background:#fff;
    color:var(--primary);
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
}

.btn-primary:hover{
    transform:translateY(-3px);
}

.btn-outline{
    border:2px solid #fff;
    color:#fff;
}

.btn-outline:hover{
    background:#fff;
    color:var(--primary);
}

/* NEW ADMIN BUTTON */
.btn-admin{
    background:var(--secondary);
    color:#fff;
    box-shadow:0 5px 15px rgba(0,0,0,0.25);
}

.btn-admin:hover{
    transform:translateY(-3px);
    background:#16a085;
}

/* ================= HERO SECTION ================= */
.header{
    height:100vh;
    background:linear-gradient(-45deg,var(--primary),var(--secondary),var(--dark),#2c5364);
    background-size:400% 400%;
    animation:gradientBG 12s ease infinite;
    display:flex;
    align-items:center;
    padding:0 8%;
    color:#fff;
}

@keyframes gradientBG{
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

.hero{
    display:flex;
    justify-content:space-between;
    align-items:center;
    width:100%;
    gap:40px;
}

.hero-content{
    max-width:550px;
}

.hero-content h1{
    font-size:44px;
    line-height:1.2;
    margin-bottom:20px;
}

.hero-content p{
    font-size:18px;
    opacity:0.9;
    margin-bottom:30px;
}

/* Illustration */
.hero-illustration{
    animation:float 4s ease-in-out infinite;
}

@keyframes float{
    0%{transform:translateY(0);}
    50%{transform:translateY(-15px);}
    100%{transform:translateY(0);}
}

/* ================= STATS SECTION ================= */
.stats{
    background:#fff;
    padding:100px 8%;
    display:flex;
    justify-content:space-around;
    text-align:center;
}

.stat{
    transition:0.3s;
}

.stat:hover{
    transform:translateY(-8px);
}

.stat h2{
    font-size:42px;
    color:var(--primary);
}

.stat p{
    margin-top:10px;
    color:#555;
}

/* ================= CHAT BUTTON ================= */
.chat-float{
    position:fixed;
    bottom:30px;
    right:30px;
    width:65px;
    height:65px;
    background:var(--secondary);
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:26px;
    color:#fff;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
    transition:0.3s ease;
    text-decoration:none;
}

.chat-float:hover{
    transform:scale(1.1);
}

/* ================= FOOTER ================= */
.footer{
    background:var(--primary);
    color:#fff;
    text-align:center;
    padding:25px;
    font-size:14px;
}

/* ================= RESPONSIVE ================= */
@media(max-width:900px){
    .hero{
        flex-direction:column;
        text-align:center;
    }

    .stats{
        flex-direction:column;
        gap:50px;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="logo">
        <h1>Medical AI Pro</h1>
    </div>
    <div class="nav-buttons">
        <a href="admin/login.php" class="btn-admin">Admin Panel</a>
        <a href="auth/login.php" class="btn-outline">Login</a>
        <a href="auth/register.php" class="btn-primary">Get Started</a>
    </div>
</nav>

<!-- HERO -->
<header class="header">
    <section class="hero">
        <div class="hero-content">
            <h1>Enterprise AI-Powered Medical Appointment System</h1>
            <p>
                Secure, scalable and intelligent chatbot-driven appointment 
                management designed for modern hospitals and healthcare providers.
            </p>
        </div>

        <div class="hero-illustration">
            <svg width="350" height="350" viewBox="0 0 24 24" fill="white">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 
                0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/>
            </svg>
        </div>
    </section>
</header>

<!-- STATS -->
<section class="stats">
    <div class="stat">
        <h2>10K+</h2>
        <p>Appointments Processed</p>
    </div>
    <div class="stat">
        <h2>98%</h2>
        <p>Patient Satisfaction</p>
    </div>
    <div class="stat">
        <h2>24/7</h2>
        <p>AI Availability</p>
    </div>
</section>

<!-- CHAT BUTTON -->
<a href="auth/login.php" class="chat-float">💬</a>

<!-- FOOTER -->
<footer class="footer">
    © 2026 Medical AI Pro | Developed by Logic Layers [780,997]
</footer>

</body>
</html>