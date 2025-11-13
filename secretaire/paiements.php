<?php
// secretaire/paiements.php
session_start();
require_once __DIR__ . "/../includes/config.php"; // $pdo

// Sécurité basique du rôle
if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2){
    header("Location: ../index.php"); // <-- ici mettre le vrai chemin du login
    exit;
}

$nom    = $_SESSION['user']['nom'];
$prenom = $_SESSION['user']['prenom'];
$photo  = $_SESSION['user']['photo'] ?? "default.png";
$success = '';
$error = '';

// Traitement du formulaire d'ajout
if(isset($_POST['submit'])) {
    try {
        $etudiant_id   = intval($_POST['etudiant_id'] ?? 0);
        $montant       = floatval($_POST['montant'] ?? 0);
        $versement_num = intval($_POST['versement_num'] ?? 0);
        $date_payment  = $_POST['date_payment'] ?? null;
        $method        = trim($_POST['method'] ?? '');
        $note          = trim($_POST['note'] ?? '');

        // Upload reçu
        $receiptPath = null;
        if(!empty($_FILES['receipt']['name']) && $_FILES['receipt']['error'] === 0){
            $ext = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
            $allowed = ['png','jpg','jpeg','gif','pdf'];
            if(!in_array(strtolower($ext), $allowed)){
                throw new Exception("Type de fichier non autorisé pour le reçu.");
            }
            $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destRelative = "uploads/" . $newName;
            $destFull = __DIR__ . "/../assets/" . $destRelative;
            if(!is_dir(dirname($destFull))) mkdir(dirname($destFull), 0755, true);
            if(!move_uploaded_file($_FILES['receipt']['tmp_name'], $destFull)){
                throw new Exception("Impossible d'uploader le fichier reçu.");
            }
            $receiptPath = $destRelative;
        }

        // Insertion
        $stmt = $pdo->prepare("INSERT INTO paiements (etudiant_id, montant, versement_num, date_payment, method, note, receipt) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$etudiant_id, $montant, $versement_num, $date_payment, $method, $note, $receiptPath]);

        $success = "✅ Paiement ajouté avec succès !";
    } catch (Exception $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

// Récupérer étudiants
$etudiants = $pdo->query("SELECT id, nom, prenom FROM users WHERE role_id = 4 ORDER BY nom, prenom")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer paiements
$paiements = $pdo->query("
    SELECT p.*, u.nom as etu_nom, u.prenom as etu_prenom 
    FROM paiements p
    JOIN users u ON p.etudiant_id = u.id
    ORDER BY p.date_payment DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secrétaire — Paiements | ITAC</title>
 
</head>
<body>

<div class="dashboard-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">
       <div class="logo" style="color: #fff;">ITAC</div>
     <div class="profile">
      <img src="../uploads/<?=$photo?>" alt="photo">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Secretaire</p>
    </div>
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
        <header class="topbar">
            <div class="welcome">
                <div class="welcome-text">💵 Gestion des paiements</div>
            </div>
        </header>

        <!-- FORMULAIRE AJOUT -->
        <section class="form-container">
            <div class="panel">
                <?php if($success): ?>
                    <div class="msg-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="msg-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" class="form-box">
                    <input name="etudiant_id" list="students" placeholder="Choisir un étudiant" required>
                    <datalist id="students">
                        <?php foreach($etudiants as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></option>
                        <?php endforeach; ?>
                    </datalist>

                    <input name="montant" type="number" step="0.01" placeholder="Montant (HTG)" required>
                    <input name="versement_num" type="number" min="1" max="3" placeholder="Versement numéro" required>
                    <input name="date_payment" type="date" required>
                    <input name="method" type="text" placeholder="Méthode de paiement" required>
                    <input name="note" type="text" placeholder="Note (facultatif)">
                    <input name="receipt" type="file" accept=".png,.jpg,.jpeg,.gif,.pdf">
                    <button type="submit" name="submit" class="btn-submit">Ajouter Paiement</button>
                </form>
            </div>
        </section>

        <!-- TABLEAU PAIEMENTS -->
        <section class="table-section">
            <div class="panel">
                <h2>Paiements enregistrés</h2>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Montant</th>
                            <th>Versement</th>
                            <th>Date</th>
                            <th>Méthode</th>
                            <th>Note</th>
                            <th>Reçu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($paiements) === 0): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">Aucun paiement enregistré</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($paiements as $p): ?>
                            <tr>
                                <td data-label="Étudiant"><?= htmlspecialchars($p['etu_nom'].' '.$p['etu_prenom']) ?></td>
                                <td data-label="Montant"><?= number_format($p['montant'],2) ?> HTG</td>
                                <td data-label="Versement"><?= (int)$p['versement_num'] ?></td>
                                <td data-label="Date"><?= htmlspecialchars($p['date_payment']) ?></td>
                                <td data-label="Méthode"><?= htmlspecialchars($p['method']) ?></td>
                                <td data-label="Note"><?= htmlspecialchars($p['note']) ?></td>
                                <td data-label="Reçu">
                                    <?php if($p['receipt']): ?>
                                        <a href="../assets/<?= htmlspecialchars($p['receipt']) ?>" target="_blank">Voir</a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

</body>
</html>


<style>
:root{
  --bg-main:#fef6e4;          /* Fond général secrétaire */
  --accent:#ef4444;           /* Accent principal (rouge) */
  --accent-dark:#b91c1c;      /* Accent sombre */
  --card-bg:#fff5f5;          /* Fond des cartes */
  --card-hover:#fee2e2;       /* Fond carte au hover */
  --text-light:#1f2937;       /* Texte principal */
}

*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:'Inter',sans-serif;
    background:var(--bg-main);
    color:var(--text-light);
    overflow-x:hidden;
}

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
    border:1px solid rgba(255,255,255,0.05);
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
/* ======== PANELS ======== */
.panel {
    background: #fff;
    padding: 22px 24px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    margin-bottom: 24px;
}

/* ======== MESSAGES ======== */
.msg-success {
    background-color: #d1fae5;
    color: #065f46;
    padding: 12px 16px;
    border-radius: 8px;
    font-weight: 700;
    margin-bottom: 16px;
    text-align: center;
}

.msg-error {
    background-color: #fee2e2;
    color: #991b1b;
    padding: 12px 16px;
    border-radius: 8px;
    font-weight: 700;
    margin-bottom: 16px;
    text-align: center;
}

/* ======== FORMULAIRE ======== */
.form-box {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
}

.form-box input,
.form-box select,
.form-box button {
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 14px;
    outline: none;
    transition: 0.2s;
}

.form-box input:focus,
.form-box select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 8px rgba(59,130,246,0.15);
}

.btn-submit {
    background-color: #3b82f6;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: 0.3s;
}

.btn-submit:hover {
    background-color: #2563eb;
}

/* ======== TABLEAU STYLÉ ======== */
.styled-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    margin-top: 16px;
}

.styled-table thead tr {
    background-color: #3b82f6;
    color: #ffffff;
    text-align: left;
    font-weight: 600;
}

.styled-table th,
.styled-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #e5e7eb;
}

.styled-table tbody tr {
    background-color: #ffffff;
    transition: background-color 0.2s ease-in-out;
}

.styled-table tbody tr:nth-of-type(even) {
    background-color: #f9fafb;
}

.styled-table tbody tr:hover {
    background-color: #e0f2fe;
}

.styled-table a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
}

.styled-table a:hover {
    text-decoration: underline;
}

/* ======== RESPONSIVE TABLE ======== */
@media (max-width: 768px) {
    .styled-table thead {
        display: none;
    }
    .styled-table, .styled-table tbody, .styled-table tr, .styled-table td {
        display: block;
        width: 100%;
    }
    .styled-table tr {
        margin-bottom: 16px;
        border-bottom: 2px solid #e5e7eb;
    }
    .styled-table td {
        text-align: right;
        padding-left: 50%;
        position: relative;
    }
    .styled-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 15px;
        width: 45%;
        padding-left: 15px;
        font-weight: 600;
        text-align: left;
    }
}
:root{
  --bg-main:#fef6e4;          /* Fond général secrétaire */
  --accent:#ef4444;           /* Accent principal (rouge) */
  --accent-dark:#b91c1c;      /* Accent sombre */
  --card-bg:#fff5f5;          /* Fond des cartes */
  --card-hover:#fee2e2;       /* Fond carte au hover */
  --text-light:#1f2937;       /* Texte principal */
}

*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:'Inter',sans-serif;
    background:var(--bg-main);
    color:var(--text-light);
    overflow-x:hidden;
}

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
    border:1px solid rgba(255,255,255,0.05);
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
.main-content h2{
    text-align:center;
    color:var(--accent);
    margin-bottom:20px;
}

/* Cards */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:18px;
    margin-bottom:30px;
}
.cards .card{
    background:var(--card-bg);
    padding:22px;
    border-radius:12px;
    text-align:center;
    font-weight:600;
    font-size:18px;
    box-shadow:0 6px 20px rgba(0,0,0,0.15);
    transition:.3s;
}
.cards .card:hover{
    background:var(--card-hover);
    transform:translateY(-6px);
    box-shadow:0 12px 30px rgba(0,0,0,0.25);
}

.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
/* Dashboard liens rapides */
.secretaire-dashboard{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:20px;
}
.secretaire-dashboard .card{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    padding:24px;
    background:var(--card-bg);
    color:var(--text-light);
    border-radius:12px;
    font-weight:600;
    font-size:16px;
    text-align:center;
    text-decoration:none;
    transition:.3s;
    box-shadow:0 5px 18px rgba(0,0,0,0.15);
}
.secretaire-dashboard .card i{
    font-size:40px;
    margin-bottom:12px;
    color:var(--accent);
}
.secretaire-dashboard .card:hover{
    background:var(--card-hover);
    transform:translateY(-5px);
    box-shadow:0 12px 28px rgba(0,0,0,0.25);
}

/* Responsive */
@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
    .cards{grid-template-columns:1fr;}
    .secretaire-dashboard{grid-template-columns:1fr;}
}

/* Avatar */
.avatar{
    width:60px;
    height:60px;
    border-radius:50%;
    border:3px solid var(--accent);
}
.topbar{
    display:flex;
    align-items:center;
    gap:20px;
}

</style>
