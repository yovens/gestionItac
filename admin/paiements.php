<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$nom = $_SESSION['user']['nom'] ?? '';
$prenom = $_SESSION['user']['prenom'] ?? '';
$photo = $_SESSION['user']['photo'] ?? 'default.png';

/* === SUPPRESSION D'UN PAIEMENT === */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM paiements WHERE id = ?")->execute([$id]);
        flash("Paiement supprimé avec succès.");
    } catch (PDOException $e) {
        flash("Erreur : impossible de supprimer ce paiement (" . $e->getMessage() . ")");
    }
    header('Location: paiements.php');
    exit;
}

/* === AJOUT D'UN PAIEMENT === */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $etudiant_id = intval($_POST['etudiant_id']);
    $montant = floatval($_POST['montant']);
    $versement_num = intval($_POST['versement_num']);
    $method = $_POST['method'];
    $receipt = null;

    if (!empty($_FILES['receipt']['name'])) {
        $receipt = upload_file($_FILES['receipt']);
    }

    $pdo->prepare("INSERT INTO paiements (etudiant_id, montant, versement_num, date_payment, method, receipt)
                   VALUES (?,?,?,?,?,?)")
        ->execute([$etudiant_id, $montant, $versement_num, date('Y-m-d'), $method, $receipt]);

    flash("Paiement enregistré avec succès !");
    header('Location: paiements.php');
    exit;
}

/* === RÉCUPÉRATION DES ÉTUDIANTS === */
$students = $pdo->query("SELECT id, prenom, nom FROM users WHERE role_id = 4")->fetchAll(PDO::FETCH_ASSOC);

/* === RÉCUPÉRATION DES PAIEMENTS AVEC CLASSE === */
$paiements = $pdo->query("
    SELECT p.*, u.prenom, u.nom, c.nom AS classe_nom
    FROM paiements p
    LEFT JOIN users u ON p.etudiant_id = u.id
    LEFT JOIN etudiants_classes ec ON ec.etudiant_id = u.id
    LEFT JOIN classes c ON ec.classe_id = c.id
    ORDER BY p.date_payment DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Paiements — Admin ITAC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
  --bg-main:#0b0f2b; --accent:#f59e0b; --accent-dark:#b45309;
  --card-bg:#1f2937; --card-hover:#3b4252; --text-light:#f0f0f0;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0b0f2b,#1e1e3f);color:var(--text-light);}
.floaties{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden;}
.floaties span{position:absolute;border-radius:50%;opacity:0.08;filter:blur(22px);animation: floaty 16s linear infinite;}
.f1{width:280px;height:280px;top:10%;left:-20px;background:linear-gradient(45deg,var(--accent),rgba(245,158,11,0.3));animation-duration:20s;}
.f2{width:160px;height:160px;bottom:15%;right:5%;background:linear-gradient(45deg,var(--accent-dark),rgba(180,83,9,0.3));animation-duration:14s;animation-delay:3s;}
.f3{width:100px;height:100px;top:70%;left:40%;background:linear-gradient(90deg,#ffffff22,#00000000);animation-duration:18s;animation-delay:1s;}
@keyframes floaty{0%,100%{transform:translateY(0) rotate(0deg);}50%{transform:translateY(-25px) rotate(45deg);}}

/* Layout */
.dashboard-container{display:grid;grid-template-columns:280px 1fr;gap:28px;padding:32px;position:relative;z-index:5;max-width:1300px;margin:0 auto;}
.sidebar{background:rgba(20,20,40,0.85);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;border:1px solid rgba(255,255,255,0.05);}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar a{display:block;padding:12px 14px;margin:6px 0;border-radius:10px;color:var(--text-light);text-decoration:none;font-weight:600;transition:.3s;background:rgba(255,255,255,0.03);}
.sidebar a.active, .sidebar a:hover{background:var(--accent);color:#000;font-weight:700;transform:translateX(4px);}

.main-content{padding:28px;display:flex;flex-direction:column;gap:20px;}
.main-content h2{text-align:center;color:var(--accent);margin-bottom:20px;}
.form-box, .table-container{background:var(--card-bg);padding:22px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.25);}
.form-box form{display:flex;flex-wrap:wrap;gap:12px;align-items:center;}
.form-box input, .form-box select{flex:1;padding:10px;border-radius:8px;border:none;outline:none;background:#2a2f42;color:#fff;}
.form-box button{padding:10px 16px;border:none;border-radius:8px;background:var(--accent);color:#000;font-weight:600;cursor:pointer;transition:.3s;}
.form-box button:hover{background:var(--accent-dark);}
.flash{padding:12px;background:#065f46;color:#d1fae5;border-radius:8px;text-align:center;margin-bottom:14px;font-weight:600;}

.table-container table{width:100%;border-collapse:collapse;color:#fff;}
.table-container th, .table-container td{padding:12px;border-bottom:1px solid rgba(255,255,255,0.1);text-align:left;}
.table-container th{color:var(--accent);}
.btn-del{background:#dc2626;color:#fff;padding:6px 10px;border:none;border-radius:6px;cursor:pointer;font-weight:600;transition:.3s;}
.btn-del:hover{background:#b91c1c;}
.table-container a{color:var(--accent);text-decoration:none;}
.table-container a:hover{text-decoration:underline;}

@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
}
</style>
</head>
<body>
<div class="floaties"><span class="f1"></span><span class="f2"></span><span class="f3"></span></div>

<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">ITAC</div>
    <div class="profile">
      <img src="../uploads/<?=$photo?>" alt="photo" style="width:70px;height:70px;border-radius:50%;object-fit:cover;border:2px solid var(--accent);">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Admin</p>
    </div>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="classes.php"><i class="fas fa-building"></i> Classes</a>
    <a href="matieres.php"><i class="fas fa-book"></i> Matières</a>
    <a href="affectations.php"><i class="fas fa-tasks"></i> Affectations</a>
    <a href="utilisateurs.php"><i class="fas fa-users-cog"></i> Utilisateurs</a>
    <a href="paiements.php" class="active"><i class="fas fa-credit-card"></i> Paiements</a>
    <a href="profil.php"><i class="fas fa-user-cog"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <main class="main-content">
    <h2>Gestion des Paiements</h2>
    <?php if($m=flash()):?><div class="flash"><?=htmlspecialchars($m)?></div><?php endif;?>

    <div class="form-box">
      <form method="post" enctype="multipart/form-data">
        <select name="etudiant_id">
          <?php foreach($students as $s): ?>
            <option value="<?=$s['id']?>"><?=$s['prenom'].' '.$s['nom']?></option>
          <?php endforeach; ?>
        </select>
        <input name="montant" placeholder="Montant (en Gdes)" required>
        <select name="versement_num"><option>1</option><option>2</option><option>3</option></select>
        <input name="method" placeholder="Méthode">
        <input type="file" name="receipt">
        <input type="hidden" name="action" value="add">
        <button type="submit">Enregistrer</button>
      </form>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Étudiant</th>
            <th>Classe</th>
            <th>Montant</th>
            <th>Versement</th>
            <th>Date</th>
            <th>Reçu</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($paiements as $p): ?>
          <tr>
            <td><?=$p['id']?></td>
            <td><?=htmlspecialchars($p['prenom'].' '.$p['nom'])?></td>
            <td><?=htmlspecialchars($p['classe_nom'] ?? 'Non défini')?></td>
            <td><?=$p['montant']?> Gdes</td>
            <td><?=$p['versement_num']?></td>
            <td><?=$p['date_payment']?></td>
            <td><?= $p['receipt'] ? '<a href="/itac/'.$p['receipt'].'" target="_blank">Voir</a>' : '-' ?></td>
            <td>
              <a href="?delete=<?=$p['id']?>" onclick="return confirm('Supprimer ce paiement ?')" class="btn-del"><i class="fas fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
