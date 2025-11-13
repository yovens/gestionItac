<?php
session_start();
require_once "../includes/config.php";
if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 3){
    header("Location: ../index.php");
    exit;
}

$prof_id = $_SESSION['user']['id'];
$req = $pdo->query("SELECT * FROM calendar_events ORDER BY date_debut ASC");
$events = $req->fetchAll(PDO::FETCH_ASSOC); // <-- CORRECTION ici
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mon planning — ITAC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
  --bg-main:#0b0f2b; --accent:#3b82f6; --accent-dark:#1e40af;
  --card-bg:#1f2937; --card-hover:#374151; --text-light:#f0f0f0;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0b0f2b,#1e1e3f);color:var(--text-light);}
.dashboard-container{display:grid;grid-template-columns:280px 1fr;gap:28px;padding:32px;max-width:1300px;margin:0 auto;}
.sidebar{background:rgba(20,20,40,0.85);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;border:1px solid rgba(255,255,255,0.05);}
.sidebar .logo{text-align:center;font-size:26px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar a{display:block;padding:12px 14px;margin:6px 0;border-radius:10px;color:var(--text-light);text-decoration:none;font-weight:600;transition:.3s;background:rgba(255,255,255,0.03);}
.sidebar a.active, .sidebar a:hover{background:var(--accent);color:#000;font-weight:700;transform:translateX(4px);}
.main-content{padding:28px;display:flex;flex-direction:column;gap:20px;}
.main-content h2{text-align:center;color:var(--accent);margin-bottom:20px;}
.table{width:100%;border-collapse:collapse;background:var(--card-bg);border-radius:12px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,0.25);}
.table th,.table td{padding:12px 16px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);}
.table th{background:var(--accent-dark);color:#fff;}
.table tr:hover{background:var(--card-hover);}
@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
}
</style>
</head>
<body>
<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">Prof</div>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
    <a href="saisie_notes.php"><i class="fas fa-pen"></i> Saisie notes</a>
      <a href="classes.php" ><i class="fas fa-users"></i> Mes classes</a>
    <a href="examens.php" ><i class="fas fa-book"></i> Examens</a>
    <a href="planning.php ><i class="fas fa-calendar"></i> Mon planning</a>
    <a href="profil.php"><i class="fas fa-users-cog"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <main class="main-content">
    <h2>Mon planning</h2>
    <table class="table">
      <thead>
        <tr><th>Date début</th><th>Date fin</th><th>Description</th><th>Type</th></tr>
      </thead>
      <tbody>
        <?php foreach($events as $row): ?>
          <tr>
            <td><?=htmlspecialchars($row['date_debut'])?></td>
            <td><?=htmlspecialchars($row['date_fin'])?></td>
            <td><?=htmlspecialchars($row['titre'].' — '.$row['description'])?></td>
            <td><?=htmlspecialchars($row['type_event'])?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</div>
</body>
</html>






