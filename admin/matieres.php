<?php
// admin/matieres.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') header('Location: ../index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add') {
        $nom = $_POST['nom']; $code = $_POST['code']; $coef = intval($_POST['coef']);
        $pdo->prepare("INSERT INTO matieres (nom, code, coefficient) VALUES (?,?,?)")->execute([$nom,$code,$coef]);
        flash("Matière ajoutée");
    } elseif ($_POST['action'] === 'delete') {
        $pdo->prepare("DELETE FROM matieres WHERE id = ?")->execute([intval($_POST['id'])]);
        flash("Matière supprimée");
    }
    header('Location: matieres.php'); exit;
}

$matieres = $pdo->query("SELECT * FROM matieres")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Matières — Admin ITAC</title>
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

.admin-dashboard{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-top:20px;}
.admin-dashboard .card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;background:var(--card-bg);color:var(--text-light);border-radius:12px;font-weight:600;font-size:16px;text-align:center;text-decoration:none;transition:.3s;box-shadow:0 5px 18px rgba(0,0,0,0.25);}
.admin-dashboard .card i{font-size:40px;margin-bottom:12px;color:var(--accent);}
.admin-dashboard .card:hover{background:var(--card-hover);transform:translateY(-5px);box-shadow:0 12px 28px rgba(0,0,0,0.35);}

@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
    .admin-dashboard{grid-template-columns:1fr;}
}
</style>
</head>
<body>
<div class="floaties" aria-hidden="true">
  <span class="f1"></span>
  <span class="f2"></span>
  <span class="f3"></span>
</div>

<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">ITAC</div>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="classes.php"><i class="fas fa-building"></i> Classes</a>
    <a href="matieres.php" class="active"><i class="fas fa-book"></i> Matières</a>
    <a href="affectations.php"><i class="fas fa-tasks"></i> Affectations</a>
    <a href="utilisateurs.php"><i class="fas fa-users-cog"></i> Utilisateurs</a>
    <a href="paiements.php"><i class="fas fa-credit-card"></i> Paiements</a>
    <a href="profil.php"><i class="fas fa-users-cog"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <main class="main-content">
    <h2>Matières</h2>
    <?php if($m = flash()): ?><div class="flash"><?=htmlspecialchars($m)?></div><?php endif;?>

    <div class="form-box">
      <form method="post" class="form-inline">
        <input name="nom" placeholder="Nom" required>
        <input name="code" placeholder="Code" required>
        <input name="coef" placeholder="Coef" type="number" value="1" required>
        <input type="hidden" name="action" value="add">
        <button>Ajouter</button>
      </form>
    </div>

    <div class="table-container">
      <table>
        <thead><tr><th>ID</th><th>Nom</th><th>Code</th><th>Coef</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach($matieres as $m): ?>
          <tr>
            <td><?= $m['id'] ?></td>
            <td><?= htmlspecialchars($m['nom']) ?></td>
            <td><?= htmlspecialchars($m['code']) ?></td>
            <td><?= $m['coefficient'] ?></td>
            <td>
              <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?=$m['id']?>">
                <input type="hidden" name="action" value="delete">
                <button onclick="return confirm('Supprimer?')">Suppr</button>
              </form>
            </td>
          </tr>
        <?php endforeach;?>
        </tbody>
      </table>
    </div>

    <div class="admin-dashboard">
      <a href="dashboard.php" class="card"><i class="fas fa-tachometer-alt"></i><br>Tableau de bord</a>
      <a href="classes.php" class="card"><i class="fas fa-building"></i><br>Gérer Classes</a>
      <a href="matieres.php" class="card"><i class="fas fa-book"></i><br>Gérer Matières</a>
      <a href="affectations.php" class="card"><i class="fas fa-tasks"></i><br>Affectations</a>
      <a href="utilisateurs.php" class="card"><i class="fas fa-users-cog"></i><br>Utilisateurs</a>
      <a href="paiements.php" class="card"><i class="fas fa-credit-card"></i><br>Paiements</a>
    </div>
  </main>
</div>
</body>
</html>
