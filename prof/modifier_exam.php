<?php
session_start();
require_once "../includes/config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 3){
    header("Location: ../index.php");
    exit;
}

$prof_id = $_SESSION['user']['id'];
$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM examens_prof WHERE id=? AND prof_id=?");
$stmt->execute([$id, $prof_id]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$exam){
    echo "Examen introuvable.";
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nom = $_POST['nom'];
    $type = $_POST['type_exam'];
    $date_exam = $_POST['date_exam'];
    $note = $_POST['note'];

    $pdo->prepare("UPDATE examens_prof SET nom=?, type_exam=?, date_exam=?, note=? WHERE id=? AND prof_id=?")
        ->execute([$nom, $type, $date_exam, $note, $id, $prof_id]);
    header("Location: examens.php");
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Modifier Examen — Prof | ITAC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg-main:#0b0f2b; --accent:#3b82f6; --accent-dark:#1e40af;
  --card-bg:#1f2937; --text-light:#f0f0f0;
}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0b0f2b,#1e1e3f);color:var(--text-light);
display:flex;justify-content:center;align-items:center;height:100vh;}
.card{background:var(--card-bg);padding:30px;border-radius:12px;max-width:480px;width:90%;box-shadow:0 6px 20px rgba(0,0,0,0.25);}
h2{text-align:center;color:var(--accent);margin-bottom:20px;}
input, select, textarea{width:100%;padding:12px;margin:10px 0;border:1px solid rgba(255,255,255,0.1);
border-radius:8px;background:#111827;color:#fff;}
button{width:100%;padding:12px;border:none;border-radius:8px;background:var(--accent);
color:white;font-weight:700;cursor:pointer;}
button:hover{background:var(--accent-dark);}
a{text-align:center;display:block;margin-top:10px;color:var(--accent);}
</style>
</head>
<body>
  <div class="card">
    <h2>Modifier l’examen</h2>
    <form method="post">
      <input name="nom" value="<?=htmlspecialchars($exam['nom'])?>" required>
      <select name="type_exam" required>
        <option value="intra" <?=$exam['type_exam']=='intra'?'selected':''?>>Intra</option>
        <option value="final" <?=$exam['type_exam']=='final'?'selected':''?>>Final</option>
        <option value="reprise" <?=$exam['type_exam']=='reprise'?'selected':''?>>Reprise</option>
      </select>
      <input type="date" name="date_exam" value="<?=$exam['date_exam']?>" required>
      <textarea name="note"><?=htmlspecialchars($exam['note'])?></textarea>
      <button>Enregistrer les modifications</button>
    </form>
    <a href="examens.php">⬅ Retour</a>
  </div>
</body>
</html>
