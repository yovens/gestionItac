<?php
session_start();
require_once "../includes/config.php";

// Vérifier que c’est bien un secrétaire connecté
if (!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'secretaire') {
    header("Location: ../index.php");
    exit;
}

$id = $_SESSION['user']['id'];

// Récupérer les infos du secrétaire
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role_id = 2 LIMIT 1");
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
<title>Profil Secrétaire</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
    --bg-main:#f3f4f6;
    --accent:#ef4444;
    --accent-dark:#b91c1c;
    --card-bg:#ffffff;
    --text-dark:#1f2937;
}

/* BODY */
body{
    font-family:'Inter',sans-serif;
    margin:0;
    background: var(--bg-main);
    color: var(--text-dark);
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
.topbar{
    display:flex;
    align-items:center;
    gap:20px;
}




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


</style>
</head>
<body>






<div class="dashboard-container">
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

  <main class="main-content">
  <header class="topbar">

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




















