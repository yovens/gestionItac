<?php
// eleve/bulletin_pdf.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../vendor/fpdf/fpdf.php'; // chemin vers FPDF
if(session_status()===PHP_SESSION_NONE) session_start();

if(!isset($_SESSION['user']) || ($_SESSION['user']['role_name'] ?? '') !== 'eleve'){
    header("Location: ../index.php");
    exit;
}

$etudiant_id = $_SESSION['user']['id'];
$nom = $_SESSION['user']['nom'] ?? '';
$prenom = $_SESSION['user']['prenom'] ?? '';

// Même logique de versements pour bloquer les notes
$versements_requis = [1=>1000,2=>2000,3=>1500];

$stmt = $pdo->prepare("SELECT * FROM paiements WHERE etudiant_id=? ORDER BY date_payment ASC");
$stmt->execute([$etudiant_id]);
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_verse = 0;
foreach($paiements as $p){
    $total_verse += $p['montant'];
}

function acces_note($total_verse,$versement_id,$versements_requis){
    $cumule = 0;
    for($i=1;$i<=$versement_id;$i++) $cumule += $versements_requis[$i];
    return $total_verse >= $cumule;
}

// Récupération des notes
$stmt = $pdo->prepare("
    SELECT n.*, m.nom AS matiere, e.nom AS examen, e.versement_id
    FROM notes n
    JOIN matieres m ON n.matiere_id = m.id
    JOIN examens e ON n.exam_id = e.id
    WHERE n.etudiant_id = ?
");
$stmt->execute([$etudiant_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Création PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,"Bulletin de notes de $prenom $nom",0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,10,"Matiere",1);
$pdf->Cell(60,10,"Examen",1);
$pdf->Cell(30,10,"Note",1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);
$sum=0;$count=0;
foreach($notes as $n){
    $versement_id = $n['versement_id'] ?? 1;
    $note_display = acces_note($total_verse,$versement_id,$versements_requis) ? $n['note'] : 'Bloqué';
    $pdf->Cell(60,10,$n['matiere'],1);
    $pdf->Cell(60,10,$n['examen'],1);
    $pdf->Cell(30,10,$note_display,1);
    $pdf->Ln();

    if(is_numeric($n['note']) && acces_note($total_verse,$versement_id,$versements_requis)){
        $sum += $n['note'];
        $count++;
    }
}

$moyenne = $count>0 ? $sum/$count : 0;
$pdf->Ln(5);
$pdf->Cell(0,10,"Moyenne generale: ".number_format($moyenne,2),0,1,'L');

$pdf->Output('D','bulletin_'.$nom.'_'.$prenom.'.pdf'); // Force téléchargement
