<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'professeur') {
    header('Location: ..//index.php');
    exit;
}

$prof_id = $_SESSION['user']['id'];
$nom     = $_SESSION['user']['nom'];
$prenom  = $_SESSION['user']['prenom'];
$photo   = $_SESSION['user']['photo'] ?? "default.png";

// Affectations
$affs = $pdo->prepare("SELECT a.*, m.nom as matiere, c.nom as classe 
                       FROM affectations a 
                       JOIN matieres m ON a.matiere_id=m.id 
                       JOIN classes c ON a.classe_id=c.id 
                       WHERE a.prof_id=?");
$affs->execute([$prof_id]);
$affs = $affs->fetchAll(PDO::FETCH_ASSOC);

// Sauvegarde note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'save_note') {
    $etudiant_id = intval($_POST['etudiant_id']);
    $matiere_id  = intval($_POST['matiere_id']);
    $exam_id     = intval($_POST['exam_id']);
    $note        = floatval($_POST['note']);

    $stmt = $pdo->prepare("SELECT id FROM notes WHERE etudiant_id=? AND matiere_id=? AND exam_id=?");
    $stmt->execute([$etudiant_id,$matiere_id,$exam_id]);
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE notes SET note=? WHERE etudiant_id=? AND matiere_id=? AND exam_id=?")
            ->execute([$note,$etudiant_id,$matiere_id,$exam_id]);
    } else {
        $pdo->prepare("INSERT INTO notes (etudiant_id, matiere_id, exam_id, note) VALUES (?,?,?,?)")
            ->execute([$etudiant_id,$matiere_id,$exam_id,$note]);
    }
    flash("Note enregistrée avec succès !");
    header('Location: saisie_notes.php');
    exit;
}

$exams = $pdo->query("SELECT * FROM examens ORDER BY date_debut DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Saisie Notes — Professeur</title>
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

/* Form + Table */
form{margin-bottom:20px;}
label{font-weight:600;margin-right:10px;}
select,input{padding:6px 10px;border-radius:6px;border:1px solid #444;background:#1f2937;color:#fff;}
button{padding:6px 12px;border:none;border-radius:6px;background:var(--accent);color:#fff;font-weight:600;cursor:pointer;transition:.3s;}
button:hover{background:var(--accent-dark);}
.table-form table{width:100%;border-collapse:collapse;margin-top:15px;}
.table-form th,.table-form td{padding:10px;border-bottom:1px solid #444;text-align:left;}
.table-form th{background:var(--card-bg);color:var(--accent);}
.table-form tr:hover{background:rgba(255,255,255,0.05);}

/* Flash */
.flash{padding:10px 14px;background:var(--accent);color:#000;border-radius:6px;font-weight:600;margin-bottom:16px;text-align:center;}
</style>
</head>
<body>
<div class="floaties"><span class="f1"></span><span class="f2"></span><span class="f3"></span></div>

<div class="dashboard-container">
  <!-- Sidebar -->
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

  <!-- Main -->
  <main class="main-content">
    <h2>✏️ Saisie des Notes</h2>
    <?php if($m=flash()):?><div class="flash"><?=htmlspecialchars($m)?></div><?php endif; ?>

    <form id="chooseAssign" method="get">
      <label>Affectation :</label>
      <select name="aff" onchange="this.form.submit()">
        <option value="">-- choisir --</option>
        <?php foreach($affs as $a): ?>
          <option value="<?=$a['id']?>" <?=isset($_GET['aff']) && $_GET['aff']==$a['id'] ? 'selected':''?>>
            <?=$a['matiere'].' — '.$a['classe'].' ('.$a['session_nom'].')'?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <?php if (!empty($_GET['aff'])):
      $aid = intval($_GET['aff']);
      $a = $pdo->prepare("SELECT * FROM affectations WHERE id=?"); $a->execute([$aid]); $a = $a->fetch(PDO::FETCH_ASSOC);
      $students = $pdo->prepare("SELECT u.* FROM users u JOIN etudiants_classes ec ON u.id=ec.etudiant_id WHERE ec.classe_id=?");
      $students->execute([$a['classe_id']]);
    ?>
      <form method="post" class="table-form">
        <input type="hidden" name="matiere_id" value="<?= $a['matiere_id'] ?>">
        <label>Examen :</label>
        <select name="exam_id">
          <?php foreach($exams as $ex):?>
            <option value="<?=$ex['id']?>"><?=htmlspecialchars($ex['nom'].' ('.$ex['type_exam'].')')?></option>
          <?php endforeach;?>
        </select>
        <table>
          <thead><tr><th>Étudiant</th><th>Note</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach($students as $s): ?>
              <tr>
                <td><?=$s['prenom'].' '.$s['nom']?></td>
                <td><input type="number" step="0.01" name="note" required></td>
                <td>
                  <input type="hidden" name="etudiant_id" value="<?=$s['id']?>">
                  <button type="submit" name="action" value="save_note">Enregistrer</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </form>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
