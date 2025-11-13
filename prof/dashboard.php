<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 3) {
    header("Location: ../index.php");
    exit;
}

$nom = $_SESSION['user']['nom'];
$prenom = $_SESSION['user']['prenom'];
$photo = $_SESSION['user']['photo'] ?? "default.png";
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Professeur — ITAC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
  --bg-main:#0b0f2b; 
  --accent:#3b82f6;         /* Bleu pour Professeurs */
  --accent-dark:#1e40af;
  --card-bg:#1f2937; 
  --card-hover:#374151; 
  --text-light:#f0f0f0;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0b0f2b,#1e1e3f);color:var(--text-light);overflow-x:hidden;}
.floaties{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden;}
.floaties span{position:absolute;border-radius:50%;opacity:0.08;filter:blur(22px);animation: floaty 16s linear infinite;}
.f1{width:280px;height:280px;top:10%;left:-20px;background:linear-gradient(45deg,var(--accent),rgba(59,130,246,0.3));animation-duration:20s;}
.f2{width:160px;height:160px;bottom:15%;right:5%;background:linear-gradient(45deg,var(--accent-dark),rgba(30,64,175,0.3));animation-duration:14s;animation-delay:3s;}
.f3{width:100px;height:100px;top:70%;left:40%;background:linear-gradient(90deg,#ffffff22,#00000000);animation-duration:18s;animation-delay:1s;}
@keyframes floaty{0%,100%{transform:translateY(0) rotate(0deg);}50%{transform:translateY(-25px) rotate(45deg);}}

/* Layout */
.dashboard-container{display:grid;grid-template-columns:280px 1fr;gap:28px;padding:32px;position:relative;z-index:5;max-width:1300px;margin:0 auto;}
.sidebar{background:rgba(20,20,40,0.85);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;border:1px solid rgba(255,255,255,0.05);}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
.sidebar a{display:block;padding:12px 14px;margin:6px 0;border-radius:10px;color:var(--text-light);text-decoration:none;font-weight:600;transition:.3s;background:rgba(255,255,255,0.03);}
.sidebar a.active, .sidebar a:hover{background:var(--accent);color:#000;font-weight:700;transform:translateX(4px);}

.main-content{padding:28px;display:flex;flex-direction:column;gap:20px;}
.main-content h2{text-align:center;color:var(--accent);margin-bottom:20px;}

/* Cards */
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;margin-bottom:30px;}
.cards .card{background:var(--card-bg);padding:22px;border-radius:12px;text-align:center;font-weight:600;font-size:18px;box-shadow:0 6px 20px rgba(0,0,0,0.25);transition:.3s;position:relative;overflow:hidden;color:var(--text-light);text-decoration:none;}
.cards .card:hover{transform:translateY(-6px);box-shadow:0 12px 30px rgba(0,0,0,0.35);}
.cards .card i{font-size:34px;margin-bottom:12px;color:var(--accent);}

@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
    .cards{grid-template-columns:1fr;}
}
</style>
</head>
<body>
<div class="floaties" aria-hidden="true">
  <span class="f1"></span>
  <span class="f2"></span>
  <span class="f3"></span>
</div>

<div class="dashboard-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="logo">ITAC</div>
    <div class="profile">
      <img src="../uploads/<?=$photo?>" alt="photo">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Professeur</p>
    </div>
    <a href="dashboard.php" ><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="saisie_notes.php"><i class="fas fa-pen"></i> Saisie des notes</a>
      <a href="classes.php" ><i class="fas fa-users"></i> Mes classes</a>
    <a href="examens.php" ><i class="fas fa-book"></i> Examens</a>
    <a href="planning.php"><i class="fas fa-calendar"></i> Planning</a>
    <a href="profil.php"><i class="fas fa-users-cog"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <!-- Main content -->
  <main class="main-content">
    <h2>Tableau de bord Professeur</h2>

    <div class="cards">
      <a href="saisie_notes.php" class="card"><i class="fas fa-pen"></i><br>Saisie des notes</a>
      <a href="planning.php" class="card"><i class="fas fa-calendar"></i><br>Planning</a>
      <a href="../logout.php" class="card"><i class="fas fa-sign-out-alt"></i><br>Déconnexion</a>
    </div>
  </main>
</div>
</body>
</html>
