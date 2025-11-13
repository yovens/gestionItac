<?php
// eleve/calendrier.php
require_once __DIR__ . '/../includes/config.php';
if(session_status() === PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user']) || ($_SESSION['user']['role_id'] ?? 0) != 4){
    header("Location: ../index.php");
    exit;
}
?>

<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Calendrier — ITAC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<style>
:root{
  --bg-1: #0f1724; --accent-1: #7c3aed; --accent-2: #06b6d4;
  --muted: #cbd5e1; --glass: rgba(255,255,255,0.06);
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:#071024;color:#e6eef8;}
.floaties{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none;}
.floaties span{position:absolute;border-radius:50%;opacity:0.12;filter:blur(18px);transform:translate3d(0,0,0);animation: floaty 12s linear infinite;}
.f1{width:260px;height:260px;left:-30px;top:10%;background:linear-gradient(135deg,var(--accent-1), rgba(124,58,237,0.4));animation-duration:18s;}
.f2{width:160px;height:160px;right:5%;top:60%;background:linear-gradient(135deg,var(--accent-2), rgba(6,182,212,0.4));animation-duration:14s;animation-delay:2s;}
.f3{width:100px;height:100px;left:30%;top:75%;background:linear-gradient(90deg,#ffffff22,#00000000);animation-duration:20s;animation-delay:3s;opacity:0.06;}
@keyframes floaty{0%,100%{transform:translateY(0) rotate(0deg);}50%{transform:translateY(-30px) rotate(45deg);}}

.dashboard-container{display:grid;grid-template-columns:320px 1fr;gap:28px;padding:36px;position:relative;z-index:5;max-width:1200px;margin:10px auto;}
.sidebar{background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;border:1px solid rgba(255,255,255,0.03);}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent-1);margin-bottom:12px;}
.sidebar a{display:block;padding:10px 12px;margin:6px 0;border-radius:10px;color:#eaf2ff;text-decoration:none;font-weight:600;background:linear-gradient(180deg, rgba(124,58,237,0.06), rgba(6,182,212,0.03));border:1px solid rgba(255,255,255,0.02);}
.sidebar a.active, .sidebar a:hover{transform:translateX(6px);transition:transform .18s ease;box-shadow:0 10px 30px rgba(6,182,212,0.05);}

.panel-right{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border-radius:12px;padding:18px;border:1px solid rgba(255,255,255,0.03);box-shadow:0 8px 30px rgba(2,6,23,0.45);}
.panel-right h2{text-align:center;margin-bottom:18px;color:#fff;}
.panel-right .info{text-align:center;margin-bottom:20px;}
.badge{display:inline-block;padding:6px 12px;margin:0 5px;border-radius:6px;color:#fff;font-size:13px;}
.badge.green{background:#27ae60;}
.badge.blue{background:#2980b9;}
.badge.pink{background:#e91e63;}
.badge.red{background:#c0392b;}

#calendar{max-width:1000px;margin:0 auto;background:#0f1724;border-radius:12px;padding:15px;box-shadow:0 8px 30px rgba(0,0,0,0.2);color:#fff;}

@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:14px;}
    .sidebar{width:100%;flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
}

.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
</style>
</head>
<body>
<div class="floaties" aria-hidden="true"><span class="f1"></span><span class="f2"></span><span class="f3"></span></div>

<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">ITAC</div>
    
    <div class="profile">
     
      <p>Étudiant • ITAC</p>
    </div>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="mes_notes.php">📚 Mes notes</a>
    <a href="mes_bulletins.php">📄 Mes bulletins</a>
    <a href="paiements.php">💰 Paiements</a>
    <a href="profil.php">⚙️ Profil</a>
    <a href="calendrier.php" class="active">📆 Calendrier</a>
    <a href="../logout.php">⤴️ Déconnexion</a>
  </aside>

  <section class="panel-right">
    <h2>Calendrier Universitaire</h2>
    <p class="info">
      <span class="badge green">Ouverture / Reprise des cours</span>
      <span class="badge blue">Examens</span>
      <span class="badge pink">Reprises</span>
      <span class="badge red">Congés / Vacances</span>
    </p>
    <div id="calendar"></div>
  </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
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
