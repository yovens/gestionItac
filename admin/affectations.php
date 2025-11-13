<?php
// admin/affectations.php — version responsive et stylée
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') header('Location: ../index.php');

$nom = $_SESSION['user']['nom'] ?? '';
$prenom = $_SESSION['user']['prenom'] ?? '';
$photo = $_SESSION['user']['photo'] ?? 'default.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add') {
        $prof = intval($_POST['prof_id']);
        $matiere = intval($_POST['matiere_id']);
        $classe = intval($_POST['classe_id']);
        $session_nom = trim($_POST['session_nom']);
        $pdo->prepare("INSERT INTO affectations (prof_id, matiere_id, classe_id, session_nom) VALUES (?,?,?,?)")
            ->execute([$prof, $matiere, $classe, $session_nom]);
        flash("✅ Affectation ajoutée avec succès !");
    } elseif ($_POST['action'] === 'delete') {
        $pdo->prepare("DELETE FROM affectations WHERE id = ?")->execute([intval($_POST['id'])]);
        flash("❌ Affectation supprimée !");
    }
    header('Location: affectations.php');
    exit;
}

$profs = $pdo->query("SELECT id, prenom, nom FROM users WHERE role_id = 3")->fetchAll(PDO::FETCH_ASSOC);
$matieres = $pdo->query("SELECT * FROM matieres")->fetchAll(PDO::FETCH_ASSOC);
$classes = $pdo->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$affects = $pdo->query("
    SELECT a.*, u.prenom, u.nom, m.nom AS matiere, c.nom AS classe 
    FROM affectations a
    LEFT JOIN users u ON a.prof_id=u.id
    LEFT JOIN matieres m ON a.matiere_id=m.id
    LEFT JOIN classes c ON a.classe_id=c.id
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Affectations — ITAC Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
  --bg-main:#0b0f2b; --accent:#f59e0b; --accent-dark:#b45309;
  --card-bg:#1f2937; --card-hover:#3b4252; --text-light:#f0f0f0;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0b0f2b,#1e1e3f);color:var(--text-light);min-height:100vh;}
a{text-decoration:none;}

.dashboard-container{display:grid;grid-template-columns:280px 1fr;gap:28px;padding:32px;max-width:1300px;margin:0 auto;}
.sidebar{background:rgba(20,20,40,0.9);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);}
.sidebar .profile{text-align:center;margin-bottom:12px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;border:2px solid var(--accent);margin-bottom:6px;}
.sidebar .profile h3{font-size:17px;font-weight:600;}
.sidebar a{display:flex;align-items:center;gap:8px;padding:10px;border-radius:10px;color:var(--text-light);transition:.3s;background:rgba(255,255,255,0.03);}
.sidebar a:hover,.sidebar a.active{background:var(--accent);color:#000;font-weight:700;transform:translateX(4px);}
.sidebar a i{min-width:22px;text-align:center;}

.main-content{padding:24px;display:flex;flex-direction:column;gap:20px;}
.main-content h2{text-align:center;color:var(--accent);margin-bottom:10px;}
.form-box,.table-container{background:var(--card-bg);padding:20px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.25);}
.form-box form{display:flex;flex-wrap:wrap;gap:10px;align-items:center;}
.form-box select,.form-box input{flex:1;min-width:150px;padding:10px;border-radius:8px;border:none;outline:none;background:#2a2f42;color:#fff;}
.form-box button{padding:10px 16px;border:none;border-radius:8px;background:var(--accent);color:#000;font-weight:600;cursor:pointer;transition:.3s;}
.form-box button:hover{background:var(--accent-dark);}
.table-container table{width:100%;border-collapse:collapse;}
.table-container th,.table-container td{padding:10px;border-bottom:1px solid rgba(255,255,255,0.1);text-align:left;}
.table-container th{color:var(--accent);}
.table-container form button{background:#ef4444;border:none;padding:6px 10px;border-radius:6px;color:#fff;cursor:pointer;}
.table-container form button:hover{background:#b91c1c;}

.flash{padding:10px;text-align:center;background:#065f46;color:#d1fae5;border-radius:8px;font-weight:600;}

.admin-dashboard{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-top:20px;}
.admin-dashboard .card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;background:var(--card-bg);border-radius:12px;text-align:center;transition:.3s;box-shadow:0 5px 18px rgba(0,0,0,0.25);}
.admin-dashboard .card i{font-size:40px;margin-bottom:8px;color:var(--accent);}
.admin-dashboard .card:hover{background:var(--card-hover);transform:translateY(-5px);}

@media(max-width:900px){
  .dashboard-container{grid-template-columns:1fr;padding:16px;}
  .sidebar{flex-direction:row;overflow-x:auto;gap:10px;padding:10px;}
  .sidebar a{flex:none;white-space:nowrap;padding:8px 10px;}
  .form-box form{flex-direction:column;}
  .table-container{overflow-x:auto;}
  .admin-dashboard{grid-template-columns:repeat(auto-fit,minmax(150px,1fr));}
}
</style>
</head>
<body>
<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">ITAC</div>
    <div class="profile">
      <img src="../uploads/<?=$photo?>" alt="photo admin">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Administrateur</p>
    </div>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
    <a href="classes.php"><i class="fas fa-building"></i>Classes</a>
    <a href="matieres.php"><i class="fas fa-book"></i>Matières</a>
    <a href="affectations.php" class="active"><i class="fas fa-tasks"></i>Affectations</a>
    <a href="utilisateurs.php"><i class="fas fa-users-cog"></i>Utilisateurs</a>
    <a href="paiements.php"><i class="fas fa-credit-card"></i>Paiements</a>
    <a href="profil.php"><i class="fas fa-user-circle"></i>Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
  </aside>

  <main class="main-content">
    <h2>Gestion des Affectations</h2>
    <?php if($m = flash()): ?><div class="flash"><?=htmlspecialchars($m)?></div><?php endif;?>

    <div class="form-box">
      <form method="post">
        <select name="prof_id" required>
          <option value="">-- Professeur --</option>
          <?php foreach($profs as $p): ?>
            <option value="<?=$p['id']?>"><?=htmlspecialchars($p['prenom'].' '.$p['nom'])?></option>
          <?php endforeach; ?>
        </select>
        <select name="matiere_id" required>
          <option value="">-- Matière --</option>
          <?php foreach($matieres as $m): ?>
            <option value="<?=$m['id']?>"><?=htmlspecialchars($m['nom'])?></option>
          <?php endforeach; ?>
        </select>
        <select name="classe_id" required>
          <option value="">-- Classe --</option>
          <?php foreach($classes as $c): ?>
            <option value="<?=$c['id']?>"><?=htmlspecialchars($c['nom'])?></option>
          <?php endforeach; ?>
        </select>
        <input name="session_nom" placeholder="Ex: 1ère session" required>
        <input type="hidden" name="action" value="add">
        <button type="submit"><i class="fas fa-plus-circle"></i> Ajouter</button>
      </form>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr><th>#</th><th>Professeur</th><th>Matière</th><th>Classe</th><th>Session</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach($affects as $a): ?>
          <tr>
            <td><?=$a['id']?></td>
            <td><?=$a['prenom'].' '.$a['nom']?></td>
            <td><?=htmlspecialchars($a['matiere'])?></td>
            <td><?=htmlspecialchars($a['classe'])?></td>
            <td><?=htmlspecialchars($a['session_nom'])?></td>
            <td>
              <form method="post" onsubmit="return confirm('Supprimer cette affectation ?');">
                <input type="hidden" name="id" value="<?=$a['id']?>">
                <input type="hidden" name="action" value="delete">
                <button><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="admin-dashboard">
      <a href="dashboard.php" class="card"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
      <a href="classes.php" class="card"><i class="fas fa-building"></i>Classes</a>
      <a href="matieres.php" class="card"><i class="fas fa-book"></i>Matières</a>
      <a href="affectations.php" class="card"><i class="fas fa-tasks"></i>Affectations</a>
      <a href="utilisateurs.php" class="card"><i class="fas fa-users-cog"></i>Utilisateurs</a>
      <a href="paiements.php" class="card"><i class="fas fa-credit-card"></i>Paiements</a>
    </div>
  </main>
</div>
</body>
</html>
