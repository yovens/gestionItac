<?php
session_start();
require_once "../includes/config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 3){
    header("Location: ../index.php");
    exit;
}

$prof_id = $_SESSION['user']['id'];

// --- Ajout / Suppression examen prof et upload PDF
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if($_POST['action'] === 'add_exam_prof'){
        $nom = $_POST['nom'];
        $type = $_POST['type_exam'];
        $date_exam = $_POST['date_exam'];
        $matiere_id = $_POST['matiere_id'];
        $note = $_POST['note'];
        $fichier = null;

        if(isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK){
            $ext = pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
            if(strtolower($ext) === 'pdf'){
                $dir = "../uploads/examens/";
                if(!is_dir($dir)) mkdir($dir, 0777, true);
                $fichier_name = "exam_".time()."_".$prof_id.".pdf";
                move_uploaded_file($_FILES['fichier']['tmp_name'], $dir.$fichier_name);
                $fichier = $fichier_name;
            }
        }

        $pdo->prepare("INSERT INTO examens_prof (prof_id, matiere_id, nom, type_exam, date_exam, note, fichier)
                       VALUES (?,?,?,?,?,?,?)")
            ->execute([$prof_id, $matiere_id, $nom, $type, $date_exam, $note, $fichier]);
    }
    elseif($_POST['action'] === 'delete_exam'){
        $id = $_POST['id'];
        $f = $pdo->prepare("SELECT fichier FROM examens_prof WHERE id=? AND prof_id=?");
        $f->execute([$id,$prof_id]);
        $row = $f->fetch(PDO::FETCH_ASSOC);
        if($row && $row['fichier'] && file_exists("../uploads/examens/".$row['fichier'])){
            unlink("../uploads/examens/".$row['fichier']);
        }
        $pdo->prepare("DELETE FROM examens_prof WHERE id=? AND prof_id=?")->execute([$id, $prof_id]);
    }
    header("Location: examens.php");
    exit;
}

$examens_planifies = $pdo->query("SELECT * FROM examens ORDER BY date_debut DESC")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT e.*, m.nom AS matiere_nom
    FROM examens_prof e
    JOIN matieres m ON e.matiere_id = m.id
    WHERE e.prof_id = ?
    ORDER BY e.date_exam DESC
");
$stmt->execute([$prof_id]);
$examens_prof = $stmt->fetchAll(PDO::FETCH_ASSOC);

$matieres = $pdo->prepare("
    SELECT DISTINCT m.id, m.nom 
    FROM affectations a
    JOIN matieres m ON a.matiere_id = m.id
    WHERE a.prof_id = ?
");
$matieres->execute([$prof_id]);
$matieres = $matieres->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Examens — Prof | ITAC</title>
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
.card{background:var(--card-bg);padding:22px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.25);overflow-x:auto;}
.card h3{margin-bottom:12px;color:var(--accent);}
.form-box input, .form-box select, .form-box textarea{
  width:100%;padding:12px;margin:10px 0;border:1px solid rgba(255,255,255,0.1);
  border-radius:8px;background:#111827;color:#fff;
}
.btn{padding:12px 16px;border:none;border-radius:8px;background:var(--accent);
color:#fff;font-weight:700;cursor:pointer;transition:.3s;}
.btn:hover{background:var(--accent-dark);}
.table{width:100%;border-collapse:collapse;background:var(--card-bg);
border-radius:12px;overflow:hidden;margin-top:12px;display:block;overflow-x:auto;}
.table table{width:100%;border-collapse:collapse;}
.table th,.table td{padding:12px 16px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.08);}
.table th{background:var(--accent-dark);color:#fff;}
.table tr:hover{background:var(--card-hover);}
.actions form{display:inline;}
.actions button{background:none;border:none;color:#f87171;cursor:pointer;font-size:16px;}
.actions a{color:#3b82f6;text-decoration:none;margin-right:8px;}
input[type="file"]{color:#ccc;}
a.pdf-link{color:#3b82f6;text-decoration:none;font-weight:600;}
a.pdf-link:hover{text-decoration:underline;}
@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;white-space:nowrap;}
}
@media(max-width:600px){
    .form-box input, .form-box select, .form-box textarea{font-size:14px;padding:8px;}
    .btn{padding:10px 12px;font-size:14px;}
    .table th,.table td{padding:8px;font-size:13px;}
}
</style>
</head>
<body>
<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">Prof</div>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
    <a href="saisie_notes.php"><i class="fas fa-pen"></i> Saisie notes</a>
    <a href="classes.php"><i class="fas fa-users"></i> Mes classes</a>
    <a href="examens.php" class="active"><i class="fas fa-book"></i> Examens</a>
    <a href="planning.php"><i class="fas fa-calendar"></i> Mon planning</a>
    <a href="profil.php"><i class="fas fa-users-cog"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <main class="main-content">
    <h2>Gestion des examens</h2>

    <div class="card">
      <h3>Créer un examen</h3>
      <form method="post" enctype="multipart/form-data" class="form-box">
        <input type="hidden" name="action" value="add_exam_prof">
        <input name="nom" placeholder="Nom examen" required>
        <select name="matiere_id" required>
          <option value="">-- Sélectionnez la matière --</option>
          <?php foreach($matieres as $m): ?>
          <option value="<?=$m['id']?>"><?=$m['nom']?></option>
          <?php endforeach; ?>
        </select>
        <select name="type_exam" required>
          <option value="intra">Intra</option>
          <option value="final">Final</option>
          <option value="reprise">Reprise</option>
        </select>
        <input name="date_exam" type="date" required>
        <textarea name="note" placeholder="Note ou consigne (optionnel)"></textarea>
        <input type="file" name="fichier" accept="application/pdf">
        <button class="btn">Créer</button>
      </form>
    </div>

    <div class="card">
      <h3>Mes examens préparés</h3>
      <div class="table">
        <table>
          <thead>
            <tr><th>Nom</th><th>Matière</th><th>Type</th><th>Date</th><th>Note</th><th>Fichier PDF</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach($examens_prof as $ex): ?>
            <tr>
              <td><?=htmlspecialchars($ex['nom'])?></td>
              <td><?=htmlspecialchars($ex['matiere_nom'])?></td>
              <td><?=htmlspecialchars($ex['type_exam'])?></td>
              <td><?=$ex['date_exam']?></td>
              <td><?=htmlspecialchars($ex['note'])?></td>
              <td>
                <?php if(!empty($ex['fichier'])): ?>
                  <a class="pdf-link" href="../uploads/examens/<?=htmlspecialchars($ex['fichier'])?>" target="_blank"><i class="fas fa-file-pdf"></i> Voir PDF</a>
                <?php else: ?>
                  Aucun fichier
                <?php endif; ?>
              </td>
              <td class="actions">
                <a href="modifier_exam.php?id=<?=$ex['id']?>"><i class="fas fa-edit"></i></a>
                <form method="post" onsubmit="return confirm('Supprimer cet examen ?')">
                  <input type="hidden" name="action" value="delete_exam">
                  <input type="hidden" name="id" value="<?=$ex['id']?>">
                  <button><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <h3>Examens planifiés (secrétariat)</h3>
      <div class="table">
        <table>
          <thead>
            <tr><th>Nom</th><th>Type</th><th>Début</th><th>Fin</th><th>Session</th></tr>
          </thead>
          <tbody>
            <?php foreach($examens_planifies as $ex): ?>
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
      </div>
    </div>

  </main>
</div>
</body>
</html>
