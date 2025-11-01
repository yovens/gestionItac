<?php
// secretaire/bulletins.php
session_start();
require_once "../includes/config.php"; // doit définir $pdo (PDO)
require_once "../vendor/fpdf/fpdf.php";

// Contrôle rôle
if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2){
    header("Location: ../index.php"); // <-- ici mettre le vrai chemin du login
    exit;
}


$secretaire_id = $_SESSION['user']['id'];
$nom = $_SESSION['user']['nom'];
$prenom = $_SESSION['user']['prenom'];
// photo relative dans assets (ex: 'uploads/default.png')
$photo = $_SESSION['user']['photo'] ?? 'uploads/default.png';

// notifications non lues
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND lu = 0");
$stmt->execute([$secretaire_id]);
$notifNonLues = (int)$stmt->fetchColumn();

/* ---------- Génération PDF si etudiant_id présent (doit être avant toute sortie HTML) ---------- */
if (isset($_GET['etudiant_id'])) {
    $etudiant_id = intval($_GET['etudiant_id']);

    // Récupérer l'étudiant
    $s = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role_id = 4");
    $s->execute([$etudiant_id]);
    $etudiant = $s->fetch(PDO::FETCH_ASSOC);

    if (!$etudiant) {
        header("Location: bulletins.php");
        exit;
    }

    // Récupérer les notes
    $q = $pdo->prepare("SELECT m.nom AS matiere, e.nom AS exam, n.note
                       FROM notes n
                       JOIN matieres m ON n.matiere_id = m.id
                       JOIN examens e ON n.exam_id = e.id
                       WHERE n.etudiant_id = ?");
    $q->execute([$etudiant_id]);
    $notes = $q->fetchAll(PDO::FETCH_ASSOC);

    // Générer PDF
    $pdf = new FPDF('P','mm','A4');
    $pdf->AddPage();
    $logoPath = __DIR__ . '/../assets/uploads/logo_itac.png';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 10, 8, 30);
    }
    $pdf->SetFont("Arial","B",14);
    $pdf->Cell(0,10,"Institut Technologie Appliquee des Cages",0,1,"C");
    $pdf->SetFont("Arial","",11);
    $pdf->Cell(0,6,"Bulletin Officiel - ".date("Y"),0,1,"C");
    $pdf->Ln(6);

    $pdf->SetFont("Arial","",11);
    $pdf->Cell(0,7,"Nom : ".$etudiant['nom']." ".$etudiant['prenom'],0,1);
    $pdf->Cell(0,7,"Tel : ".($etudiant['phone'] ?? ''),0,1);
    $pdf->Cell(0,7,"Email : ".($etudiant['email'] ?? ''),0,1);
    $pdf->Ln(4);

    $pdf->SetFont("Arial","B",11);
    $pdf->Cell(80,9,"Matiere",1,0,"C");
    $pdf->Cell(60,9,"Examen",1,0,"C");
    $pdf->Cell(30,9,"Note",1,1,"C");

    $pdf->SetFont("Arial","",11);
    $sum = 0; $count = 0;
    if (!empty($notes)) {
        foreach ($notes as $row) {
            $pdf->Cell(80,8,utf8_decode($row['matiere']),1);
            $pdf->Cell(60,8,utf8_decode($row['exam']),1);
            $pdf->Cell(30,8,(string)$row['note'],1,1,"C");
            $sum += floatval($row['note']);
            $count++;
        }
    }

    if ($count > 0) {
        $moy = round($sum / $count, 2);
        $pdf->SetFont("Arial","B",11);
        $pdf->Cell(140,9,"Moyenne Generale",1);
        $pdf->Cell(30,9,(string)$moy,1,1,"C");
        $pdf->Ln(6);

        if ($moy < 65) {
            $pdf->SetTextColor(255,0,0);
            $pdf->SetFont("Arial","B",12);
            $pdf->Cell(0,8,"Decision : En reprise",0,1,"L");
        } else {
            $pdf->SetTextColor(0,120,0);
            $pdf->SetFont("Arial","B",12);
            $pdf->Cell(0,8,"Decision : Passe a la classe superieure",0,1,"L");
        }
    } else {
        $pdf->Cell(0,8,"Aucune note enregistree.",0,1);
    }

    $filename = "bulletin_{$etudiant['nom']}_{$etudiant['prenom']}.pdf";
    $pdf->Output('I', $filename);
    exit;
}

/* ---------- Page HTML (affichage) ---------- */
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Secrétaire — Bulletins | ITAC</title>
  <link rel="stylesheet" href="../assets/css/secretaire.css"> <!-- <== important -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar" aria-label="navigation">
      <div class="logo">ITAC</div>
      <nav>
        <ul>
          <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li><a href="inscriptions.php"><i class="fas fa-book"></i> Inscriptions</a></li>
          <li><a href="examens.php"><i class="fas fa-book"></i> Examens</a></li>
          <li><a href="notes.php"><i class="fas fa-book-open"></i> Notes</a></li>
          <li><a href="bulletins.php" class="active"><i class="fas fa-file-pdf"></i> Bulletins</a></li>
          <li><a href="paiements.php"><i class="fas fa-file-invoice-dollar"></i> Paiements</a></li>
          <li><a href="calendrier.php"><i class="fas fa-calendar-alt"></i> Calendrier</a></li>
          <a href="releve_notes.php"><i class="fas fa-calendar-alt"></i> Releve_notes</a>
           <a href="profil.php"><i class="fas fa-calendar-alt"></i> Profil</a>
          <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
      </nav>
    </aside>

    <!-- MAIN -->
    <main class="main-content">


      <section class="cards-container">
        <div class="card animate-card" style="width:100%;">
          <h2>📄 Générer le bulletin d'un étudiant (PDF)</h2>

          <form method="get" class="form-card" style="margin-top:10px">
            <div class="form-group">
              <label for="etudiant_id">Choisir un étudiant :</label>
              <select id="etudiant_id" name="etudiant_id" required>
                <option value="">-- Sélectionner --</option>
                <?php
                  $s = $pdo->query("SELECT id, nom, prenom FROM users WHERE role_id = 4 ORDER BY nom, prenom");
                  while ($r = $s->fetch(PDO::FETCH_ASSOC)) {
                      echo '<option value="'.(int)$r['id'].'">'.htmlspecialchars($r['nom'].' '.$r['prenom']).'</option>';
                  }
                ?>
              </select>
            </div>
            <button type="submit" class="btn-save">Générer Bulletin PDF</button>
          </form>

          <hr style="margin:18px 0;border:none;border-top:1px solid #eee">

          <h3>Liste rapide des étudiants</h3>
          <div style="overflow:auto;max-height:320px;margin-top:10px">
            <table class="styled-table">
              <thead>
                <tr><th>Nom</th><th>Prénom</th><th>Téléphone</th><th>Email</th></tr>
              </thead>
              <tbody>
                <?php
                $s2 = $pdo->query("SELECT nom, prenom, phone, email FROM users WHERE role_id = 4 ORDER BY nom, prenom LIMIT 500");
                while ($row = $s2->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars($row['nom']).'</td>';
                    echo '<td>'.htmlspecialchars($row['prenom']).'</td>';
                    echo '<td class="small-muted">'.htmlspecialchars($row['phone'] ?? '').'</td>';
                    echo '<td class="small-muted">'.htmlspecialchars($row['email'] ?? '').'</td>';
                    echo '</tr>';
                }
                ?>
              </tbody>
            </table>
          </div>

        </div>
      </section>

    </main>
  </div>
</body>
</html>





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































 
</style>
