<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'professeur') {
    header('Location: ../index.php');
    exit;
}

$prof_id = $_SESSION['user']['id'];
$nom     = $_SESSION['user']['nom'];
$prenom  = $_SESSION['user']['prenom'];

// Vérifier qu'on a un ID de note
if (!isset($_GET['id'])) {
    flash("Note introuvable !");
    header('Location: saisie_notes.php');
    exit;
}

$note_id = intval($_GET['id']);

// Récupérer la note
$stmt = $pdo->prepare("
    SELECT n.*, u.nom as nom_e, u.prenom as prenom_e, m.nom as matiere_nom, e.nom as exam_nom, e.type_exam
    FROM notes n
    JOIN users u ON n.etudiant_id=u.id
    JOIN matieres m ON n.matiere_id=m.id
    JOIN examens e ON n.exam_id=e.id
    JOIN affectations a ON a.matiere_id=m.id AND a.classe_id=(SELECT ec.classe_id FROM etudiants_classes ec WHERE ec.etudiant_id=u.id LIMIT 1)
    WHERE n.id=? AND a.prof_id=?
");
$stmt->execute([$note_id, $prof_id]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    flash("Note introuvable ou accès refusé !");
    header('Location: saisie_notes.php');
    exit;
}

// Enregistrer modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_note = floatval($_POST['note']);
    $pdo->prepare("UPDATE notes SET note=? WHERE id=?")->execute([$new_note, $note_id]);
    flash("Note modifiée avec succès !");
    header('Location: saisie_notes.php');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Modifier Note — Professeur</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
  --bg-main:#0b0f2b; --accent:#3b82f6; --accent-dark:#1e40af;
  --card-bg:#1f2937; --card-hover:#374151; --text-light:#f0f0f0;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0b0f2b,#1e1e3f);color:var(--text-light);overflow-x:hidden;}
.dashboard-container{display:grid;grid-template-columns:280px 1fr;gap:28px;padding:32px;position:relative;z-index:5;max-width:1400px;margin:0 auto;}
.sidebar{background:rgba(20,20,40,0.85);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;border:1px solid rgba(255,255,255,0.05);}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile h3{font-size:18px;font-weight:600;}
.sidebar .profile p{font-size:14px;opacity:0.8;}
.sidebar a{display:block;padding:12px 14px;margin:6px 0;border-radius:10px;color:var(--text-light);text-decoration:none;font-weight:600;transition:.3s;background:rgba(255,255,255,0.03);}
.sidebar a.active, .sidebar a:hover{background:var(--accent);color:#000;font-weight:700;transform:translateX(4px);}

.main-content{padding:28px;display:flex;flex-direction:column;gap:20px;}
.main-content h2{text-align:center;color:var(--accent);margin-bottom:20px;}
.card{background:var(--card-bg);padding:22px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.25);}
.card h3{margin-bottom:12px;color:var(--accent);}
input{padding:8px 10px;border-radius:6px;border:1px solid #444;background:#1f2937;color:#fff;width:100%;margin-bottom:12px;}
button{padding:8px 16px;border:none;border-radius:6px;background:var(--accent);color:#fff;font-weight:600;cursor:pointer;transition:.3s;}
button:hover{background:var(--accent-dark);}
.flash{padding:10px 14px;background:var(--accent);color:#000;border-radius:6px;font-weight:600;margin-bottom:16px;text-align:center;}
</style>
</head>
<body>
<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">ITAC</div>
    <div class="profile">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Professeur</p>
    </div>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="saisie_notes.php" class="active"><i class="fas fa-pen"></i> Saisie notes</a>
    <a href="planning.php"><i class="fas fa-calendar"></i> Planning</a>
    <a href="profil.php"><i class="fas fa-users-cog"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <main class="main-content">
    <h2>Modifier la note de <?=$note['prenom_e'].' '.$note['nom_e']?></h2>
    <?php if($m=flash()):?><div class="flash"><?=htmlspecialchars($m)?></div><?php endif; ?>
    
    <div class="card">
      <form method="post">
        <label>Étudiant :</label>
        <input type="text" value="<?=$note['prenom_e'].' '.$note['nom_e']?>" disabled>
        
        <label>Matière :</label>
        <input type="text" value="<?=$note['matiere_nom']?>" disabled>
        
        <label>Examen :</label>
        <input type="text" value="<?=$note['exam_nom'].' ('.$note['type_exam'].')'?>" disabled>
        
        <label>Note :</label>
        <input type="number" step="0.01" name="note" value="<?=$note['note']?>" required>
        
        <button type="submit">Enregistrer la modification</button>
      </form>
    </div>
  </main>
</div>
</body>
</html>
