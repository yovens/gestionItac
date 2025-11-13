


<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}




$nom = $_SESSION['user']['nom'] ?? '';
$prenom = $_SESSION['user']['prenom'] ?? '';
$photo = $_SESSION['user']['photo'] ?? 'default.png'; 
// stats
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 4")->fetchColumn();
$total_profs = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 3")->fetchColumn();
$total_classes = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Admin — ITAC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
  --bg-main:#0b0f2b; --accent:#f59e0b; --accent-dark:#b45309;
  --card-bg:#1f2937; --card-hover:#3b4252; --text-light:#f0f0f0;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0b0f2b,#1e1e3f);color:var(--text-light);overflow-x:hidden;}
.floaties{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden;}
.floaties span{position:absolute;border-radius:50%;opacity:0.08;filter:blur(22px);animation: floaty 16s linear infinite;}
.f1{width:280px;height:280px;top:10%;left:-20px;background:linear-gradient(45deg,var(--accent),rgba(245,158,11,0.3));animation-duration:20s;}
.f2{width:160px;height:160px;bottom:15%;right:5%;background:linear-gradient(45deg,var(--accent-dark),rgba(180,83,9,0.3));animation-duration:14s;animation-delay:3s;}
.f3{width:100px;height:100px;top:70%;left:40%;background:linear-gradient(90deg,#ffffff22,#00000000);animation-duration:18s;animation-delay:1s;}
@keyframes floaty{0%,100%{transform:translateY(0) rotate(0deg);}50%{transform:translateY(-25px) rotate(45deg);}}

.dashboard-container{display:grid;grid-template-columns:280px 1fr;gap:28px;padding:32px;position:relative;z-index:5;max-width:1300px;margin:0 auto;}
.sidebar{background:rgba(20,20,40,0.85);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;border:1px solid rgba(255,255,255,0.05);}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar a{display:block;padding:12px 14px;margin:6px 0;border-radius:10px;color:var(--text-light);text-decoration:none;font-weight:600;transition:.3s;background:rgba(255,255,255,0.03);}
.sidebar a.active, .sidebar a:hover{background:var(--accent);color:#000;font-weight:700;transform:translateX(4px);}

.main-content{padding:28px;display:flex;flex-direction:column;gap:20px;}
.main-content h2{text-align:center;color:var(--accent);margin-bottom:20px;}

.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px;margin-bottom:30px;}
.cards .card{background:var(--card-bg);padding:22px;border-radius:12px;text-align:center;font-weight:600;font-size:18px;box-shadow:0 6px 20px rgba(0,0,0,0.25);transition:.3s;position:relative;overflow:hidden;}
.cards .card:hover{transform:translateY(-6px);box-shadow:0 12px 30px rgba(0,0,0,0.35);}

.admin-dashboard{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;}
.admin-dashboard .card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;background:var(--card-bg);color:var(--text-light);border-radius:12px;font-weight:600;font-size:16px;text-align:center;text-decoration:none;transition:.3s;box-shadow:0 5px 18px rgba(0,0,0,0.25);}
.admin-dashboard .card i{font-size:40px;margin-bottom:12px;color:var(--accent);}
.admin-dashboard .card:hover{background:var(--card-hover);transform:translateY(-5px);box-shadow:0 12px 28px rgba(0,0,0,0.35);}

@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
    .cards{grid-template-columns:1fr;}
    .admin-dashboard{grid-template-columns:1fr;}
}

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
<div class="floaties" aria-hidden="true">
  <span class="f1"></span>
  <span class="f2"></span>
  <span class="f3"></span>
</div>

<div class="dashboard-container">
  <aside class="sidebar">
        <div class="logo" style="color: #fff;">ITAC</div>
     <div class="profile">
      <img src="../uploads/<?=$photo?>" alt="photo">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Admin</p>
    </div>
    <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="classes.php"><i class="fas fa-building"></i> Classes</a>
    <a href="matieres.php"><i class="fas fa-book"></i> Matières</a>
    <a href="affectations.php"><i class="fas fa-tasks"></i> Affectations</a>
    <a href="utilisateurs.php"><i class="fas fa-users-cog"></i> Utilisateurs</a>
    <a href="paiements.php"><i class="fas fa-credit-card"></i> Paiements</a>
    <a href="profil.php"><i class="fas fa-users-cog"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <main class="main-content">
  <header class="topbar">
            <div class="welcome">
                <h2>Bienvenue, <?= htmlspecialchars($prenom . ' ' . $nom) ?></h2>
            </div>
        </header>
    <h2>Tableau de bord Admin</h2>
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

    <div class="admin-dashboard">
      <a href="classes.php" class="card"><i class="fas fa-building"></i><br>Gérer Classes</a>
      <a href="matieres.php" class="card"><i class="fas fa-book"></i><br>Gérer Matières</a>
      <a href="affectations.php" class="card"><i class="fas fa-tasks"></i><br>Affectations</a>
      <a href="utilisateurs.php" class="card"><i class="fas fa-users-cog"></i><br>Utilisateurs</a>
      <a href="paiements.php" class="card"><i class="fas fa-credit-card"></i><br>Paiements</a>
    </div>
  </main>
</div>
</body>
</html>
