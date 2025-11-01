
<?php
session_start();
require_once "../includes/config.php";

if(!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 2){
    header("Location: ../index.php"); // <-- ici mettre le vrai chemin du login
    exit;
}


$secretaire_id = $_SESSION['user']['id'];
$nom = $_SESSION['user']['nom'];
$prenom = $_SESSION['user']['prenom'];
$photo = $_SESSION['user']['photo'] ?? "default.png";

// Notifications non lues
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id=? AND lu=0");
$stmt->execute([$secretaire_id]);
$notifNonLues = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Ajout étudiant
if(isset($_POST['submit'])){
    $nomE = $_POST['nom'];
    $prenomE = $_POST['prenom'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role_id = 4; // élève

    $photoPath = "uploads/default.png";
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
        $photoPath = "uploads/".time()."_".$_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "../assets/".$photoPath);
    }

    $stmt = $pdo->prepare("INSERT INTO users(role_id, nom, prenom, phone, email, username, password, photo) 
                           VALUES(?,?,?,?,?,?,?,?)");
    $stmt->execute([$role_id, $nomE, $prenomE, $phone, $email, $username, $password, $photoPath]);

    $etudiant_id = $pdo->lastInsertId();

    $classe_id = $_POST['classe_id'];
    $annee_academique = "2025-2026";
    $stmt2 = $pdo->prepare("INSERT INTO etudiants_classes(etudiant_id, classe_id, annee_academique) VALUES(?,?,?)");
    $stmt2->execute([$etudiant_id, $classe_id, $annee_academique]);

    $success = "✅ Étudiant inscrit et affecté à la classe avec succès !";
}

// Liste classes
$classes = $pdo->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);

// Voir étudiants par classe
$listeParClasse = [];
if(isset($_GET['classe_id'])){
    $cid = $_GET['classe_id'];
    $stmt = $pdo->prepare("SELECT u.nom,u.prenom,u.email FROM users u 
                           JOIN etudiants_classes ec ON u.id=ec.etudiant_id 
                           WHERE ec.classe_id=?");
    $stmt->execute([$cid]);
    $listeParClasse = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>





<div class="dashboard-container">
  <aside class="sidebar">
    <div class="logo">ITAC</div>
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
  <header class="topbar">


  <section class="cards-container">
            <!-- Formulaire inscription -->
            <div class="card animate-card">
                <h3>Inscrire un étudiant</h3>
                <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
                <form method="POST" enctype="multipart/form-data" class="form-box">
                    <input type="text" name="nom" placeholder="Nom" required>
                    <input type="text" name="prenom" placeholder="Prénom" required>
                    <input type="text" name="phone" placeholder="Téléphone" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                    <input type="text" name="password" placeholder="Mot de passe" required>
                    <input type="file" name="photo" accept="image/*" required>
                    <select name="classe_id" required>
                        <option value="">Sélectionner une classe</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['nom'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="submit" class="btn-submit">Inscrire</button>
                </form>
            </div>

            <!-- Liste étudiants par classe -->
            <div class="card animate-card">
                <h3>Étudiants par classe</h3>
                <form method="GET" class="form-box">
                    <select name="classe_id" onchange="this.form.submit()">
                        <option value="">Choisir une classe</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (isset($_GET['classe_id']) && $_GET['classe_id']==$c['id'])?'selected':'' ?>>
                                <?= $c['nom'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <?php if(!empty($listeParClasse)): ?>
                    <table class="styled-table">
                        <thead>
                            <tr><th>Nom</th><th>Prénom</th><th>Email</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach($listeParClasse as $et): ?>
                            <tr>
                                <td><?= $et['nom'] ?></td>
                                <td><?= $et['prenom'] ?></td>
                                <td><?= $et['email'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif(isset($_GET['classe_id'])): ?>
                    <p>Aucun étudiant inscrit dans cette classe.</p>
                <?php endif; ?>
            </div>
        </section>











<style>
:root{
  --bg-main:#fef6e4;
  --accent:#ef4444;
  --accent-dark:#b91c1c;
  --card-bg:#fff5f5;
  --card-hover:#fee2e2;
  --text-light:#1f2937;
}

*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:'Inter',sans-serif;
    background:var(--bg-main);
    color:var(--text-light);
    overflow-x:hidden;
}

/* DASHBOARD GRID */
.dashboard-container{
    display:grid;
    grid-template-columns:280px 1fr;
    gap:28px;
    padding:32px;
    max-width:1300px;
    margin:0 auto;
}

/* Sidebar */
.sidebar{
    background:rgba(239,68,68,0.85);
    border-radius:14px;
    padding:18px;
    display:flex;
    flex-direction:column;
    gap:12px;
}
.sidebar .logo{
    text-align:center;
    font-size:28px;
    font-weight:700;
    color:#fff;
    margin-bottom:14px;
}
.sidebar a{
    display:block;
    padding:12px 14px;
    margin:6px 0;
    border-radius:10px;
    color:#fff;
    text-decoration:none;
    font-weight:600;
    transition:.3s;
    background:rgba(255,255,255,0.05);
}
.sidebar a.active, .sidebar a:hover{
    background:#fff;
    color:var(--accent-dark);
    font-weight:700;
    transform:translateX(4px);
}

/* Main content */
.main-content{
    padding:28px;
    display:flex;
    flex-direction:column;
    gap:20px;
}
.topbar{
    display:flex;
    align-items:center;
    gap:20px;
}
.avatar{
    width:60px;
    height:60px;
    border-radius:50%;
    border:3px solid var(--accent);
}

/* Cards */
.cards-container{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
}
.card{
    background:var(--card-bg);
    padding:22px;
    border-radius:12px;
    box-shadow:0 6px 20px rgba(0,0,0,0.15);
    transition:.3s;
}
.card:hover{
    background:var(--card-hover);
    transform:translateY(-6px);
    box-shadow:0 12px 30px rgba(0,0,0,0.25);
}

/* Formulaire et tableau */
.form-box input, .form-box select{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:8px;
    outline:none;
    background:#f9fafb;
    transition:.3s;
}
.form-box input:focus, .form-box select:focus{
    border-color:#3b82f6;
    box-shadow:0 0 6px rgba(59,130,246,0.4);
}
.btn-submit{
    width:100%;
    padding:14px;
    border:none;
    border-radius:8px;
    background:#3b82f6;
    color:white;
    font-size:16px;
    font-weight:bold;
    cursor:pointer;
    transition:.3s;
}
.btn-submit:hover{
    background:#2563eb;
}
.success{
    background:#d1fae5;
    color:#065f46;
    padding:10px;
    border-radius:8px;
    text-align:center;
    margin-bottom:15px;
    font-weight:bold;
}

/* Tableau styled */
.styled-table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}
.styled-table thead tr{
    background:#3b82f6;
    color:#fff;
}
.styled-table th, .styled-table td{
    padding:12px 15px;
    border-bottom:1px solid #ddd;
}
.styled-table tbody tr:hover{
    background:#e0f2fe;
}

/* Animation */
.animate-card{
    opacity:0;
    animation:fadeInUp 1s ease forwards;
}
@keyframes fadeInUp{
    from{opacity:0; transform:translateY(30px);}
    to{opacity:1; transform:translateY(0);}
}

/* Responsive */
@media(max-width:900px){
    .dashboard-container{grid-template-columns:1fr;padding:18px;gap:18px;}
    .sidebar{flex-direction:row;overflow-x:auto;}
    .sidebar a{margin:0 8px;}
    .cards-container{grid-template-columns:1fr;}
}


/* Dashboard liens rapides */
.secretaire-dashboard{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:20px;
}
.secretaire-dashboard .card{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    padding:24px;
    background:var(--card-bg);
    color:var(--text-light);
    border-radius:12px;
    font-weight:600;
    font-size:16px;
    text-align:center;
    text-decoration:none;
    transition:.3s;
    box-shadow:0 5px 18px rgba(0,0,0,0.15);
}
.secretaire-dashboard .card i{
    font-size:40px;
    margin-bottom:12px;
    color:var(--accent);
}






</style>
