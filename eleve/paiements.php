<?php 
// eleve/paiements.php
session_start();
require_once "../includes/config.php";
require_once "../includes/helpers.php";

// Vérification si c'est bien un élève
if(!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'eleve'){
    header("Location: ../index.php");
    exit;
}

$eleve_id = $_SESSION['user']['id'];

// Définir le calendrier scolaire des versements
$calendar_versements = [
    1 => '2025-12-10', // avant examen intra 1ère session
    2 => '2026-02-10', // avant examen final 1ère session
    3 => '2026-05-05', // avant examen intra 2ème session
];

// Montants requis par versement
$versements_requis = [1 => 1000, 2 => 2000, 3 => 1500];

// Récupérer les paiements de l'élève
$stmt = $pdo->prepare("SELECT * FROM paiements WHERE etudiant_id = ? ORDER BY date_payment ASC");
$stmt->execute([$eleve_id]);
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer la somme totale versée
$total_verse = 0;
foreach($paiements as $p){
    $total_verse += $p['montant'];
}

// Fonction pour vérifier l’accès à chaque versement
function acces_versement($versement_id, $total_verse, $versements_requis){
    $cumule = 0;
    for($i=1;$i<=$versement_id;$i++){
        $cumule += $versements_requis[$i];
    }
    return $total_verse >= $cumule;
}

// Calculer le solde manquant
$solde_manquant = 0;
foreach($versements_requis as $id => $montant){
    if($total_verse < $montant) {
        $solde_manquant = $montant - $total_verse;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paiements — Dashboard Élève</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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

/* DASHBOARD GRID */
.dashboard-container{display:grid;grid-template-columns:320px 1fr;gap:28px;padding:36px;position:relative;z-index:5;max-width:1200px;margin:10px auto;}
.sidebar{background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;border:1px solid rgba(255,255,255,0.03);}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent-1);margin-bottom:12px;}
.sidebar a{display:block;padding:10px 12px;margin:6px 0;border-radius:10px;color:#eaf2ff;text-decoration:none;font-weight:600;background:linear-gradient(180deg, rgba(124,58,237,0.06), rgba(6,182,212,0.03));border:1px solid rgba(255,255,255,0.02);}
.sidebar a.active, .sidebar a:hover{transform:translateX(6px);transition:transform .18s ease;box-shadow:0 10px 30px rgba(6,182,212,0.05);}

.panel-right{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border-radius:12px;padding:18px;border:1px solid rgba(255,255,255,0.03);box-shadow:0 8px 30px rgba(2,6,23,0.45);}
h2{margin-bottom:18px;font-weight:700;color:#fff;}
.table-paiements{width:100%;border-collapse:collapse;margin-bottom:18px;}
.table-paiements th,.table-paiements td{padding:12px 14px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);}
.table-paiements th{background:rgba(255,255,255,0.02);}
.success{color:#22c55e;font-weight:600;}
.warning{color:#facc15;font-weight:600;}
.cta{display:inline-block;margin-top:12px;background:linear-gradient(90deg,var(--accent-2),var(--accent-1));padding:10px 14px;border-radius:10px;color:#fff;font-weight:700;text-decoration:none;box-shadow:0 8px 20px rgba(124,58,237,0.12);}

.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
</style>
</head>
<body>
<div class="floaties" aria-hidden="true"><span class="f1"></span><span class="f2"></span><span class="f3"></span></div>

<div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
       <div class="logo">ITAC</div>
    <div class="profile">
      
      <p>Étudiant • ITAC</p>
    </div>
        <a href="dashboard.php">🏠 Tableau de bord</a>
        <a href="mes_notes.php">📚 Mes notes</a>
        <a href="mes_bulletins.php">📄 Mes bulletins</a>
        <a href="paiements.php">💰 Paiements</a>
    <a href="profil.php">⚙️ Profil</a>
        <a href="calendrier.php">📆 Calendrier</a>
        <a href="../logout.php">⤴️ Déconnexion</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="panel-right">
        <h2>Mes Paiements</h2>

        <?php if(empty($paiements)): ?>
            <p style="text-align:center;">Aucun paiement enregistré pour le moment.</p>
        <?php else: ?>
            <table class="table-paiements">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Versement #</th>
                        <th>Méthode</th>
                        <th>Note</th>
                        <th>Reçu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($paiements as $p): ?>
                        <tr>
                            <td style="text-align:center;"><?= htmlspecialchars($p['date_payment']) ?></td>
                            <td style="text-align:center;"><?= htmlspecialchars(number_format($p['montant'], 2)) ?> HTG</td>
                            <td style="text-align:center;"><?= htmlspecialchars($p['versement_num']) ?></td>
                            <td style="text-align:center;"><?= htmlspecialchars($p['method']) ?></td>
                            <td style="text-align:center;">
                                <?php
                                    if(acces_versement($p['versement_num'],$total_verse,$versements_requis)){
                                        echo "Accessible";
                                    } else {
                                        echo "<span class='warning'>Bloqué (Solde manquant: ".number_format($solde_manquant,2)." HTG)</span>";
                                    }
                                ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if(!empty($p['receipt'])): ?>
                                    <a href="../uploads/<?= htmlspecialchars($p['receipt']) ?>" target="_blank">📄 Voir reçu</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if($solde_manquant > 0): ?>
            <p class="warning">⚠️ Vous devez encore payer <?= number_format($solde_manquant,2) ?> HTG pour débloquer vos notes.</p>
        <?php else: ?>
            <p class="success">✅ Tous vos versements sont à jour. Vos notes sont accessibles.</p>
        <?php endif; ?>

    </main>
</div>
</body>
</html>
