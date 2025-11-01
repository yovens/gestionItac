<?php
require_once 'includes/config.php';
require_once 'includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && $user['password'] === $password) {
        $_SESSION['user'] = $user;
        if ($user['role_name'] === 'admin') header('Location: admin/dashboard.php');
        elseif ($user['role_name'] === 'secretaire') header('Location: secretaire/dashboard.php');
        elseif ($user['role_name'] === 'professeur') header('Location: prof/dashboard.php');
        else header('Location: eleve/dashboard.php');
        exit;
    } else {
        flash('Identifiants invalides');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ITAC — Gestion Universitaire</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
  --neon-green:#00ff88;
  --neon-blue:#00c8ff;
  --neon-pink:#ff00e0;
  --bg-dark:#0d1117;
  --text-light:#fff;
}

/* BODY */
body{
  margin:0;
  padding:0;
  font-family:'Orbitron', monospace;
  background:#0d1117;
  color:var(--text-light);
  overflow:hidden;
}

/* CANVAS */
#bg-canvas{
  position:fixed;
  top:0; left:0;
  width:100%;
  height:100%;
  z-index:0;
}

/* HERO GRID */
.home-grid{
  position:relative;
  z-index:5;
  display:flex;
  justify-content:center;
  align-items:center;
  width:100%;
  height:100vh;
}

/* HERO CONTENT */
.hero-content{
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:30px;
  background: rgba(15,15,30,0.85);
  padding:50px 70px;
  border-radius:25px;
  box-shadow:0 0 30px rgba(0,255,255,0.4),0 0 60px rgba(255,0,200,0.2) inset;
  backdrop-filter:blur(8px);
  animation:fadeIn 1.2s ease forwards;
}

/* LOGO */
.logo{
  width:180px;
  height:180px;
  object-fit:contain;
  filter:drop-shadow(0 0 10px var(--neon-green)) drop-shadow(0 0 20px var(--neon-pink));
  animation:logoFloat 3s ease-in-out infinite alternate;
}

/* TEXT */
.text-content h1{
  font-size:48px;
  color:var(--neon-green);
  text-shadow:0 0 10px var(--neon-green),0 0 20px var(--neon-pink),0 0 30px var(--neon-blue);
  animation:textGlow 2s infinite alternate;
}
.text-content p{
  font-size:18px;
  color:#aaa;
}

/* FORM */
.login-form{
  display:flex;
  flex-direction:column;
  width:320px;
  gap:12px;
}
.login-form input{
  padding:14px;
  border-radius:12px;
  border:2px solid var(--neon-blue);
  background: rgba(0,0,0,0.5);
  color:#fff;
  font-weight:bold;
  transition:0.3s;
}
.login-form input:focus{
  border-color:var(--neon-pink);
  box-shadow:0 0 10px var(--neon-pink),0 0 20px var(--neon-blue);
}
.login-form input::placeholder{
  color:#0ff;
}
.login-form button{
  padding:14px;
  border:none;
  border-radius:12px;
  font-weight:bold;
  background:linear-gradient(90deg,var(--neon-green),var(--neon-pink),var(--neon-blue));
  color:#000;
  cursor:pointer;
  box-shadow:0 0 10px var(--neon-green),0 0 20px var(--neon-pink);
  transition:0.3s;
}
.login-form button:hover{
  filter:brightness(1.2) drop-shadow(0 0 15px var(--neon-green));
}

/* FLASH */
.flash{
  color:#ff4c4c;
  font-weight:700;
  margin-top:8px;
  text-align:center;
}

/* ANIMATIONS */
@keyframes fadeIn{0%{opacity:0;transform:translateY(20px);}100%{opacity:1;transform:translateY(0);}}
@keyframes logoFloat{0%{transform:translateY(0);}100%{transform:translateY(-15px);}}
@keyframes textGlow{
  0%{text-shadow:0 0 5px var(--neon-green);}
  50%{text-shadow:0 0 20px var(--neon-pink);}
  100%{text-shadow:0 0 35px var(--neon-blue);}
}

@media(max-width:900px){
  .hero-content{padding:30px 20px;}
}



.hero-content{
    display:flex;
    flex-direction: column; /* reste en colonne */
    align-items: center;    /* centre horizontalement */
    justify-content: center; /* centre verticalement */
    gap: 30px;
    background: rgba(15,15,30,0.85);
    padding: 50px 70px;
    border-radius: 25px;
    box-shadow: 0 0 30px rgba(0,255,255,0.4), 0 0 60px rgba(255,0,200,0.2) inset;
    backdrop-filter: blur(8px);
    text-align: center; /* centre le texte */
}

</style>
</head>
<body>
<canvas id="bg-canvas"></canvas>
<div class="home-grid">
  <section class="hero">
    <div class="hero-content">
      <img src="assets/uploads/logo_itac.png" alt="Logo ITAC" class="logo">
      <div class="text-content">
        <h1>Bienvenue à ITAC</h1>
        <p>Gestion universitaire futuriste — inscriptions, notes, paiements, calendrier et plus.</p>
        <form method="post" class="login-form">
          <input name="username" placeholder="Nom d'utilisateur" required>
          <input name="password" type="password" placeholder="Mot de passe" required>
          <button type="submit">Se connecter</button>
        </form>
        <?php if ($m = flash()): ?><div class="flash"><?=htmlspecialchars($m)?></div><?php endif; ?>
      </div>
    </div>
  </section>
</div>

<script>
// MATRIX + PARTICULES INTERACTIVES
const canvas = document.getElementById('bg-canvas');
const ctx = canvas.getContext('2d');
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

let letters = "01ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
const fontSize = 16;
const columns = Math.floor(canvas.width / fontSize);
const drops = Array(columns).fill(1);

function drawMatrix() {
    ctx.fillStyle = "rgba(13,17,23,0.15)";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.font = fontSize + "px monospace";
    for(let i=0;i<drops.length;i++){
        const text = letters[Math.floor(Math.random()*letters.length)];
        ctx.fillStyle = `rgba(0,255,136,${Math.random()})`;
        ctx.fillText(text,i*fontSize,drops[i]*fontSize);
        if(drops[i]*fontSize>canvas.height && Math.random()>0.975) drops[i]=0;
        drops[i]++;
    }
    requestAnimationFrame(drawMatrix);
}
drawMatrix();

window.addEventListener('resize', ()=>{
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
});
</script>
</body>
</html>
