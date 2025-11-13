<?php 
session_start();
require_once "../includes/config.php";

// Vérification session & rôle (secrétaire = 2)
if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2){
    header("Location: ../index.php");
    exit;
}

$nom    = $_SESSION['user']['nom'];
$prenom = $_SESSION['user']['prenom'];
$photo  = $_SESSION['user']['photo'] ?? "default.png";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Secrétaire — Calendrier | ITAC</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">

<style>
:root{
  --bg-main:#fef6e4; --accent:#ef4444; --accent-dark:#b91c1c;
  --card-bg:#fff5f5; --card-hover:#fee2e2; --text:#1f2937; --muted:#6b7280; --blue:#3b82f6; --shadow:0 6px 20px rgba(0,0,0,0.08);
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:var(--bg-main);color:var(--text);-webkit-font-smoothing:antialiased;}
a{color:var(--blue);text-decoration:none;}

/* Layout */
.dashboard-container{
  display:grid;
  grid-template-columns:260px 1fr;
  gap:28px;
  max-width:1300px;
  margin:28px auto;
  padding:0 20px 40px;
}

/* SIDEBAR */
.sidebar{
  background:rgba(239,68,68,0.95);
  border-radius:12px;
  padding:18px;
  color:#fff;
  display:flex;
  flex-direction:column;
  gap:8px;
  border:1px solid rgba(255,255,255,0.04);
}
.sidebar .logo{font-weight:700;font-size:24px;text-align:center;color:#fff;margin-bottom:8px}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
.sidebar nav ul{list-style:none;padding:0;}
.sidebar nav li{margin:6px 0;}
.sidebar nav a{
  display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:#fff;font-weight:600;background:rgba(255,255,255,0.03);
}
.sidebar nav a.active, .sidebar nav a:hover{
  background:#fff;color:var(--accent-dark);transform:translateX(6px);
}

/* MAIN */
.main-content{padding:18px 0;min-height:60vh;}
.cards-container{display:grid;grid-template-columns:1fr;gap:16px;}
.card{background:#fff;padding:18px;border-radius:10px;box-shadow:var(--shadow);}
.card h2{text-align:center;margin-bottom:12px;color:var(--accent-dark);}
.info{text-align:center;margin-bottom:16px;}
.badge{display:inline-block;padding:6px 12px;margin:0 5px;border-radius:6px;color:#fff;font-size:13px;}
.badge.green{background:#27ae60;}
.badge.blue{background:#2980b9;}
.badge.pink{background:#e91e63;}
.badge.red{background:#c0392b;}

#calendar{
    max-width:100%;
    margin:0 auto;
    background:#fff;
    border-radius:8px;
    padding:15px;
    box-shadow:0 3px 10px rgba(0,0,0,0.1);
}

/* Responsive */
@media(max-width:900px){
  .dashboard-container{grid-template-columns:1fr;padding:16px;gap:14px;}
  .sidebar{flex-direction:row;overflow-x:auto;padding:10px;}
  .sidebar .profile{display:none;}
  .sidebar nav ul{display:flex;gap:8px;}
  .sidebar nav li{margin:0;}
}
</style>
</head>
<body>

<div class="dashboard-container">
  <!-- Sidebar -->
  <aside class="sidebar">
       <div class="logo" style="color: #fff;">ITAC</div>
     <div class="profile">
        <img src="../uploads/<?=$photo?>" alt="photo">
        <h3><?=$prenom." ".$nom?></h3>
        <p>Secrétaire</p>
     </div>
     <nav>
       <ul>
         <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
         <li><a href="inscriptions.php"><i class="fas fa-user-plus"></i> Inscriptions</a></li>
         <li><a href="examens.php"><i class="fas fa-book"></i> Examens</a></li>
         <li><a href="notes.php"><i class="fas fa-book-open"></i> Notes</a></li>
         <li><a href="bulletins.php"><i class="fas fa-file-pdf"></i> Bulletins</a></li>
         <li><a href="paiements.php"><i class="fas fa-file-invoice-dollar"></i> Paiements</a></li>
         <li><a href="calendrier.php" class="active"><i class="fas fa-calendar-alt"></i> Calendrier</a></li>
         <li><a href="releve_notes.php"><i class="fas fa-calendar-alt"></i> Relevé Notes</a></li>
         <li><a href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
         <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
       </ul>
     </nav>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
      <section class="cards-container">
          <div class="card animate-card">
              <h2>📆 Calendrier Universitaire</h2>
              <p class="info">
                  <span class="badge green">Ouverture / Reprise des cours</span>
                  <span class="badge blue">Examens</span>
                  <span class="badge pink">Reprises</span>
                  <span class="badge red">Congés / Vacances</span>
              </p>
              <div id="calendar"></div>
          </div>
      </section>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: [
            { title: 'Ouverture 1ère session', start: '2025-10-27', color: 'green' },
            { title: 'Congé Toussaint', start: '2025-11-01', color: 'red' },
            { title: 'Congé', start: '2025-11-18', color: 'red' },
            { title: 'Examens Intra 1ère session', start: '2025-12-15', end: '2025-12-19', color: 'blue' },
            { title: 'Reprise des cours', start: '2026-01-05', color: 'green' },
            { title: 'Examens Finaux 1ère session', start: '2026-02-16', end: '2026-02-20', color: 'blue' },
            { title: 'Examens de Reprise', start: '2026-03-09', end: '2026-03-14', color: 'pink' },
            { title: 'Ouverture 2ème session', start: '2026-03-23', color: 'green' },
            { title: 'Congé Avril', start: '2026-04-13', end: '2026-04-17', color: 'red' },
            { title: 'Examens Intra 2ème session', start: '2026-05-11', end: '2026-05-15', color: 'blue' },
            { title: 'Examens Finaux 2ème session', start: '2026-07-05', end: '2026-07-10', color: 'blue' },
            { title: 'Examens de Reprise', start: '2026-07-27', end: '2026-07-30', color: 'pink' },
            { title: 'Vacances', start: '2026-07-31', end: '2026-10-26', color: 'red' }
        ]
    });
    calendar.render();
});
</script>
</body>
</html>
