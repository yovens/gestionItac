<?php
// eleve/mes_notes.php — style alternatif élève
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user']) || ($_SESSION['user']['role_name'] ?? '') !== 'eleve') {
    header('Location: ../index.php');
    exit;
}

$etudiant_id = $_SESSION['user']['id'];
$nom = $_SESSION['user']['nom'] ?? '';
$prenom = $_SESSION['user']['prenom'] ?? '';
$photo = $_SESSION['user']['photo'] ?? 'default.png';

$notes_stmt = $pdo->prepare("
    SELECT n.*, m.nom as matiere, e.nom as examen 
    FROM notes n 
    JOIN matieres m ON n.matiere_id=m.id 
    LEFT JOIN examens e ON n.exam_id=e.id 
    WHERE n.etudiant_id=?
");
$notes_stmt->execute([$etudiant_id]);
$notes = $notes_stmt->fetchAll(PDO::FETCH_ASSOC);

$avg = average_student($pdo, $etudiant_id);
?>

<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mes Notes — ITAC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Rubik:wght@500;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg-1: #0f1724; --accent-1: #7c3aed; --accent-2: #06b6d4;
  --muted: #cbd5e1; --glass: rgba(255,255,255,0.06);
}
*{margin:0;padding:0;box-sizing:border-box;}
body{
  font-family:'Inter',sans-serif;
  background: radial-gradient(800px 400px at 10% 10%, rgba(124,58,237,0.12), transparent 8%),
              radial-gradient(700px 350px at 90% 80%, rgba(6,182,212,0.08), transparent 10%),
              linear-gradient(180deg, #071024 0%, #07102a 40%, #081230 100%);
  color:#e6eef8;
}
.floaties{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none;}
.floaties span{position:absolute;border-radius:50%;opacity:0.12;filter:blur(18px);transform:translate3d(0,0,0);animation: floaty 12s linear infinite;}
.f1{width:260px;height:260px;left:-30px;top:10%;background:linear-gradient(135deg,var(--accent-1), rgba(124,58,237,0.4));animation-duration:18s;}
.f2{width:160px;height:160px;right:5%;top:60%;background:linear-gradient(135deg,var(--accent-2), rgba(6,182,212,0.4));animation-duration:14s;animation-delay:2s;}
.f3{width:100px;height:100px;left:30%;top:75%;background:linear-gradient(90deg,#ffffff22,#00000000);animation-duration:20s;animation-delay:3s;opacity:0.06;}
@keyframes floaty{0%,100%{transform:translateY(0) rotate(0deg);}50%{transform:translateY(-30px) rotate(45deg);}}

.dashboard-container{display:grid;grid-template-columns:320px 1fr;gap:28px;padding:36px;position:relative;z-index:5;max-width:1200px;margin:10px auto;}
.sidebar{background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;border:1px solid rgba(255,255,255,0.03);}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent-1);margin-bottom:12px;}
.sidebar a{display:block;padding:10px 12px;margin:6px 0;border-radius:10px;color:#eaf2ff;text-decoration:none;font-weight:600;background:linear-gradient(180deg, rgba(124,58,237,0.06), rgba(6,182,212,0.03));border:1px solid rgba(255,255,255,0.02);}
.sidebar a.active, .sidebar a:hover{transform:translateX(6px);transition:transform .18s ease;box-shadow:0 10px 30px rgba(6,182,212,0.05);}

.panel-right{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border-radius:12px;padding:18px;border:1px solid rgba(255,255,255,0.03);box-shadow:0 8px 30px rgba(2,6,23,0.45);}
h2{margin-bottom:18px;font-weight:700;color:#fff;}
.table{width:100%;border-collapse:collapse;margin-bottom:18px;}
.table th,.table td{padding:12px 14px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);}
.table th{background:rgba(255,255,255,0.02);}
.success{color:#22c55e;font-weight:600;}
.warning{color:#facc15;font-weight:600;}
.cta{display:inline-block;margin-top:12px;background:linear-gradient(90deg,var(--accent-2),var(--accent-1));padding:10px 14px;border-radius:10px;color:#fff;font-weight:700;text-decoration:none;box-shadow:0 8px 20px rgba(124,58,237,0.12);}
@media(max-width:980px){.dashboard-container{grid-template-columns:1fr;padding:18px;gap:14px;}}
</style>
<body>
<div class="floaties" aria-hidden="true"><span class="f1"></span><span class="f2"></span><span class="f3"></span></div>
<div class="dashboard-container">

  <aside class="sidebar">
    <div class="logo">ITAC</div>
    <a href="dashboard.php">🏠 Tableau de bord</a>
    <a href="mes_notes.php" class="active">📚 Mes notes</a>
    <a href="mes_bulletins.php">📄 Mes bulletins</a>
    <a href="paiements.php"><i class="fas fa-file-pdf"></i> ---> Paiements</a>
        <a href="profil.php"><i class="fas fa-users-cog"></i> --->Profil</a>
    <a href="calendrier.php">📆 Calendrier</a>
    <a href="../logout.php">⤴️ Déconnexion</a>
  </aside>

  <section class="panel-right">
    <h2>Mes notes</h2>
    <table class="table">
      <thead><tr><th>Matière</th><th>Examen</th><th>Note</th></tr></thead>
      <tbody>
        <?php foreach($notes as $n): ?>
        <tr>
          <td><?=htmlspecialchars($n['matiere'])?></td>
          <td><?=htmlspecialchars($n['examen'])?></td>
          <td><?=$n['note']?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p>Moyenne générale: <?=number_format($avg,2)?></p>
    <?php if($avg >= 65): ?>
      <p class="success">Félicitations! Vous avez passé automatiquement à la classe supérieure.</p>
    <?php else: ?>
      <p class="warning">Vous êtes en reprise pour les cours où vous n'avez pas atteint 65.</p>
    <?php endif; ?>
    <a href="dashboard.php" class="cta">Retour au dashboard</a>
  </section>

</div>
</body>
</html>
