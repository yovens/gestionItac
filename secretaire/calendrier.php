<?php
session_start();
require_once "../includes/config.php";

// Vérification session & rôle (secrétaire = 2)
if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2){
    header("Location: ../index.php"); // <-- ici mettre le vrai chemin du login
    exit;
}

$nom    = $_SESSION['user']['nom'];
$prenom = $_SESSION['user']['prenom'];
$photo  = $_SESSION['user']['photo'] ?? "default.png";
?>


<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">ITAC</div>
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="inscriptions.php"><i class="fas fa-user-plus"></i> Inscriptions</a></li>
                <li><a href="examens.php"><i class="fas fa-book"></i> Examens</a></li>
                <li><a href="notes.php"><i class="fas fa-book-open"></i> Notes</a></li>
                <li><a href="bulletins.php"><i class="fas fa-file-pdf"></i> Bulletins</a></li>
                <li><a href="paiements.php"><i class="fas fa-file-invoice-dollar"></i> Paiements</a></li>
                <li><a href="calendrier.php" class="active"><i class="fas fa-calendar-alt"></i> Calendrier</a></li>
                <a href="releve_notes.php"><i class="fas fa-calendar-alt"></i> Releve_notes</a>
                <a href="profil.php"><i class="fas fa-calendar-alt"></i> Profil</a>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
    

        <!-- Calendar Section -->
        <section class="cards-container">
            <div class="card animate-card" style="width:100%;">
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

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
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



    

<style>



:root{
  --bg-main:#fef6e4;
  --accent:#ef4444;
  --accent-dark:#b91c1c;
  --card-bg:#fff5f5;
  --card-hover:#fee2e2;
  --muted:#6b7280;
  --blue:#3b82f6;
  --surface:#ffffff;
  --shadow:0 6px 20px rgba(0,0,0,0.08);
  --text:#1f2937;
}

*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:var(--bg-main);color:var(--text);-webkit-font-smoothing:antialiased}
a{color:var(--blue);text-decoration:none}

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
.sidebar nav ul{list-style:none}
.sidebar nav li{margin:8px 0}
.sidebar nav a{
  display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:#fff;font-weight:600;background:rgba(255,255,255,0.03)
}
.sidebar nav a.active,.sidebar nav a:hover{
  background:#fff;color:var(--accent-dark);transform:translateX(6px)
}
.badge{background:#e74c3c;color:#fff;padding:3px 8px;border-radius:999px;font-size:12px;margin-left:auto}

/* MAIN */
.main-content{
  background:transparent;padding:18px 0;min-height:60vh;
}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
.welcome{display:flex;align-items:center;gap:12px}
.avatar{width:56px;height:56px;border-radius:50%;border:3px solid var(--blue);object-fit:cover}
.welcome-text{font-size:16px;color:var(--text)}
.role-text{font-size:13px;color:var(--muted)}

/* Panel */
.panel{
  background:var(--surface);
  padding:20px;
  border-radius:12px;
  box-shadow:var(--shadow);
  margin-bottom:18px;
}

/* Forms */
.form-card, .form-box {
  max-width:760px;
  width:100%;
  display:grid;
  gap:12px;
}
.form-group{display:flex;flex-direction:column;gap:6px}
label{font-weight:600;color:var(--text)}
input[type="text"], input[type="number"], input[type="date"], input[type="file"], select{
  padding:10px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;outline:none;font-size:14px
}
input:focus, select:focus{box-shadow:0 0 8px rgba(59,130,246,0.12);border-color:var(--blue)}

.btn-save, .btn-submit{
  display:inline-block;padding:12px;border-radius:10px;border:none;background:var(--blue);color:#fff;font-weight:700;cursor:pointer;
}
.btn-save:hover, .btn-submit:hover{background:#2563eb}

/* Messages */
.msg-success{background:#d1fae5;color:#065f46;padding:12px;border-radius:8px;font-weight:700;text-align:center}
.msg-error{background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;font-weight:700;text-align:center}

/* Cards container (re-usable) */
.cards-container{display:grid;grid-template-columns:1fr;gap:16px}
.card{background:var(--surface);padding:18px;border-radius:10px;box-shadow:var(--shadow)}

/* Table styled */
.styled-table{width:100%;border-collapse:collapse;font-size:14px;margin-top:8px}
.styled-table thead tr{background:var(--blue);color:#fff;font-weight:700}
.styled-table th, .styled-table td{padding:12px 14px;border-bottom:1px solid #eef2f6;text-align:left}
.styled-table tbody tr:nth-child(even){background:#fbfbfb}
.styled-table tbody tr:hover{background:#e8f6ff}
.styled-table a{color:var(--blue);font-weight:600}

/* Responsive table for small screens */
@media (max-width:780px){
  .dashboard-container{grid-template-columns:1fr;padding:16px;gap:14px}
  .sidebar{width:100%;display:flex;overflow-x:auto;gap:8px}
  .sidebar nav ul{display:flex;gap:8px}
  .sidebar nav li{margin:0}
  /* table -> card rows */
  .styled-table thead{display:none}
  .styled-table, .styled-table tbody, .styled-table tr, .styled-table td{display:block;width:100%}
  .styled-table tr{margin-bottom:12px;border-bottom:2px solid #eef2f6;padding-bottom:8px}
  .styled-table td{padding-left:50%;text-align:right;position:relative}
  .styled-table td::before{content:attr(data-label);position:absolute;left:12px;width:45%;text-align:left;font-weight:700;color:var(--muted)}
}

/* small helpers */
.small-muted{font-size:13px;color:var(--muted)}
















.calendar-page {
    padding: 20px;
}

.calendar-page h2 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 20px;
}

.calendar-page .info {
    text-align: center;
    margin-bottom: 20px;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    margin: 0 5px;
    border-radius: 6px;
    color: #fff;
    font-size: 13px;
}

.badge.green { background: #27ae60; }
.badge.blue { background: #2980b9; }
.badge.pink { background: #e91e63; }
.badge.red { background: #c0392b; }

#calendar {
    max-width: 1000px;
    margin: 0 auto;
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f2f5;
    color: #333;
}

</style>
