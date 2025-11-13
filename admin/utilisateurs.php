<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') header('Location: ../index.php');

$nom = $_SESSION['user']['nom'] ?? '';
$prenom = $_SESSION['user']['prenom'] ?? '';
$photo = $_SESSION['user']['photo'] ?? 'default.png'; 

$roles = $pdo->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);

// ==== Compteurs pour les cartes ====
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 3")->fetchColumn();
$total_profs    = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 2")->fetchColumn();
$total_classes  = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add') {
        $role_id = intval($_POST['role_id']);
        $nom = $_POST['nom']; $prenom = $_POST['prenom']; $phone = $_POST['phone'];
        $email = $_POST['email']; $username = $_POST['username']; $password = $_POST['password']; 
        $photo = null;
        if (!empty($_FILES['photo']['name'])) $photo = upload_file($_FILES['photo']);
        $pdo->prepare("INSERT INTO users (role_id, nom, prenom, phone, email, username, password, photo) VALUES (?,?,?,?,?,?,?,?)")
            ->execute([$role_id,$nom,$prenom,$phone,$email,$username,$password,$photo]);
        flash("Utilisateur ajouté");
        header('Location: utilisateurs.php'); exit;
    } elseif ($_POST['action'] === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([intval($_POST['id'])]);
        flash("Utilisateur supprimé"); header('Location: utilisateurs.php'); exit;
    }
}

$users = $pdo->query("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Utilisateurs — Admin ITAC</title>
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

/* ===== Layout ===== */
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
@media (max-width: 600px) {
  .admin-dashboard .card i {
    font-size: 30px;
  }
  .admin-dashboard .card {
    padding: 16px;
    font-size: 14px;
  }
}
html, body {
  width: 100%;
  overflow-x: hidden; /* Empêche le scroll horizontal */
}

.dashboard-container {
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
}

.table-container {
  width: 100%;
  overflow-x: auto; /* Permet le scroll horizontal seulement dans la table si trop large */
  border-radius: 10px;
}

/* Table responsive */
.table-container table {
  width: 100%;
  min-width: 600px; /* Empêche la table d’écraser les colonnes sur petits écrans */
}

/* Sidebar responsive : collapse sur mobile */
@media (max-width: 900px) {
  .dashboard-container {
    display: flex;
    flex-direction: column;
    padding: 16px;
    gap: 16px;
  }

  .sidebar {
    display: flex;
    flex-direction: row;
    overflow-x: auto;
    justify-content: space-around;
    border-radius: 0;
    background: rgba(10,10,25,0.9);
  }

  .sidebar a {
    white-space: nowrap;
    font-size: 14px;
    padding: 10px;
  }

  .main-content {
    padding: 14px;
  }

  .form-box form {
    flex-direction: column;
  }

  .form-box input, .form-box select, .form-box button {
    width: 100%;
  }
}

/* ===== Cards ===== */
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:18px;margin-bottom:20px;}
.cards .card{background:var(--card-bg);padding:24px;border-radius:12px;text-align:center;box-shadow:0 5px 18px rgba(0,0,0,0.25);transition:.3s;}
.cards .card:hover{background:var(--card-hover);transform:translateY(-5px);}
.cards .card i{font-size:40px;color:var(--accent);margin-bottom:10px;}
.cards .card span{display:block;font-size:22px;font-weight:700;color:var(--text-light);}

/* ===== Table & Form ===== */
.form-box, .table-container{background:var(--card-bg);padding:22px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.25);}
.form-box form{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;align-items:center;}
.form-box input, .form-box select{padding:10px;border-radius:8px;border:none;outline:none;background:#2a2f42;color:#fff;}
.form-box button{padding:10px 16px;border:none;border-radius:8px;background:var(--accent);color:#000;font-weight:600;cursor:pointer;transition:.3s;}
.form-box button:hover{background:var(--accent-dark);}
.flash{padding:12px;background:#065f46;color:#d1fae5;border-radius:8px;text-align:center;margin-bottom:14px;font-weight:600;}

.table-container table{width:100%;border-collapse:collapse;color:#fff;}
.table-container th, .table-container td{padding:12px;border-bottom:1px solid rgba(255,255,255,0.1);text-align:left;}
.table-container th{color:var(--accent);}
.mini-avatar{width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--accent);}
.table-container button{background:#e11d48;border:none;color:#fff;padding:8px 12px;border-radius:6px;font-weight:600;cursor:pointer;transition:.3s;}
.table-container button:hover{background:#9f1239;}

/* ===== Responsive ===== */
@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
    .cards{grid-template-columns:1fr;}
}
</style>
</head>
<body>
<div class="floaties" aria-hidden="true">
  <span class="f1"></span><span class="f2"></span><span class="f3"></span>
</div>

<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo" style="color: #fff;">ITAC</div>
    <div class="profile">
      <img src="../uploads/<?=$photo?>" onerror="this.src='../uploads/default.png';" alt="photo">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Admin</p>
    </div>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="classes.php"><i class="fas fa-building"></i> Classes</a>
    <a href="matieres.php"><i class="fas fa-book"></i> Matières</a>
    <a href="affectations.php"><i class="fas fa-tasks"></i> Affectations</a>
    <a href="utilisateurs.php" class="active"><i class="fas fa-users-cog"></i> Utilisateurs</a>
    <a href="paiements.php"><i class="fas fa-credit-card"></i> Paiements</a>
    <a href="profil.php"><i class="fas fa-user-circle"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <main class="main-content">
    <h2>Utilisateurs</h2>
    <?php if($msg = flash()): ?><div class="flash"><?=htmlspecialchars($msg)?></div><?php endif;?>

   

    <!-- ==== Formulaire ==== -->
    <div class="form-box">
      <form method="post" enctype="multipart/form-data">
        <select name="role_id" required>
          <?php foreach($roles as $r): ?><option value="<?=$r['id']?>"><?=htmlspecialchars($r['name'])?></option><?php endforeach;?>
        </select>
        <input name="nom" placeholder="Nom" required>
        <input name="prenom" placeholder="Prénom" required>
        <input name="phone" placeholder="Téléphone">
        <input name="email" placeholder="Email">
        <input name="username" placeholder="Nom d'utilisateur" required>
        <input name="password" placeholder="Mot de passe" required>
        <input type="file" name="photo" accept="image/*">
        <input type="hidden" name="action" value="add">
        <button>Ajouter</button>
      </form>
    </div>

    <!-- ==== Tableau ==== -->
    <div class="table-container">
      <table>
        <thead><tr><th>#</th><th>Photo</th><th>Nom</th><th>Rôle</th><th>Username</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($users as $u): 
              $userPhoto = "../uploads/".($u['photo'] ?: "default.png"); ?>
          <tr>
            <td><?=$u['id']?></td>
            <td><img src="<?=$userPhoto?>" onerror="this.src='../uploads/default.png';" class="mini-avatar"></td>
            <td><?=htmlspecialchars($u['prenom'].' '.$u['nom'])?></td>
            <td><?=htmlspecialchars($u['role_name'])?></td>
            <td><?=htmlspecialchars($u['username'])?></td>
            <td>
              <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?=$u['id']?>">
                <input type="hidden" name="action" value="delete">
                <button onclick="return confirm('Supprimer cet utilisateur ?')"><i class="fas fa-trash-alt"></i> Supprimer</button>
              </form>
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
