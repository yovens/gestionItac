<?php
session_start();
require_once "../includes/config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2){
    header("Location: ../index.php"); // <-- ici mettre le vrai chemin du login
    exit;
}


$secretaire_id = $_SESSION['user']['id'];
$nom = $_SESSION['user']['nom'];
$prenom = $_SESSION['user']['prenom'];
$photo = $_SESSION['user']['photo'] ?? "default.png";

$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 4")->fetchColumn();
$total_profs = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 3")->fetchColumn();
$total_classes = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Secrétaire — ITAC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
:root{
  --bg-main:#fef6e4;          /* Fond général secrétaire */
  --accent:#ef4444;           /* Accent principal (rouge) */
  --accent-dark:#b91c1c;      /* Accent sombre */
  --card-bg:#fff5f5;          /* Fond des cartes */
  --card-hover:#fee2e2;       /* Fond carte au hover */
  --text-light:#1f2937;       /* Texte principal */
}

*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:'Inter',sans-serif;
    background:var(--bg-main);
    color:var(--text-light);
    overflow-x:hidden;
}

.dashboard-container{
    display:grid;
    grid-template-columns:280px 1fr;
    gap:28px;
    padding:32px;
    max-width:1300px;
    margin:0 auto;
}

/* Sidebar */
.sidebar{
    background:rgba(239,68,68,0.85);
    border-radius:14px;
    padding:18px;
    display:flex;
    flex-direction:column;
    gap:12px;
    border:1px solid rgba(255,255,255,0.05);
}
.sidebar .logo{
    text-align:center;
    font-size:28px;
    font-weight:700;
    color:#fff;
    margin-bottom:14px;
}
.sidebar a{
    display:block;
    padding:12px 14px;
    margin:6px 0;
    border-radius:10px;
    color:#fff;
    text-decoration:none;
    font-weight:600;
    transition:.3s;
    background:rgba(255,255,255,0.05);
}
.sidebar a.active, .sidebar a:hover{
    background:#fff;
    color:var(--accent-dark);
    font-weight:700;
    transform:translateX(4px);
}

/* Main content */
.main-content{
    padding:28px;
    display:flex;
    flex-direction:column;
    gap:20px;
}
.main-content h2{
    text-align:center;
    color:var(--accent);
    margin-bottom:20px;
}
.topbar{
    display:flex;
    align-items:center;
    gap:20px;
}
.avatar{
    width:60px;
    height:60px;
    border-radius:50%;
    border:3px solid var(--accent);
}

/* Cards */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:18px;
    margin-bottom:30px;
}
.cards .card{
    background:var(--card-bg);
    padding:22px;
    border-radius:12px;
    text-align:center;
    font-weight:600;
    font-size:18px;
    box-shadow:0 6px 20px rgba(0,0,0,0.15);
    transition:.3s;
}
.cards .card:hover{
    background:var(--card-hover);
    transform:translateY(-6px);
    box-shadow:0 12px 30px rgba(0,0,0,0.25);
}

/* Dashboard liens rapides */
.secretaire-dashboard{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:20px;
}
.secretaire-dashboard .card{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    padding:24px;
    background:var(--card-bg);
    color:var(--text-light);
    border-radius:12px;
    font-weight:600;
    font-size:16px;
    text-align:center;
    text-decoration:none;
    transition:.3s;
    box-shadow:0 5px 18px rgba(0,0,0,0.15);
}
.secretaire-dashboard .card i{
    font-size:40px;
    margin-bottom:12px;
    color:var(--accent);
}
.secretaire-dashboard .card:hover{
    background:var(--card-hover);
    transform:translateY(-5px);
    box-shadow:0 12px 28px rgba(0,0,0,0.25);
}

/* Responsive */
@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
    .cards{grid-template-columns:1fr;}
    .secretaire-dashboard{grid-template-columns:1fr;}
}

/* Avatar */
.avatar{
    width:60px;
    height:60px;
    border-radius:50%;
    border:3px solid var(--accent);
}
.topbar{
    display:flex;
    align-items:center;
    gap:20px;
}
.cards {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.card {
    flex: 1;
    min-width: 200px;
    background: var(--card-bg);
    padding: 20px;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    transition: 0.3s;
}
.card i {
    font-size: 28px;
    color: var(--accent);
}
.card:hover {
    background: var(--card-hover);
    transform: translateY(-6px);
}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
</style>
</head>
<body>

<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo" style="color: #fff;">ITAC</div>
     <div class="profile">
      <img src="../uploads/<?=$photo?>" alt="photo">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Secretaire</p>
    </div>
    <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="inscriptions.php"><i class="fas fa-book"></i> Inscriptions</a>
    <a href="examens.php"><i class="fas fa-book"></i> Examens</a>
    <a href="notes.php"><i class="fas fa-book"></i> Notes</a>
    <a href="bulletins.php"><i class="fas fa-file-pdf"></i> Bulletins</a>
    <a href="paiements.php"><i class="fas fa-file-pdf"></i> Paiements</a>
    <a href="calendrier.php"><i class="fas fa-calendar-alt"></i> Calendrier</a>
    <a href="releve_notes.php"><i class="fas fa-calendar-alt"></i> Releve_notes</a>
    <a href="profil.php"><i class="fas fa-calendar-alt"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <main class="main-content">
  <header class="topbar">
            <div class="welcome">
            
                <h2>Bienvenue, <?= htmlspecialchars($prenom . ' ' . $nom) ?></h2>
            </div>
        </header>
    <h2>Tableau de bord Secrétaire</h2>
    
   <div class="cards">
    <div class="card">
        <i class="fas fa-user-graduate"></i> Étudiants: <?= $total_students ?>
    </div>
    <div class="card">
        <i class="fas fa-chalkboard-teacher"></i> Professeurs: <?= $total_profs ?>
    </div>
    <div class="card">
        <i class="fas fa-school"></i> Classes: <?= $total_classes ?>
    </div>
</div>


    <div class="secretaire-dashboard">
      <a href="inscriptions.php" class="card"><i class="fas fa-book"></i><br>Gérer Inscriptions</a>
      <a href="examens.php" class="card"><i class="fas fa-book"></i><br>Examens</a>
      <a href="notes.php" class="card"><i class="fas fa-book"></i><br>Notes</a>
      <a href="paiements.php" class="card"><i class="fas fa-file-pdf"></i><br>Paiements</a>
      <a href="bulletins.php" class="card"><i class="fas fa-file-pdf"></i><br>Bulletins</a>
    </div>
  </main>
</div>

</body>
</html>
