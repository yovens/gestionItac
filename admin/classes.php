<?php
// admin/classes.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';




if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$photo = $_SESSION['user']['photo'] ?? 'default.png';
$nom = $_SESSION['user']['nom'] ?? '';
$prenom = $_SESSION['user']['prenom'] ?? '';



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $nom = $_POST['nom'];
        $annee = intval($_POST['annee']);
        $stmt = $pdo->prepare("INSERT INTO classes (nom, annee) VALUES (?,?)");
        $stmt->execute([$nom, $annee]);
        flash("Classe ajoutée avec succès !");
    } elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        $pdo->prepare("DELETE FROM classes WHERE id = ?")->execute([$id]);
        flash("Classe supprimée !");
    }
    header('Location: classes.php');
    exit;
}

$classes = $pdo->query("SELECT * FROM classes ORDER BY annee, nom")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestion des classes — Admin ITAC</title>
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

/* Structure principale */
.dashboard-container{display:grid;grid-template-columns:280px 1fr;gap:28px;padding:32px;position:relative;z-index:5;max-width:1300px;margin:0 auto;}
.sidebar{background:rgba(20,20,40,0.85);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;border:1px solid rgba(255,255,255,0.05);}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar a{display:block;padding:12px 14px;margin:6px 0;border-radius:10px;color:var(--text-light);text-decoration:none;font-weight:600;transition:.3s;background:rgba(255,255,255,0.03);}
.sidebar a.active, .sidebar a:hover{background:var(--accent);color:#000;font-weight:700;transform:translateX(4px);}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}

.main-content{padding:28px;display:flex;flex-direction:column;gap:20px;}
.main-content h2{text-align:center;color:var(--accent);margin-bottom:20px;}

/* Formulaire */
.form-box, .table-container{background:var(--card-bg);padding:22px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.25);}
.form-box form{display:flex;flex-wrap:wrap;gap:12px;align-items:center;}
.form-box input, .form-box select{flex:1;padding:10px;border-radius:8px;border:none;outline:none;background:#2a2f42;color:#fff;}
.form-box button{padding:10px 16px;border:none;border-radius:8px;background:var(--accent);color:#000;font-weight:600;cursor:pointer;transition:.3s;}
.form-box button:hover{background:var(--accent-dark);}
.flash{padding:12px;background:#065f46;color:#d1fae5;border-radius:8px;text-align:center;margin-bottom:14px;font-weight:600;}

/* Tableau */
.table-container table{width:100%;border-collapse:collapse;color:#fff;}
.table-container th, .table-container td{padding:12px;border-bottom:1px solid rgba(255,255,255,0.1);text-align:left;}
.table-container th{color:var(--accent);}
.table-container button{padding:6px 12px;border:none;border-radius:8px;background:#e11d48;color:#fff;font-weight:600;cursor:pointer;transition:.3s;}
.table-container button:hover{background:#be123c;transform:scale(1.05);}

/* Dashboard bas */
.admin-dashboard{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-top:20px;}
.admin-dashboard .card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;background:var(--card-bg);color:var(--text-light);border-radius:12px;font-weight:600;font-size:16px;text-align:center;text-decoration:none;transition:.3s;box-shadow:0 5px 18px rgba(0,0,0,0.25);}
.admin-dashboard .card i{font-size:40px;margin-bottom:12px;color:var(--accent);}
.admin-dashboard .card:hover{background:var(--card-hover);transform:translateY(-5px);box-shadow:0 12px 28px rgba(0,0,0,0.35);}

/* Responsive */
@media(max-width:900px){
  .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
  .sidebar{flex-direction:row;overflow-x:auto;}
  .sidebar a{margin:0 8px;}
  .admin-dashboard{grid-template-columns:1fr;}
  .table-container table, .table-container thead, .table-container tbody, .table-container th, .table-container td, .table-container tr{
    display:block;width:100%;
  }
  .table-container tr{margin-bottom:12px;background:rgba(255,255,255,0.05);padding:10px;border-radius:8px;}
  .table-container td{border:none;padding:8px 6px;}
}
</style>
</head>
<body>
<div class="floaties" aria-hidden="true"><span class="f1"></span><span class="f2"></span><span class="f3"></span></div>

<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">ITAC</div>
    <div class="profile">
      <img src="../uploads/<?=$photo?>" alt="photo">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Admin</p>
    </div>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="classes.php" class="active"><i class="fas fa-building"></i> Classes</a>
    <a href="matieres.php"><i class="fas fa-book"></i> Matières</a>
    <a href="affectations.php"><i class="fas fa-tasks"></i> Affectations</a>
    <a href="utilisateurs.php"><i class="fas fa-users-cog"></i> Utilisateurs</a>
    <a href="paiements.php"><i class="fas fa-credit-card"></i> Paiements</a>
    <a href="profil.php"><i class="fas fa-user-circle"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <main class="main-content">
    <h2>📘 Gestion des classes</h2>
    <?php if ($m = flash()): ?><div class="flash"><?=htmlspecialchars($m)?></div><?php endif; ?>

    <div class="form-box">
      <form method="post">
        <input name="nom" placeholder="Nom (ex: 1ère année)" required>
        <select name="annee">
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
        </select>
        <input type="hidden" name="action" value="add">
        <button><i class="fas fa-plus-circle"></i> Ajouter</button>
      </form>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr><th>ID</th><th>Nom</th><th>Année</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach($classes as $c): ?>
          <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['nom']) ?></td>
            <td><?= $c['annee'] ?></td>
            <td>
              <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button onclick="return confirm('Supprimer cette classe ?')"><i class="fas fa-trash"></i> Supprimer</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="admin-dashboard">
      <a href="dashboard.php" class="card"><i class="fas fa-tachometer-alt"></i><br>Tableau de bord</a>
      <a href="classes.php" class="card"><i class="fas fa-building"></i><br>Gérer Classes</a>
      <a href="matieres.php" class="card"><i class="fas fa-book"></i><br>Matières</a>
      <a href="affectations.php" class="card"><i class="fas fa-tasks"></i><br>Affectations</a>
      <a href="utilisateurs.php" class="card"><i class="fas fa-users"></i><br>Utilisateurs</a>
      <a href="paiements.php" class="card"><i class="fas fa-credit-card"></i><br>Paiements</a>
    </div>
  </main>
</div>
</body>
</html>
