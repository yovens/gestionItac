<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$db   = "itac_db";
$user = "root";
$pass = "dions2003";

try {
    // PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Si tu veux aussi un alias $conn pour compatibilité
    $conn = $pdo;

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
