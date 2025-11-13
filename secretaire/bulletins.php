<?php
session_start();
require_once "../includes/config.php";
require_once "../vendor/fpdf/fpdf.php";

// Vérification rôle
if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2){
    header("Location: ../index.php");
    exit;
}


$secretaire_id = $_SESSION['user']['id'];
$nom = $_SESSION['user']['nom'];
$prenom = $_SESSION['user']['prenom'];
$photo = $_SESSION['user']['photo'] ?? "default.png";

// Génération PDF
if (isset($_GET['etudiant_id'])) {
    $etudiant_id = intval($_GET['etudiant_id']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role_id = 4");
    $stmt->execute([$etudiant_id]);
    $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$etudiant) {
        header("Location: bulletins.php");
        exit;
    }

    $q = $pdo->prepare("SELECT m.nom AS matiere, e.nom AS exam, n.note
                       FROM notes n
                       JOIN matieres m ON n.matiere_id = m.id
                       JOIN examens e ON n.exam_id = e.id
                       WHERE n.etudiant_id = ?");
    $q->execute([$etudiant_id]);
    $notes = $q->fetchAll(PDO::FETCH_ASSOC);

    $pdf = new FPDF('P','mm','A4');
    $pdf->AddPage();
    $logoPath = __DIR__ . '/../assets/uploads/logo_itac.png';
    if(file_exists($logoPath)) $pdf->Image($logoPath,10,8,30);

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
    $sum=0;$count=0;
    foreach($notes as $row){
        $pdf->Cell(80,8,utf8_decode($row['matiere']),1);
        $pdf->Cell(60,8,utf8_decode($row['exam']),1);
        $pdf->Cell(30,8,(string)$row['note'],1,1,"C");
        $sum += floatval($row['note']);
        $count++;
    }

    if($count>0){
        $moy = round($sum/$count,2);
        $pdf->SetFont("Arial","B",11);
        $pdf->Cell(140,9,"Moyenne Generale",1);
        $pdf->Cell(30,9,(string)$moy,1,1,"C");
        $pdf->Ln(6);
        $pdf->SetTextColor($moy<65?255:0,$moy<65?0:120,0);
        $pdf->SetFont("Arial","B",12);
        $pdf->Cell(0,8,"Decision : ".($moy<65?"En reprise":"Passe a la classe superieure"),0,1,"L");
    }else{
        $pdf->Cell(0,8,"Aucune note enregistree.",0,1);
    }

    $pdf->Output('I',"bulletin_{$etudiant['nom']}_{$etudiant['prenom']}.pdf");
    exit;
}

// Liste étudiants
$students = $pdo->query("SELECT id, nom, prenom, phone, email FROM users WHERE role_id = 4 ORDER BY nom, prenom LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Secrétaire — Bulletins | ITAC</title>
<link rel="stylesheet" href="../assets/css/secretaire.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{
  --bg-main:#fef6e4; --accent:#ef4444; --accent-dark:#b91c1c;
  --card-bg:#fff5f5; --card-hover:#fee2e2; --text-light:#1f2937;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:var(--bg-main);color:var(--text-light);}
.dashboard-container{display:grid;grid-template-columns:280px 1fr;gap:28px;padding:32px;max-width:1300px;margin:0 auto;}
.sidebar{background:rgba(239,68,68,0.85);border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px;}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
.sidebar a{display:block;padding:12px 14px;margin:6px 0;border-radius:10px;color:#fff;text-decoration:none;font-weight:600;transition:.3s;background:rgba(255,255,255,0.05);}
.sidebar a.active, .sidebar a:hover{background:#fff;color:var(--accent-dark);font-weight:700;transform:translateX(4px);}
.main-content{padding:28px;display:flex;flex-direction:column;gap:20px;}
.cards-container{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;}
.card{background:var(--card-bg);padding:22px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.15);transition:.3s;}
.card:hover{background:var(--card-hover);transform:translateY(-6px);box-shadow:0 12px 30px rgba(0,0,0,0.25);}
.form-card .form-group{display:flex;flex-direction:column;gap:6px;margin-bottom:12px;}
.form-card select, .form-card input{padding:10px;border-radius:8px;border:1px solid #ccc;outline:none;}
.form-card select:focus, .form-card input:focus{border-color:#3b82f6;box-shadow:0 0 6px rgba(59,130,246,0.4);}
.btn-save{padding:12px;border:none;border-radius:8px;background:#3b82f6;color:white;font-weight:bold;cursor:pointer;transition:.3s;}
.btn-save:hover{background:#2563eb;}
.styled-table{width:100%;border-collapse:collapse;margin-top:15px;}
.styled-table thead tr{background:#3b82f6;color:#fff;}
.styled-table th,.styled-table td{padding:12px 15px;border-bottom:1px solid #ddd;}
.styled-table tbody tr:hover{background:#e0f2fe;}
.animate-card{opacity:0;animation:fadeInUp 1s ease forwards;}
@keyframes fadeInUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
.sidebar .logo{text-align:center;font-size:28px;font-weight:700;color:var(--accent);margin-bottom:14px;}
.sidebar .profile{text-align:center;margin-bottom:18px;}
.sidebar .profile img{width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:8px;border:2px solid var(--accent);}
.sidebar .profile h3{font-size:18px;font-weight:600;}
/* Responsive */
@media(max-width:900px){
  .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
  .sidebar{flex-direction:row;overflow-x:auto;padding:10px;}
  .sidebar .profile{display:none;}
  .sidebar a{margin:0 8px;white-space:nowrap;}
  .cards-container{grid-template-columns:1fr;}
  .styled-table thead{display:none;}
  .styled-table, .styled-table tbody, .styled-table tr, .styled-table td{display:block;width:100%;}
  .styled-table tr{margin-bottom:12px;border-bottom:2px solid #eef2f6;padding-bottom:8px;}
  .styled-table td{padding-left:50%;text-align:right;position:relative;}
  .styled-table td::before{content:attr(data-label);position:absolute;left:12px;width:45%;text-align:left;font-weight:700;color:#6b7280;}
}
</style>
</head>
<body>
<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo" style="color: #fff;">ITAC</div>
     <div class="profile">
      <img src="../uploads/<?=$photo?>" alt="photo">
      <h3><?=$prenom." ".$nom?></h3>
      <p>Secretaire</p>
    </div>
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
    <section class="cards-container">
      <div class="card animate-card">
        <h2>📄 Générer le bulletin d'un étudiant (PDF)</h2>
        <form method="get" class="form-card">
          <div class="form-group">
            <label for="etudiant_id">Choisir un étudiant :</label>
            <select id="etudiant_id" name="etudiant_id" required>
              <option value="">-- Sélectionner --</option>
              <?php foreach($students as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom'].' '.$s['prenom']) ?></option>
              <?php endforeach; ?>
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
              <?php foreach($students as $row): ?>
              <tr>
                <td data-label="Nom"><?= htmlspecialchars($row['nom']) ?></td>
                <td data-label="Prénom"><?= htmlspecialchars($row['prenom']) ?></td>
                <td data-label="Téléphone"><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                <td data-label="Email"><?= htmlspecialchars($row['email'] ?? '') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>
</div>
</body>
</html>
