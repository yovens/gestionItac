<?php
// includes/header.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>ITAC - Gestion Universitaire</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="/itac/assets/css/animate.css">
  <script src="../assets/js/main.js" defer></script>
</head>
<body>
  <header class="topbar">
    <div class="brand">ITAC • Gestion</div>
    <nav class="nav">
      <?php if(isset($_SESSION['user'])): ?>
        <div class="welcome">
          <img src="<?php echo htmlspecialchars($_SESSION['user']['photo'] ?: '../assets/uploads/default.jpg'); ?>" alt="photo" class="avatar">
          <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']); ?></span>
        </div>
        <a href="../logout.php">Déconnexion</a>
      <?php else: ?>
        <a href="../index.php">Connexion</a>
      <?php endif; ?>
    </nav>
  </header>
  <main class="container">
