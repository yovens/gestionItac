<?php
session_start();
require_once "../includes/config.php";
require_once "../vendor/fpdf/fpdf.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role_name'] !== 'secretaire'){
    header("Location: ../index.php");
    exit;
}

if(!isset($_GET['id'])){
    die("Élève non spécifié.");
}

$eleve_id = intval($_GET['id']);

// Récupérer infos de l’élève
$stmt = $pdo->prepare("SELECT u.nom, u.prenom, c.nom as classe 
                       FROM users u
                       JOIN etudiants_classes ec ON u.id=ec.etudiant_id
                       JOIN classes c ON ec.classe_id=c.id
                       WHERE u.id=? LIMIT 1");
$stmt->execute([$eleve_id]);
$eleve = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$eleve) die("Élève introuvable.");

// Récupérer ses notes
$stmt = $pdo->prepare("SELECT m.nom as matiere, n.note 
                       FROM notes n
                       JOIN matieres m ON n.matiere_id=m.id
                       WHERE n.etudiant_id=?");
$stmt->execute([$eleve_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer moyenne
$total=0; $count=0;
foreach($notes as $n){ $total+=$n['note']; $count++; }
$moyenne = $count>0 ? round($total/$count,2) : 0;
$eligible = $moyenne>=60 ? "Oui" : "Non";

// Générer le PDF
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$logoPath = __DIR__ . '/../assets/uploads/logo_itac.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 10, 8, 30);
}


$pdf->SetFont('Arial','B',16);
$pdf->Cell(190,10,'Releve de Notes - ITAC',0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','',12);
$pdf->Cell(190,10,"Nom : ".$eleve['prenom']." ".$eleve['nom'],0,1);
$pdf->Cell(190,10,"Classe : ".$eleve['classe'],0,1);
$pdf->Ln(5);

// Tableau des notes
$pdf->SetFont('Arial','B',12);
$pdf->Cell(120,10,'Matiere',1,0,'C');
$pdf->Cell(70,10,'Note',1,1,'C');

$pdf->SetFont('Arial','',12);
foreach($notes as $n){
    $pdf->Cell(120,10,utf8_decode($n['matiere']),1,0);
    $pdf->Cell(70,10,$n['note'],1,1,'C');
}

// Moyenne & eligibilité
$pdf->SetFont('Arial','B',12);
$pdf->Cell(120,10,'Moyenne Generale',1,0);
$pdf->Cell(70,10,$moyenne."/100",1,1,'C');
$pdf->Cell(120,10,'Eligibilite fin etudes',1,0);
$pdf->Cell(70,10,$eligible,1,1,'C');

$pdf->Output("I","Releve_".$eleve['nom'].".pdf");
