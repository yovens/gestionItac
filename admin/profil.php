<?php
session_start();
require_once "../includes/config.php";

// Vérifier que c’est bien un secrétaire connecté
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$nom = $_SESSION['user']['nom'] ?? '';
$prenom = $_SESSION['user']['prenom'] ?? '';
$photo = $_SESSION['user']['photo'] ?? 'default.png'; 
$id = $_SESSION['user']['id'];

// Récupérer les infos du secrétaire
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role_id = 1 LIMIT 1");
$stmt->execute([$id]);
$secretaire = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$secretaire) {
    die("Profil introuvable.");
}

// Traitement du changement de photo
if (isset($_POST['update_photo'])) {
    if (!empty($_FILES['photo']['name'])) {
        $fileName = time() . "_" . basename($_FILES['photo']['name']);
        $targetPath = "../assets/uploads/" . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE id = ?");
            $stmt->execute([$targetPath, $id]);

            $_SESSION['success'] = "Photo mise à jour avec succès !";
            header("Location: profil.php");
            exit;
        } else {
            $error = "Erreur lors de l’upload de l’image.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">




</head>
<body>





<div class="dashboard-container">
  <aside class="sidebar">
          <div class="logo" style="color: #fff;">ITAC</div>
     <div class="profile">
      <img src="../uploads/<?=$photo?>" alt="photo">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Admin</p>
    </div>
    <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="classes.php"><i class="fas fa-building"></i> Classes</a>
    <a href="matieres.php"><i class="fas fa-book"></i> Matières</a>
    <a href="affectations.php"><i class="fas fa-tasks"></i> Affectations</a>
    <a href="utilisateurs.php"><i class="fas fa-users-cog"></i> Utilisateurs</a>
    <a href="paiements.php"><i class="fas fa-credit-card"></i> Paiements</a>
    <a href="profil.php"><i class="fas fa-users-cog"></i> Profil</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
  </aside>

  <main class="main-content">

  <div class="container">
    <h2>Mon Profil</h2>

    <!-- Photo -->
    <img src="<?= $secretaire['photo'] ? htmlspecialchars($secretaire['photo']) : '../assets/default.png' ?>" 
         alt="Photo de profil" class="profil-img">

    <!-- Infos -->
    <p class="info"><strong>Nom :</strong> <?= htmlspecialchars($secretaire['nom']) ?></p>
    <p class="info"><strong>Prénom :</strong> <?= htmlspecialchars($secretaire['prenom']) ?></p>
    <p class="info"><strong>Email :</strong> <?= htmlspecialchars($secretaire['email']) ?></p>
    <p class="info"><strong>Téléphone :</strong> <?= htmlspecialchars($secretaire['phone']) ?></p>
    <p class="info"><strong>Nom d’utilisateur :</strong> <?= htmlspecialchars($secretaire['username']) ?></p>
    <p class="info"><strong>Date création :</strong> <?= htmlspecialchars($secretaire['created_at']) ?></p>

    <!-- Formulaire changement de photo -->
    <div class="upload-form">
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="photo" required>
            <br>
            <button type="submit" name="update_photo" class="btn"><i class="fas fa-camera"></i> Changer Photo</button>
        </form>
    </div>

    <?php if (isset($error)) : ?>
        <p class="alert error"><?= $error ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])) : ?>
        <p class="alert success"><?= $_SESSION['success'] ?></p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
</div>


  </main>
</div>

<style>
:root{
  --bg-main:#0b0f2b; --accent:#f59e0b; --accent-dark:#b45309;
  --card-bg:#1f2937; --card-hover:#3b4252; --text-light:#f0f0f0;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0b0f2b,#1e1e3f);color:var(--text-light);overflow-x:hidden;}
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

.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px;margin-bottom:30px;}
.cards .card{background:var(--card-bg);padding:22px;border-radius:12px;text-align:center;font-weight:600;font-size:18px;box-shadow:0 6px 20px rgba(0,0,0,0.25);transition:.3s;position:relative;overflow:hidden;}
.cards .card:hover{transform:translateY(-6px);box-shadow:0 12px 30px rgba(0,0,0,0.35);}

.admin-dashboard{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;}
.admin-dashboard .card{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;background:var(--card-bg);color:var(--text-light);border-radius:12px;font-weight:600;font-size:16px;text-align:center;text-decoration:none;transition:.3s;box-shadow:0 5px 18px rgba(0,0,0,0.25);}
.admin-dashboard .card i{font-size:40px;margin-bottom:12px;color:var(--accent);}
.admin-dashboard .card:hover{background:var(--card-hover);transform:translateY(-5px);box-shadow:0 12px 28px rgba(0,0,0,0.35);}

@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
    .cards{grid-template-columns:1fr;}
    .admin-dashboard{grid-template-columns:1fr;}
}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
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
.topbar{
    display:flex;
    align-items:center;
    gap:20px;
}
.avatar{
    width:60px;
    height:60px;
    border-radius:50%;
    border:3px solid var(--accent);
}







/* CONTAINER */
.container{
    max-width:800px;
    margin:40px auto;
    background:var(--card-bg);
    padding:30px;
    border-radius:14px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
}
h2{
    text-align:center;
    color:var(--accent);
    margin-bottom:20px;
}
.profil-img{
    display:block;
    margin:0 auto 20px;
    width:150px;
    height:150px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid var(--accent);
}
.info{
    margin:10px 0;
    font-size:16px;
}
.info strong{
    color:var(--accent-dark);
}
.upload-form{
    text-align:center;
    margin-top:20px;
}
.btn{
    display:inline-block;
    padding:10px 20px;
    background:var(--accent);
    color:#fff;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
    border:none;
    cursor:pointer;
    transition:.3s;
}
.btn:hover{
    background:var(--accent-dark);
}
.alert{
    text-align:center;
    margin-top:15px;
    font-weight:600;
}
.alert.success{ color:green; }
.alert.error{ color:red; }


</style>




















