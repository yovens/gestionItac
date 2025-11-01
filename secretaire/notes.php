<?php
// secretaire/notes.php
session_start();
require_once "../includes/config.php"; // doit définir $pdo (PDO)

if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2){
    header("Location: ../index.php"); // <-- ici mettre le vrai chemin du login
    exit;
}

$secretaire_id = $_SESSION['user']['id'];
$nom = $_SESSION['user']['nom'];
$prenom = $_SESSION['user']['prenom'];
$photo = $_SESSION['user']['photo'] ?? 'default.png';

// notifications non lues
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND lu = 0");
$stmt->execute([$secretaire_id]);
$notifNonLues = (int)$stmt->fetchColumn();

// traitement du formulaire d'enregistrement de note
$message = "";
if (isset($_POST['save'])) {
    $etudiant_id = intval($_POST['etudiant_id'] ?? 0);
    $matiere_id  = intval($_POST['matiere_id'] ?? 0);
    $exam_id     = intval($_POST['exam_id'] ?? 0);
    $note        = floatval($_POST['note'] ?? 0);

    if ($etudiant_id && $matiere_id && $exam_id && ($note !== null)) {
        $ins = $pdo->prepare("INSERT INTO notes (etudiant_id, matiere_id, exam_id, note) VALUES (?, ?, ?, ?)");
        $ins->execute([$etudiant_id, $matiere_id, $exam_id, $note]);
        $message = "<div class='success-msg'>✅ Note enregistrée avec succès !</div>";
    } else {
        $message = "<div class='error-msg'>⚠️ Veuillez remplir tous les champs correctement.</div>";
    }
}

// récupérer listes pour selects
$students  = $pdo->query("SELECT id, nom, prenom FROM users WHERE role_id = 4 ORDER BY nom, prenom")->fetchAll(PDO::FETCH_ASSOC);
$matieres  = $pdo->query("SELECT id, nom FROM matieres ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
$examens   = $pdo->query("SELECT id, nom, type_exam, date_debut FROM examens ORDER BY date_debut DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

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
        <!-- topbar / welcome -->
        <header class="topbar">
        
           

        <!-- Notes section -->
        <section class="cards-container">
            <div class="card animate-card">
                <h3>📝 Enregistrer une note</h3>
                <?= $message ?>
                <form method="post" class="form-box" autocomplete="off">
                    <select name="etudiant_id" required>
                        <option value="">-- Choisir un étudiant --</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom'] . ' ' . $s['prenom']) ?></option>
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
                            <option value="<?= $ex['id'] ?>"><?= htmlspecialchars($ex['nom'] . ' (' . $ex['type_exam'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input name="note" type="number" step="0.01" min="0" max="100" placeholder="Note sur 100" required>
                    <button type="submit" name="save" class="btn-submit">Enregistrer</button>
                </form>
            </div>
        </section>
    </main>
</div>

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
body{
    font-family:'Inter',sans-serif;
    background:var(--bg-main);
    color:var(--text-light);
    overflow-x:hidden;
}

/* DASHBOARD GRID */
.dashboard-container{
    display:grid;
    grid-template-columns:280px 1fr;
    gap:28px;
    padding:32px;
    max-width:1300px;
    margin:0 auto;
}

/* Sidebar */
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

/* Main content */
.main-content{
    padding:28px;
    display:flex;
    flex-direction:column;
    gap:20px;
}
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}
.welcome{
    display:flex;
    align-items:center;
    gap:12px;
}
.avatar{
    width:60px;
    height:60px;
    border-radius:50%;
    border:3px solid var(--accent);
}
.cards-container{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:20px;
}
.card{
    background:var(--card-bg);
    padding:22px;
    border-radius:12px;
    box-shadow:0 6px 20px rgba(0,0,0,0.15);
    transition:.3s;
}
.card:hover{
    background:var(--card-hover);
    transform:translateY(-6px);
    box-shadow:0 12px 30px rgba(0,0,0,0.25);
}

/* Formulaire */
.form-box input, .form-box select{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:8px;
    outline:none;
    background:#f9fafb;
    transition:.3s;
}
.form-box input:focus, .form-box select:focus{
    border-color:#3b82f6;
    box-shadow:0 0 6px rgba(59,130,246,0.4);
}
.btn-submit{
    width:100%;
    padding:14px;
    border:none;
    border-radius:8px;
    background:#3b82f6;
    color:white;
    font-size:16px;
    font-weight:bold;
    cursor:pointer;
    transition:.3s;
}
.btn-submit:hover{
    background:#2563eb;
}

/* Animations */
.animate-card{
    opacity:0;
    animation:fadeInUp 1s ease forwards;
}
@keyframes fadeInUp{
    from{opacity:0; transform:translateY(30px);}
    to{opacity:1; transform:translateY(0);}
}

/* Notifications badge */
.badge{
    background:red;
    color:#fff;
    border-radius:50%;
    padding:2px 6px;
    font-size:12px;
    margin-left:6px;
}

/* Responsive */
@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
    .cards-container{grid-template-columns:1fr;}
}
</style>
