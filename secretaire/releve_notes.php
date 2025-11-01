<?php
// secretaire/releve_notes.php
session_start();
require_once "../includes/config.php";
require_once "../includes/helpers.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'secretaire'){
    header("Location: ../index.php");
    exit;
}

// Récupérer tous les élèves
$eleves = $pdo->query("SELECT u.id,u.nom,u.prenom,c.nom as classe
                       FROM users u
                       JOIN etudiants_classes ec ON u.id=ec.etudiant_id
                       JOIN classes c ON ec.classe_id=c.id
                       WHERE u.role_id=4
                       ORDER BY ec.annee_academique ASC, u.nom ASC")->fetchAll(PDO::FETCH_ASSOC);

// Préparer les relevés
$releves = [];
foreach($eleves as $e){
    // Récupérer toutes les notes de cet élève
    $stmt = $pdo->prepare("SELECT m.nom as matiere, n.note 
                           FROM notes n
                           JOIN matieres m ON n.matiere_id = m.id
                           WHERE n.etudiant_id = ?");
    $stmt->execute([$e['id']]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculer moyenne générale
    $total = 0;
    $count = 0;
    foreach($notes as $n){
        $total += $n['note'];
        $count++;
    }
    $moyenne = $count > 0 ? round($total/$count,2) : 0;

    // Eligibilité
    $eligible = $moyenne >= 60 ? 'Oui' : 'Non';

    $releves[] = [
        'eleve'=>$e,
        'notes'=>$notes,
        'moyenne'=>$moyenne,
        'eligible'=>$eligible
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Relevé de notes — Dashboard Secrétaire</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
    --bg-main:#f3f4f6;
    --accent:#ef4444;
    --accent-dark:#b91c1c;
    --card-bg:#ffffff;
    --card-hover:#fee2e2;
    --text-dark:#1f2937;
}

/* BODY */
body{
    font-family:'Inter',sans-serif;
    margin:0;
    background: var(--bg-main);
    color: var(--text-dark);
}

/* DASHBOARD GRID */
.dashboard-container{
    display:grid;
    grid-template-columns:280px 1fr;
    gap:28px;
    padding:32px;
    max-width:1400px;
    margin:0 auto;
}

/* SIDEBAR */
.sidebar{
    background:rgba(239,68,68,0.85);
    border-radius:14px;
    padding:18px;
    display:flex;
    flex-direction:column;
    gap:12px;
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

/* MAIN CONTENT */
.main-content{
    padding:28px;
    display:flex;
    flex-direction:column;
    gap:20px;
}
.main-content h2{
    color:var(--accent);
    text-align:center;
    margin-bottom:20px;
}

/* TABLE RELEVES */
.table-releves{
    width:100%;
    border-collapse:collapse;
}
.table-releves th, .table-releves td{
    padding:12px;
    border-bottom:1px solid rgba(0,0,0,0.1);
    text-align:left;
}
.table-releves thead th{
    background:var(--accent);
    color:#fff;
}
.table-releves tbody tr:hover{
    background:var(--card-hover);
}

/* Responsive */
@media(max-width:900px){
    .dashboard-container{
        grid-template-columns:1fr;
        padding:18px;
        gap:18px;
    }
    .sidebar{
        flex-direction:row;
        overflow-x:auto;
    }
    .sidebar a{
        margin:0 8px;
    }
}
</style>
</head>
<body>
<div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        
    <div class="logo">ITAC</div>
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

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <h2>Relevé de notes des élèves</h2>

        <?php if(empty($releves)): ?>
            <p style="text-align:center;">Aucun élève enregistré pour le moment.</p>
        <?php else: ?>
            <?php foreach($releves as $r): ?>
                <div class="card" style="margin-bottom:20px;">
                    <h3><?=htmlspecialchars($r['eleve']['prenom'].' '.$r['eleve']['nom'].' — '.$r['eleve']['classe'])?></h3>

                       
        <!-- Bouton Export PDF -->
        <a href="export_pdf.php?id=<?= $r['eleve']['id'] ?>" 
           class="btn-pdf" target="_blank">
           <i class="fas fa-file-pdf"></i> Exporter en PDF
        </a>
                    <?php if(empty($r['notes'])): ?>
                        <p>Aucune note disponible.</p>
                    <?php else: ?>
                        <table class="table-releves">
                            <thead>
                                <tr>
                                    <th>Matière</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($r['notes'] as $n): ?>
                                    <tr>
                                        <td><?=htmlspecialchars($n['matiere'])?></td>
                                        <td><?=htmlspecialchars($n['note'])?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr style="font-weight:bold;">
                                    <td>Moyenne générale</td>
                                    <td><?= $r['moyenne'] ?> / 100</td>
                                </tr>
                                <tr style="font-weight:bold;">
                                    <td>Éligible fin d'études</td>
                                    <td><?= $r['eligible'] ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
<style>
.btn-pdf {
    display:inline-block;
    padding:8px 14px;
    margin:10px 0;
    background:#ef4444;
    color:#fff;
    font-weight:600;
    border-radius:6px;
    text-decoration:none;
    transition:0.3s;
}
.btn-pdf:hover {
    background:#b91c1c;
}
</style>
