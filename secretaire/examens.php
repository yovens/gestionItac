<?php
// secretaire/examens.php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2){
    header("Location: ../index.php");
    exit;
}

$secretaire_id = $_SESSION['user']['id'];
$nom = $_SESSION['user']['nom'];
$prenom = $_SESSION['user']['prenom'];
$photo = $_SESSION['user']['photo'] ?? "default.png";

// Ajouter examen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add_exam') {
    $nom_exam = $_POST['nom']; 
    $type = $_POST['type_exam']; 
    $debut = $_POST['date_debut']; 
    $fin = $_POST['date_fin']; 
    $session_nom = $_POST['session_nom'];

    $pdo->prepare("INSERT INTO examens (nom,type_exam,date_debut,date_fin,session_nom) VALUES (?,?,?,?,?)")
        ->execute([$nom_exam,$type,$debut,$fin,$session_nom]);

    $color = ($type === 'intra' ? 'blue' : ($type === 'final' ? 'green' : 'pink'));
    $pdo->prepare("INSERT INTO calendar_events (titre, description, date_debut, date_fin, type_event, couleur) VALUES (?,?,?,?,?,?)")
        ->execute([$nom_exam, "Examen $type", $debut, $fin, 'examen', $color]);

    flash("Examen ajouté avec succès !");
    header('Location: examens.php');
    exit;
}

// Récupérer examens
$exams = $pdo->query("SELECT * FROM examens ORDER BY date_debut DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Examens — Secrétaire ITAC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
:root{
  --bg-main:#fef6e4;
  --accent:#ef4444;
  --accent-dark:#b91c1c;
  --card-bg:#fff5f5;
  --card-hover:#fee2e2;
  --text-light:#1f2937;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:var(--bg-main);color:var(--text-light);overflow-x:hidden;}
.dashboard-container{display:grid;grid-template-columns:280px 1fr;gap:28px;padding:32px;max-width:1300px;margin:0 auto;}
.sidebar{background:rgba(239,68,68,0.85);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
.sidebar a{display:block;padding:12px 14px;margin:6px 0;border-radius:10px;color:#fff;text-decoration:none;font-weight:600;transition:.3s;background:rgba(255,255,255,0.05);}
.sidebar a.active, .sidebar a:hover{background:#fff;color:var(--accent-dark);font-weight:700;transform:translateX(4px);}
.main-content{padding:28px;display:flex;flex-direction:column;gap:20px;}
.topbar h2{color:var(--accent);margin-bottom:20px;}
.cards-container{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;}
.card{background:var(--card-bg);padding:22px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.15);transition:.3s;}
.card:hover{background:var(--card-hover);transform:translateY(-6px);box-shadow:0 12px 30px rgba(0,0,0,0.25);}
.form-box input,.form-box select{width:100%;padding:12px;margin:10px 0;border:1px solid #ccc;border-radius:8px;outline:none;background:#f9fafb;transition:.3s;}
.form-box input:focus,.form-box select:focus{border-color:#3b82f6;box-shadow:0 0 6px rgba(59,130,246,0.4);}
.btn-submit{width:100%;padding:14px;border:none;border-radius:8px;background:#3b82f6;color:white;font-size:16px;font-weight:bold;cursor:pointer;transition:.3s;}
.btn-submit:hover{background:#2563eb;}
.styled-table{width:100%;border-collapse:collapse;margin-top:15px;}
.styled-table thead tr{background:#3b82f6;color:#fff;}
.styled-table th,.styled-table td{padding:12px 15px;border-bottom:1px solid #ddd;}
.styled-table tbody tr:hover{background:#e0f2fe;}
.animate-card{opacity:0;animation:fadeInUp 1s ease forwards;}
@keyframes fadeInUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
@media(max-width:900px){.dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}.sidebar{flex-direction:row;overflow-x:auto;}.sidebar a{margin:0 8px;}.cards-container{grid-template-columns:1fr;}}
</style>
</head>
<body>

<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo" style="color: #fff;">ITAC</div>
        <div class="profile">
            <img src="../uploads/<?=$photo?>" alt="photo">
            <h3><?=$prenom." ".$nom?></h3>
            <p>Secrétaire</p>
        </div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="inscriptions.php"><i class="fas fa-book"></i> Inscriptions</a>
        <a href="examens.php" class="active"><i class="fas fa-book"></i> Examens</a>
        <a href="notes.php"><i class="fas fa-book"></i> Notes</a>
        <a href="bulletins.php"><i class="fas fa-file-pdf"></i> Bulletins</a>
        <a href="paiements.php"><i class="fas fa-file-pdf"></i> Paiements</a>
        <a href="calendrier.php"><i class="fas fa-calendar-alt"></i> Calendrier</a>
        <a href="releve_notes.php"><i class="fas fa-calendar-alt"></i> Relevé notes</a>
        <a href="profil.php"><i class="fas fa-calendar-alt"></i> Profil</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        <header class="topbar"><h2>Gestion des examens</h2></header>

        <section class="cards-container">
            <!-- Formulaire création examen -->
            <div class="card animate-card">
                <h3>Créer un examen</h3>
                <form method="post" class="form-box">
                    <input name="nom" placeholder="Nom examen" required>
                    <select name="type_exam" required>
                        <option value="intra">Intra</option>
                        <option value="final">Final</option>
                        <option value="reprise">Reprise</option>
                    </select>
                    <input name="date_debut" type="date" required>
                    <input name="date_fin" type="date" required>
                    <input name="session_nom" placeholder="Ex: 1ère session" required>
                    <input type="hidden" name="action" value="add_exam">
                    <button class="btn-submit">Créer</button>
                </form>
            </div>

            <!-- Liste examens -->
            <div class="card animate-card">
                <h3>Examens programmés</h3>
                <?php if(!empty($exams)): ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Session</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($exams as $ex): ?>
                        <tr>
                            <td><?=htmlspecialchars($ex['nom'])?></td>
                            <td><?=htmlspecialchars($ex['type_exam'])?></td>
                            <td><?=$ex['date_debut']?></td>
                            <td><?=$ex['date_fin']?></td>
                            <td><?=htmlspecialchars($ex['session_nom'])?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>Aucun examen programmé.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

</body>
</html>
