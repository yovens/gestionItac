<?php
// eleve/dashboard.php
// Page Dashboard Élève — style différent du secrétaire

require_once __DIR__ . "/../includes/config.php"; // adapte le chemin si besoin

// Démarrer la session seulement si nécessaire (évite les warnings double session_start)
if (session_status() === PHP_SESSION_NONE) session_start();

// Vérifie rôle (4 = élève)
if (!isset($_SESSION['user']) || ($_SESSION['user']['role_id'] ?? 0) != 4) {
    header("Location: ../index.php");
    exit;
}

$nom = $_SESSION['user']['nom'] ?? '';
$prenom = $_SESSION['user']['prenom'] ?? '';
$photo = $_SESSION['user']['photo'] ?? 'default.png'; // si pas de photo fournir default.png
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Élève — Tableau de bord | ITAC</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Rubik:wght@500;700&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg-1: #0f1724;      /* deep midnight */
      --accent-1: #7c3aed;  /* purple */
      --accent-2: #06b6d4;  /* teal */
      --card-bg: rgba(255,255,255,0.06);
      --glass: rgba(255,255,255,0.06);
      --muted: #cbd5e1;
      --glass-border: rgba(255,255,255,0.06);
    }

    /* Reset */
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%}
    body{
      font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: radial-gradient(800px 400px at 10% 10%, rgba(124,58,237,0.12), transparent 8%),
                  radial-gradient(700px 350px at 90% 80%, rgba(6,182,212,0.08), transparent 10%),
                  linear-gradient(180deg, #071024 0%, #07102a 40%, #081230 100%);
      color:#e6eef8;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      overflow-y:auto;
    }

    /* decorative floating circles (different from secrétaire) */
    .floaties{
      pointer-events:none;
      position:fixed; inset:0; z-index:0;
      overflow:hidden;
    }
    .floaties span{
      position:absolute;
      display:block;
      border-radius:50%;
      opacity:0.12;
      filter: blur(18px);
      transform: translate3d(0,0,0);
      animation: floaty 12s linear infinite;
    }
    .floaties .f1{ width:260px; height:260px; left:-30px; top:10%; background:linear-gradient(135deg,var(--accent-1), rgba(124,58,237,0.4)); animation-duration:18s; }
    .floaties .f2{ width:160px; height:160px; right:5%; top:60%; background:linear-gradient(135deg,var(--accent-2), rgba(6,182,212,0.4)); animation-duration:14s; animation-delay:2s;}
    .floaties .f3{ width:100px; height:100px; left:30%; top:75%; background:linear-gradient(90deg,#ffffff22,#00000000); animation-duration:20s; animation-delay:3s; opacity:0.06}

    @keyframes floaty {
      0%{ transform: translateY(0) rotate(0deg); }
      50%{ transform: translateY(-30px) rotate(45deg); }
      100%{ transform: translateY(0) rotate(0deg); }
    }

    /* Top nav (distincte) */
    .topnav{
      position:sticky; top:0; z-index:10;
      backdrop-filter: blur(6px);
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border-bottom: 1px solid rgba(255,255,255,0.03);
      padding:14px 28px;
      display:flex; align-items:center; gap:20px; justify-content:space-between;
      box-shadow: 0 2px 20px rgba(2,6,23,0.25);
    }
    .brand {
      display:flex; align-items:center; gap:12px;
    }
    .brand .logo {
      width:44px; height:44px; border-radius:10px;
      background: linear-gradient(135deg,var(--accent-1),var(--accent-2));
      display:flex; align-items:center; justify-content:center; font-weight:700; font-family:'Rubik',sans-serif;
      box-shadow:0 6px 18px rgba(6,182,212,0.08);
    }
    .brand h1{ font-size:16px; letter-spacing:0.3px; font-weight:700; color:#fff; }
    .brand p{ font-size:12px; color:var(--muted); margin-top:2px; }

    .top-actions{ display:flex; align-items:center; gap:12px; }
    .notif-btn{
      background:transparent; border:1px solid rgba(255,255,255,0.04);
      padding:8px 12px; border-radius:10px; color:var(--muted);
      display:flex; align-items:center; gap:8px; cursor:pointer;
    }
    .profile {
      display:flex; align-items:center; gap:12px;
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      padding:6px 10px; border-radius:12px; border:1px solid rgba(255,255,255,0.03);
    }
    .profile img{ width:44px; height:44px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,0.06); }
    .profile .who{ font-size:13px; color:#fff; font-weight:600; }

    /* Main layout: centered splitpane but different */
    .student-main{
      display:grid; grid-template-columns: 320px 1fr; gap:28px; padding:36px; position:relative; z-index:5;
      align-items:start; max-width:1200px; margin: 10px auto;
    }

    /* Left panel (compact) */
    .panel-left{
      background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
      border-radius:14px; padding:18px; border:1px solid rgba(255,255,255,0.03);
      box-shadow: 0 8px 30px rgba(2,6,23,0.45);
    }
    .profile-large{
      display:flex; gap:12px; align-items:center;
    }
    .profile-large img{ width:72px; height:72px; border-radius:14px; object-fit:cover; border:3px solid rgba(255,255,255,0.06) }
    .profile-large .meta{ color:#e6eef8 }
    .meta h2{ font-size:18px; margin-bottom:6px; font-weight:700 }
    .meta p{ font-size:13px; color:var(--muted) }

    .student-menu{ margin-top:18px; }
    .student-menu a{ display:block; padding:10px 12px; margin:8px 0; border-radius:10px; color:#eaf2ff; text-decoration:none; font-weight:600;
      background:linear-gradient(180deg, rgba(124,58,237,0.06), rgba(6,182,212,0.03)); border:1px solid rgba(255,255,255,0.02);
    }
    .student-menu a:hover{ transform:translateX(6px); transition:transform .18s ease; box-shadow:0 10px 30px rgba(6,182,212,0.05) }

    /* Right panel: main content */
    .panel-right{
      min-height:320px;
    }
    .welcome-card{
      background: linear-gradient(90deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
      padding:18px; border-radius:12px; border:1px solid rgba(255,255,255,0.03);
      display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px;
    }
    .welcome-card .txt h3{ margin-bottom:6px; font-size:20px; color:#fff; font-weight:700 }
    .welcome-card .txt p{ color:var(--muted) }

    /* Cards grid (unique look) */
    .grid-cards{
      display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px;
    }
    .stud-card{
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border-radius:12px; padding:18px; min-height:120px; position:relative; overflow:hidden;
      border:1px solid rgba(255,255,255,0.03);
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .stud-card:hover{ transform: translateY(-6px); box-shadow: 0 18px 40px rgba(2,6,23,0.55); }
    .stud-card .icon{
      width:46px; height:46px; border-radius:10px; display:flex; align-items:center; justify-content:center;
      font-size:20px; margin-bottom:12px; color:#fff;
      background: linear-gradient(135deg, var(--accent-1), var(--accent-2));
    }
    .stud-card h4{ color:#fff; margin-bottom:8px; font-size:16px }
    .stud-card p{ color:var(--muted); font-size:13px; line-height:1.4 }

    /* special button */
    .cta {
      margin-top:12px; display:inline-block; background:linear-gradient(90deg,var(--accent-2),var(--accent-1));
      padding:10px 14px; border-radius:10px; color:#fff; font-weight:700; text-decoration:none;
      box-shadow: 0 8px 20px rgba(124,58,237,0.12);
    }

    /* footer small */
    .small-footer{ text-align:center; color:rgba(230,238,248,0.5); margin-top:20px; font-size:13px }

    /* responsive */
    @media (max-width:980px){
      .student-main{ grid-template-columns: 1fr; padding:18px; gap:14px; }
      .panel-left{ order:2 }
      .panel-right{ order:1 }
      .topnav{ padding:12px 16px }
      .brand h1{ display:none }
    }

  </style>
</head>
<body>
  <!-- Animated decorative floaties: different from secrétaire -->
  <div class="floaties" aria-hidden="true">
    <span class="f1"></span>
    <span class="f2"></span>
    <span class="f3"></span>
  </div>

  <!-- Top navigation -->
  <header class="topnav" role="banner">
    <div class="brand">
      <div class="logo">ITAC</div>
      <div>
        <h1>ITAC — Espace Élève</h1>
        <p>Institut Technologie Appliquée des Cayes</p>
      </div>
    </div>

    <div class="top-actions">
    
      <div class="profile" title="Mon profil">
        <img src="../assets/uploads/admin.jpg" alt="Photo de profil">
        <div class="who"><?= htmlspecialchars($prenom . " " . $nom) ?></div>
      </div>
    </div>
  </header>

  <!-- Main content (different layout than secrétaire) -->
  <main class="student-main" role="main">

    <!-- Left compact panel -->
    <aside class="panel-left" aria-label="Profil et menu">
      <div class="profile-large">
        <img src="../assets/uploads/admin.jpg" alt="Photo de profil">
        <div class="meta">
          <h2><?= htmlspecialchars($prenom . ' ' . $nom) ?></h2>
          <p>Étudiant • ITAC</p>
        </div>
      </div>

      <nav class="student-menu" aria-label="Menu élève">
        <a href="dashboard.php">🏠 Tableau de bord</a>
        <a href="mes_notes.php">📚 Mes notes</a>
        <a href="mes_bulletins.php">📄 Mes bulletins</a>
        <a href="paiements.php"><i class="fas fa-file-pdf"></i> ---> Paiements</a>
        <a href="profil.php"><i class="fas fa-users-cog"></i> --->Profil</a>
        <a href="calendrier.php">📆 Calendrier</a>
      
    
        <a href="../logout.php">⤴️ Déconnexion</a>
      </nav>

      <div style="margin-top:20px;color:var(--muted);font-size:13px">
        <strong>Conseil rapide :</strong>
        <p style="margin-top:8px">Vérifie tes paiements avant les examens. Moyenne requise : 65.</p>
      </div>
    </aside>

    <!-- Right main panel -->
    <section class="panel-right">
      <div class="welcome-card" role="region" aria-label="Bienvenue">
        <div class="txt">
          <h3>Bienvenue, <?= htmlspecialchars($prenom) ?> 👋</h3>
          <p>Consulte tes notes, bulletins et le calendrier scolaire depuis cet espace.</p>
        </div>
        <div>
          <a class="cta" href="mes_notes.php">Voir mes notes</a>
        </div>
      </div>

      <div class="grid-cards" aria-live="polite">
        <article class="stud-card">
          <div class="icon">📝</div>
          <h4>Mes Notes</h4>
          <p>Consulte tes notes par session et examen, visualise ta moyenne et ton statut (passé/reprise).</p>
          <a class="cta" style="margin-top:10px;display:inline-block" href="mes_notes.php">Accéder</a>
        </article>

        <article class="stud-card">
          <div class="icon">📄</div>
          <h4>Mes Bulletins</h4>
          <p>Télécharge les bulletins officiels (PDF) fournis par le secrétariat.</p>
          <a class="cta" href="mes_bulletins.php">Télécharger</a>
        </article>

        <article class="stud-card">
          <div class="icon">📆</div>
          <h4>Calendrier</h4>
          <p>Voir l'ouverture des sessions, examens, reprises et vacances avec codes couleur clairs.</p>
          <a class="cta" href="calendrier.php">Voir le calendrier</a>
        </article>

      
      </div>

      <div class="small-footer">© <?= date('Y') ?> ITAC — Institut Technologie Appliquée des Cayes</div>
    </section>

  </main>

</body>
</html>
