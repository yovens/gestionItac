<?php
// secretaire/notes.php
session_start();
require_once "../includes/config.php"; // doit définir $pdo

if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2){
    header("Location: ../index.php");
    exit;
}

$secretaire_id = $_SESSION['user']['id'];
$nom = $_SESSION['user']['nom'];
$prenom = $_SESSION['user']['prenom'];
$photo = $_SESSION['user']['photo'] ?? 'default.png';

$message = "";

// Enregistrement d'une note
if (isset($_POST['save'])) {
    $etudiant_id = intval($_POST['etudiant_id'] ?? 0);
    $matiere_id  = intval($_POST['matiere_id'] ?? 0);
    $exam_id     = intval($_POST['exam_id'] ?? 0);
    $note        = floatval($_POST['note'] ?? 0);

    if ($etudiant_id && $matiere_id && $exam_id) {
        $ins = $pdo->prepare("INSERT INTO notes (etudiant_id, matiere_id, exam_id, note) VALUES (?, ?, ?, ?)");
        $ins->execute([$etudiant_id, $matiere_id, $exam_id, $note]);
        $message = "<div class='success-msg'>✅ Note enregistrée avec succès !</div>";
    } else {
        $message = "<div class='error-msg'>⚠️ Veuillez remplir tous les champs correctement.</div>";
    }
}

// Suppression d'une note
if(isset($_POST['delete_note'])){
    $note_id = intval($_POST['note_id']);
    $del = $pdo->prepare("DELETE FROM notes WHERE id = ?");
    $del->execute([$note_id]);
    $message = "<div class='success-msg'>✅ Note supprimée avec succès !</div>";
}

// Récupération des listes
$students  = $pdo->query("SELECT id, nom, prenom FROM users WHERE role_id = 4 ORDER BY nom, prenom")->fetchAll(PDO::FETCH_ASSOC);
$matieres  = $pdo->query("SELECT id, nom FROM matieres ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$examens   = $pdo->query("SELECT id, nom, type_exam FROM examens ORDER BY date_debut DESC")->fetchAll(PDO::FETCH_ASSOC);

// Toutes les notes
$all_notes = $pdo->query("
    SELECT n.id as note_id, u.nom AS etu_nom, u.prenom AS etu_prenom,
           m.nom AS matiere, e.nom AS examen, e.type_exam, n.note
    FROM notes n
    JOIN users u ON n.etudiant_id = u.id
    JOIN matieres m ON n.matiere_id = m.id
    JOIN examens e ON n.exam_id = e.id
    ORDER BY m.nom, u.nom, u.prenom
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Notes — Secrétaire ITAC</title>
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
.cards-container{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;}
.card{background:var(--card-bg);padding:22px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.15);transition:.3s;}
.card:hover{background:var(--card-hover);transform:translateY(-6px);box-shadow:0 12px 30px rgba(0,0,0,0.25);}
.form-box input, .form-box select{width:100%;padding:12px;margin:10px 0;border:1px solid #ccc;border-radius:8px;background:#f9fafb;outline:none;transition:.3s;}
.form-box input:focus, .form-box select:focus{border-color:#3b82f6;box-shadow:0 0 6px rgba(59,130,246,0.4);}
.btn-submit{width:100%;padding:14px;border:none;border-radius:8px;background:#3b82f6;color:white;font-size:16px;font-weight:bold;cursor:pointer;transition:.3s;}
.btn-submit:hover{background:#2563eb;}
.styled-table{width:100%;border-collapse: collapse;margin-top:16px;}
.styled-table th, .styled-table td{padding:10px 12px;border-bottom:1px solid #ddd;text-align:center;}
.styled-table thead tr{background:#3b82f6;color:#fff;font-weight:600;}
.styled-table tbody tr:hover{background: #e0f2fe;}
.btn-delete{padding:6px 12px;border:none;border-radius:6px;background:#ef4444;color:white;font-weight:600;cursor:pointer;transition:0.3s;}
.btn-delete:hover{background:#b91c1c;}
.success-msg{background:#d1fae5;padding:10px;border-radius:8px;margin-bottom:10px;color:#065f46;}
.error-msg{background:#fee2e2;padding:10px;border-radius:8px;margin-bottom:10px;color:#b91c1c;}
.animate-card{opacity:0;animation:fadeInUp 1s ease forwards;}
@keyframes fadeInUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
@media(max-width:900px){.dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}.sidebar{flex-direction:row;overflow-x:auto;}.sidebar a{margin:0 8px;}.cards-container{grid-template-columns:1fr;}}
</style>
</head>
<body>

<div class="dashboard-container">
    <aside class="sidebar">
         <div class="logo" style="color: #fff;">ITAC</div>
        <div class="profile">
            <img src="../uploads/<?=$photo?>" alt="photo">
            <h3><?=$prenom." ".$nom?></h3>
            <p>Secrétaire</p>
        </div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="inscriptions.php"><i class="fas fa-book"></i> Inscriptions</a>
        <a href="examens.php"><i class="fas fa-book"></i> Examens</a>
        <a href="notes.php" class="active"><i class="fas fa-book"></i> Notes</a>
        <a href="bulletins.php"><i class="fas fa-file-pdf"></i> Bulletins</a>
        <a href="paiements.php"><i class="fas fa-file-pdf"></i> Paiements</a>
        <a href="calendrier.php"><i class="fas fa-calendar-alt"></i> Calendrier</a>
        <a href="releve_notes.php"><i class="fas fa-calendar-alt"></i> Relevé de notes</a>
        <a href="profil.php"><i class="fas fa-user"></i> Profil</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </aside>

    <main class="main-content">
        <?= $message ?>

        <section class="cards-container">
            <div class="card animate-card">
                <h3>📝 Enregistrer une note</h3>
                <form method="post" class="form-box" autocomplete="off">
                    <select name="etudiant_id" required>
                        <option value="">-- Choisir un étudiant --</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom'].' '.$s['prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="matiere_id" required>
                        <option value="">-- Choisir une matière --</option>
                        <?php foreach ($matieres as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="exam_id" required>
                        <option value="">-- Choisir un examen --</option>
                        <?php foreach ($examens as $ex): ?>
                            <option value="<?= $ex['id'] ?>"><?= htmlspecialchars($ex['nom'].' ('.$ex['type_exam'].')') ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input name="note" type="number" step="0.01" min="0" max="100" placeholder="Note sur 100" required>
                    <button type="submit" name="save" class="btn-submit">Enregistrer</button>
                </form>
            </div>
        </section>

        <section class="cards-container">
            <div class="card">
                <h3>📊 Toutes les notes des élèves</h3>
                <?php if(empty($all_notes)): ?>
                    <p>Aucune note enregistrée pour le moment.</p>
                <?php else: ?>
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Élève</th>
                                <th>Matière</th>
                                <th>Examen</th>
                                <th>Type</th>
                                <th>Note</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_notes as $n): ?>
                                <tr>
                                    <td><?= htmlspecialchars($n['etu_nom'].' '.$n['etu_prenom']) ?></td>
                                    <td><?= htmlspecialchars($n['matiere']) ?></td>
                                    <td><?= htmlspecialchars($n['examen']) ?></td>
                                    <td><?= htmlspecialchars($n['type_exam']) ?></td>
                                    <td><?= htmlspecialchars($n['note']) ?></td>
                                    <td>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="note_id" value="<?= $n['note_id'] ?>">
                                            <button type="submit" name="delete_note" onclick="return confirm('Supprimer cette note ?')" class="btn-delete">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

</body>
</html>
