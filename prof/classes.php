<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 3) {
    header("Location: ../index.php");
    exit;
}

$prof_id = $_SESSION['user']['id'];
$nom     = $_SESSION['user']['nom'];
$prenom  = $_SESSION['user']['prenom'];
$photo   = $_SESSION['user']['photo'] ?? "default.png";

/* === Récupération des classes du prof via affectations === */
$stmt = $pdo->prepare("
    SELECT DISTINCT c.id AS classe_id, c.nom AS classe_nom, c.annee,
                    m.nom AS matiere_nom
    FROM affectations a
    JOIN classes c ON a.classe_id = c.id
    JOIN matieres m ON a.matiere_id = m.id
    WHERE a.prof_id = ?
");
$stmt->execute([$prof_id]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mes Classes — Professeur</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
  --bg-main:#0b0f2b; --accent:#3b82f6; --accent-dark:#1e40af;
  --card-bg:#1f2937; --card-hover:#374151; --text-light:#f0f0f0;
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
.dashboard-container{display:grid;grid-template-columns:280px 1fr;gap:28px;padding:32px;position:relative;z-index:5;max-width:1400px;margin:0 auto;}
.sidebar{background:rgba(20,20,40,0.85);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;border:1px solid rgba(255,255,255,0.05);}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
.sidebar .profile p{font-size:14px;opacity:0.8;}
.sidebar a{display:block;padding:12px 14px;margin:6px 0;border-radius:10px;color:var(--text-light);text-decoration:none;font-weight:600;transition:.3s;background:rgba(255,255,255,0.03);}
.sidebar a.active, .sidebar a:hover{background:var(--accent);color:#000;font-weight:700;transform:translateX(4px);}

.main-content{padding:28px;display:flex;flex-direction:column;gap:20px;}
.main-content h2{text-align:center;color:var(--accent);margin-bottom:20px;}

.card{background:var(--card-bg);padding:20px;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,0.3);transition:.3s;overflow-x:auto;}
.card:hover{background:var(--card-hover);}
.card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:10px;}
.card-header h3{color:var(--accent);font-size:18px;}
button, .btn{padding:6px 12px;border:none;border-radius:6px;background:var(--accent);color:#fff;font-weight:600;cursor:pointer;transition:.3s;text-decoration:none;}
button:hover, .btn:hover{background:var(--accent-dark);}
table{width:100%;border-collapse:collapse;margin-top:10px;min-width:300px;}
th,td{padding:10px;border-bottom:1px solid #444;text-align:left;}
th{color:var(--accent);}
tr:hover{background:rgba(255,255,255,0.05);}

/* Responsive */
@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;gap:8px;}
    .sidebar a{margin:0 6px;white-space:nowrap;}
}
@media(max-width:600px){
    .card-header{flex-direction:column;align-items:flex-start;}
    table{font-size:14px;}
}
</style>
</head>
<body>
<div class="floaties"><span class="f1"></span><span class="f2"></span><span class="f3"></span></div>

<div class="dashboard-container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="logo">ITAC</div>
    <div class="profile">
      <img src="../uploads/<?=$photo?>" alt="photo">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Professeur</p>
    </div>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="saisie_notes.php"><i class="fas fa-pen"></i> Saisie notes</a>
    <a href="classes.php" class="active"><i class="fas fa-users"></i> Mes classes</a>
    <a href="examens.php"><i class="fas fa-book"></i> Examens</a>
    <a href="planning.php"><i class="fas fa-calendar"></i> Planning</a>
    <a href="profil.php"><i class="fas fa-user-cog"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <!-- Main -->
  <main class="main-content">
    <h2>🎓 Mes Classes & Élèves</h2>

    <?php if($classes): ?>
      <?php foreach($classes as $c): ?>
        <div class="card">
          <div class="card-header">
            <h3><?= htmlspecialchars($c['classe_nom']) ?> — <?= htmlspecialchars($c['annee']) ?></h3>
            <a class="btn" href="?classe_id=<?=$c['classe_id']?>">Voir les élèves</a>
          </div>
          <p>Matière : <strong><?= htmlspecialchars($c['matiere_nom']) ?></strong></p>

          <?php if(isset($_GET['classe_id']) && $_GET['classe_id']==$c['classe_id']): 
            $stmt2 = $pdo->prepare("
              SELECT u.id, u.nom, u.prenom, u.email 
              FROM users u
              JOIN etudiants_classes ec ON ec.etudiant_id=u.id
              WHERE ec.classe_id=?
            ");
            $stmt2->execute([$c['classe_id']]);
            $etudiants = $stmt2->fetchAll(PDO::FETCH_ASSOC);
          ?>
          <table>
            <thead><tr><th>Nom</th><th>Prénom</th><th>Email</th></tr></thead>
            <tbody>
              <?php if($etudiants): foreach($etudiants as $e): ?>
                <tr>
                  <td><?= htmlspecialchars($e['nom']) ?></td>
                  <td><?= htmlspecialchars($e['prenom']) ?></td>
                  <td><?= htmlspecialchars($e['email']) ?></td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="3">Aucun élève inscrit.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Aucune classe assignée.</p>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
