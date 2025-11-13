<?php
// eleve/mes_bulletins.php — style élève avec contrôle de paiement
require_once __DIR__ . '/../includes/config.php';
if(session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user']) || ($_SESSION['user']['role_name'] ?? '') !== 'eleve'){
    header("Location: ../index.php");
    exit;
}

$etudiant_id = $_SESSION['user']['id'];
$nom = $_SESSION['user']['nom'] ?? '';
$prenom = $_SESSION['user']['prenom'] ?? '';
$photo = $_SESSION['user']['photo'] ?? 'default.png';

// Montants requis pour chaque versement
$versements_requis = [
    1 => 1000,
    2 => 2000,
    3 => 1500,
];

// Récupérer les paiements
$stmt = $pdo->prepare("SELECT * FROM paiements WHERE etudiant_id=? ORDER BY date_payment ASC");
$stmt->execute([$etudiant_id]);
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_verse = 0;
foreach($paiements as $p){
    $total_verse += $p['montant'];
}

// Fonction pour déterminer si l’accès est autorisé
function acces_note($total_verse, $versement_id, $versements_requis){
    $cumule = 0;
    for($i=1;$i<=$versement_id;$i++){
        $cumule += $versements_requis[$i] ?? 0;
    }
    return $total_verse >= $cumule;
}

// Récupérer les notes avec examen et matière, incluant versement_id
$stmt = $pdo->prepare("
    SELECT n.*, m.nom AS matiere, e.nom AS examen, COALESCE(e.versement_id,1) as versement_id
    FROM notes n
    JOIN matieres m ON n.matiere_id = m.id
    JOIN examens e ON n.exam_id = e.id
    WHERE n.etudiant_id = ?
");
$stmt->execute([$etudiant_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul moyenne
$sum = 0;
$count = 0;
foreach($notes as $n){
    $versement_id = $n['versement_id'] ?? 1;
    if(acces_note($total_verse, $versement_id, $versements_requis)){
        $sum += $n['note'];
        $count++;
    }
}
$moyenne = $count > 0 ? $sum/$count : 0;
?>

<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mes Bulletins — ITAC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Rubik:wght@500;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg-1: #0f1724;
  --accent-1: #7c3aed;
  --accent-2: #06b6d4;
  --card-bg: rgba(255,255,255,0.06);
  --glass: rgba(255,255,255,0.06);
  --muted: #cbd5e1;
  --glass-border: rgba(255,255,255,0.06);
}

/* Reset */
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{
  font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  background: radial-gradient(800px 400px at 10% 10%, rgba(124,58,237,0.12), transparent 8%),
              radial-gradient(700px 350px at 90% 80%, rgba(6,182,212,0.08), transparent 10%),
              linear-gradient(180deg, #071024 0%, #07102a 40%, #081230 100%);
  color:#e6eef8;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
  overflow-y:auto;
}

.floaties{pointer-events:none;position:fixed; inset:0; z-index:0; overflow:hidden;}
.floaties span{position:absolute;display:block;border-radius:50%;opacity:0.12;filter: blur(18px);transform: translate3d(0,0,0);animation: floaty 12s linear infinite;}
.floaties .f1{ width:260px; height:260px; left:-30px; top:10%; background:linear-gradient(135deg,var(--accent-1), rgba(124,58,237,0.4)); animation-duration:18s; }
.floaties .f2{ width:160px; height:160px; right:5%; top:60%; background:linear-gradient(135deg,var(--accent-2), rgba(6,182,212,0.4)); animation-duration:14s; animation-delay:2s;}
.floaties .f3{ width:100px; height:100px; left:30%; top:75%; background:linear-gradient(90deg,#ffffff22,#00000000); animation-duration:20s; animation-delay:3s; opacity:0.06}

@keyframes floaty {0%{ transform: translateY(0) rotate(0deg);} 50%{ transform: translateY(-30px) rotate(45deg);} 100%{ transform: translateY(0) rotate(0deg);}}

/* Layout */
.dashboard-container{
  display:grid; grid-template-columns: 320px 1fr; gap:28px; padding:36px; max-width:1200px; margin:20px auto;
}
.sidebar{
  background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
  border-radius:14px; padding:18px; border:1px solid rgba(255,255,255,0.03);
  display:flex; flex-direction:column; gap:12px;
}
.sidebar .logo{
  font-family:'Rubik',sans-serif; font-weight:700; font-size:22px; background: linear-gradient(135deg,var(--accent-1),var(--accent-2)); padding:12px; border-radius:10px; text-align:center;
}
.sidebar a{
  display:block; padding:10px 12px; margin:4px 0; border-radius:10px; color:#eaf2ff; text-decoration:none; font-weight:600;
  background:linear-gradient(180deg, rgba(124,58,237,0.06), rgba(6,182,212,0.03)); border:1px solid rgba(255,255,255,0.02);
}
.sidebar a:hover, .sidebar a.active{transform:translateX(6px); transition:transform .18s ease; box-shadow:0 10px 30px rgba(6,182,212,0.05);}

/* Right panel */
.panel-right{
  background: linear-gradient(90deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
  padding:18px; border-radius:12px; border:1px solid rgba(255,255,255,0.03);
  min-height:320px;
}
.panel-right h2{margin-bottom:12px;}
.table{width:100%;border-collapse:collapse;margin-top:12px;}
.table th, .table td{padding:12px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.1);}
.table th{background: var(--accent-1); color:#fff;}
.table tbody tr:hover{background: rgba(124,58,237,0.1);}
.cta{
  display:inline-block; margin-top:12px; background:linear-gradient(90deg,var(--accent-2),var(--accent-1));
  padding:10px 14px; border-radius:10px; color:#fff; font-weight:700; text-decoration:none;
  box-shadow: 0 8px 20px rgba(124,58,237,0.12);
}
.success{color:#22c55e;font-weight:700;}
.warning{color:#facc15;font-weight:700;}

@media (max-width:980px){
  .dashboard-container{grid-template-columns:1fr;padding:18px;gap:14px;}
}

.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
</style>
</head>
<body>
<div class="floaties" aria-hidden="true"><span class="f1"></span><span class="f2"></span><span class="f3"></span></div>

<div class="dashboard-container">
  <!-- Sidebar -->
  <aside class="sidebar">
   
    <div class="profile">
      <img src="../uploads/<?=$photo?>" alt="photo">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Étudiant • ITAC</p>
    </div>
    <a href="dashboard.php">🏠 Tableau de bord</a>
    <a href="mes_notes.php">📚 Mes notes</a>
    <a href="mes_bulletins.php" class="active">📄 Mes bulletins</a>
    <a href="paiements.php">💰 Paiements</a>
    <a href="profil.php">⚙️ Profil</a>
    <a href="calendrier.php">📆 Calendrier</a>
    <a href="../logout.php">⤴️ Déconnexion</a>
  </aside>

  <!-- Main panel -->
  <section class="panel-right">
    <h2>Mes bulletins</h2>
    <table class="table">
      <thead>
        <tr><th>Matière</th><th>Examen</th><th>Note</th></tr>
      </thead>
      <tbody>
        <?php foreach($notes as $n): 
            $versement_id = $n['versement_id'] ?? 1;
        ?>
        <tr>
          <td><?=htmlspecialchars($n['matiere'])?></td>
          <td><?=htmlspecialchars($n['examen'])?></td>
          <td>
            <?php
            if(acces_note($total_verse, $versement_id, $versements_requis)){
                echo $n['note'];
            } else {
                echo "<span style='color:#f00;'>Bloqué (Paiement requis)</span>";
            }
            ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php if($count>0): ?><br>
      <p>Moyenne générale: <?=number_format($moyenne,2)?></p>
      <?php if($moyenne>=65): ?>
        <br>
        <p class="success">Félicitations, tu passes en classe supérieure !</p>
      <?php else: ?>
        <p class="warning">Tu es en reprise !</p>
      <?php endif; ?>
    <?php else: ?>
      <p class="warning">Aucune note disponible ou paiement requis.</p>
    <?php endif; ?>
    <br>
<a href="bulletin_pdf.php" class="cta">📥 Télécharger mon bulletin</a>

   
  </section>
</div>
</body>
</html>
