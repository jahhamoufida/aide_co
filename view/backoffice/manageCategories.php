<?php


session_start();
require_once __DIR__ . '/../../controller/CategorieC.php';
// ----------------------------
// 1) Optional: vérification d'une 2FA globale (ancienne logique)
// ----------------------------
if (!isset($_SESSION['2fa_validated']) || $_SESSION['2fa_validated'] !== true) {
    
}

// ----------------------------
// 2) Autoload et initialisation RobThree 2FA
// ----------------------------
require_once '../../vendor/autoload.php';
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\Providers\Qr\QRServerProvider;

// Création du du secret + QR provider
$qrProvider = new QRServerProvider();
$tfa = new TwoFactorAuth($qrProvider, 'Supportini.TN');
if (!isset($_SESSION['twofa_secret'])) {// Générer secret si inexistant
    $_SESSION['twofa_secret'] = $tfa->createSecret(160); 
}
$secret = $_SESSION['twofa_secret'];
$qrCodeUrl = $tfa->getQRCodeImageAsDataUri('Supportini.Admin', $secret);


// Traitement AJAX : vérification code 2F
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify2fa') {
    $code = trim($_POST['code'] ?? '');
    if ($tfa->verifyCode($secret, $code)) {
        $_SESSION['last_2fa_ok'] = time();
        echo "OK";
    } else {
        echo "NO";
    }
    exit;
}


// IMPORTS + CONTROLLER
require_once '../../controller/CategorieC.php';
require_once '../../model/Categorie.php';

$cc = new CategorieC();
$liste = $cc->listCategories();

// ----------------------------
// STATISTIQUES
// ----------------------------
$totalCategories = count($liste);
$totalPlaces = array_sum(array_column($liste, 'nb_evenements'));
$totalInscrits = 0;


// UPLOAD DIRECTORY

$upload_dir = __DIR__ . '/../../uploads/';


// MODE MODIFICATION

$categorieToEdit = null;
if (isset($_GET['edit'])) {
    $categorieToEdit = $cc->getCategorie($_GET['edit']);
}


// TRAITEMENT ADD / UPDATE

$errors = [];

if (isset($_POST['add']) || isset($_POST['update'])) {
    $nom = trim($_POST['nom_categorie'] ?? '');
    $desc = trim($_POST['description_categorie'] ?? '');
    $etat = $_POST['etat'] ?? 'active';

    $nb_evenements = isset($_POST['nb_evenements']) ? max(0, (int) $_POST['nb_evenements']) : 0;

    $date_creation = isset($_POST['add']) ? date('Y-m-d') : ($_POST['date_creation'] ?? date('Y-m-d'));

    // Validation
    if (strlen($nom) < 3) $errors[] = "Le nom doit contenir au moins 3 caractères.";
    if (strlen($desc) < 3) $errors[] = "La description doit contenir au moins 3 caractères.";
    if ($date_creation < date('Y-m-d')) $errors[] = "La date de création doit être aujourd'hui ou ultérieure.";

    if (empty($errors)) {
        $categorie = new Categorie($nom, $desc, $date_creation, $etat, $nb_evenements);

        if (isset($_POST['add'])) {
            $cc->addCategorie($categorie);
        } elseif (isset($_POST['update']) && !empty($_POST['id_categorie'])) {
            $cc->updateCategorie((int) $_POST['id_categorie'], $categorie);
        }

        header("Location: manageCategories.php");
        exit;
    }
}


if (isset($_GET['delete'])) {
    $cc->deleteCategorie($_GET['delete']);
    header("Location: manageCategories.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des Catégories</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>

:root {
    --primary-red: #d32f2f;
    --dark-red: #b71c1c;
    --light-red: #ff6659;
    --dark-bg: #121212;
    --card-bg: #1e1e1e;
    --text-light: #f5f5f5;
    --text-muted: #aaaaaa;
    --border-color: #333333;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Montserrat', sans-serif;
    background-color: var(--dark-bg);
    color: var(--text-light);
    line-height: 1.6;
    display: flex;
    min-height: 100vh;
}

/* === STYLE SIDEBAR === */
.sidebar {
    width: 260px;
    background-color: var(--card-bg);
    padding: 20px 0;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
    z-index: 100;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    top: 0;
    left: 0;
}

.sidebar-header {
    text-align: center;
    padding: 20px;
    border-bottom: 1px solid #333333;
    margin-bottom: 20px;
}

.sidebar-logo {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 15px;
}

.sidebar-title {
    font-size: 22px;
    font-weight: 700;
    color: #f5f5f5;
}

.sidebar-title span {
    color: #d32f2f;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
}

.sidebar-link {
    padding: 15px 25px;
    color: #aaaaaa;
    display: flex;
    align-items: center;
    gap: 15px;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.sidebar-link:hover {
    background-color: rgba(255, 255, 255, 0.05);
    color: #ffffff;
}

.sidebar-link.active {
    background-color: rgba(211, 47, 47, 0.1);
    color: #d32f2f;
    border-left-color: #d32f2f;
}

/* Main content */
.main-container {
    margin-left: 260px;
    padding: 30px;
    width: calc(100% - 260px);
}

.page-title {
    font-size: 28px;
    margin-bottom: 25px;
    color: var(--text-light);
    font-weight: 600;
    border-bottom: 2px solid var(--primary-red);
    padding-bottom: 10px;
}

/* ==== STAT CARDS (style identique à manageEvents) ==== */
.stats-container {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
}

.stat-card {
    flex: 1;
    padding: 25px;
    background-color: #111;
    border-radius: 12px;
    text-align: center;
    color: white;
    border: 1px solid #333;
    transition: 0.3s;
}

.stat-card:hover {
    transform: translateY(-3px);
    background-color: #1a1a1a;
}

.stat-card i {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #d32f2f;
}

.stat-card h3 {
    font-size: 2rem;
    margin: 10px 0;
}

.stat-card p {
    color: #aaaaaa;
    font-size: 0.9rem;
}

/* Card design */
.card {
    background: #000000;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    padding: 20px;
    margin-bottom: 25px;
    border: 1px solid #333;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #333333;
}

.card-title {
    font-size: 20px;
    font-weight: 600;
    color: #ffffff;
}

/* Table styling - MODIFIÉ pour correspondre à l'ancienne interface */
.table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.table th {
    background-color: #d32f2f; /* Bande en rouge */
    color: #ffffff; /* Texte en blanc */
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: 0.5px;
}

.table td {
    padding: 12px 15px;
    border-bottom: 1px solid #333333;
    background-color: #000000; /* Fond des cellules en noir */
    color: #ffffff; /* Texte en blanc */
}

.table tr:last-child td {
    border-bottom: none;
}

.table tr:hover td {
    background-color: #1a1a1a; /* Surbrillance au survol */
}

/* Form styling */
.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #ffffff;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #333333;
    border-radius: 4px;
    font-size: 16px;
    transition: border-color 0.3s;
    background-color: #1a1a1a;
    color: #ffffff;
}

.form-control:focus {
    outline: none;
    border-color: #d32f2f;
    box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.2);
}

.form-control::placeholder {
    color: #aaaaaa;
}

/* Button styling */
.btn {
    display: inline-block;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 15px;
}

.btn-primary {
    background-color: #d32f2f;
    color: white;
}

.btn-primary:hover {
    background-color: #b71c1c;
}

.btn-success {
    background-color: #4caf50;
    color: white;
}

.btn-success:hover {
    background-color: #388e3c;
}

.btn-danger {
    background-color: #f44336;
    color: white;
}

.btn-danger:hover {
    background-color: #d32f2f;
}

.btn-outline {
    background-color: transparent;
    border: 1px solid #333;
    color: #f5f5f5;
}

.btn-outline:hover {
    background-color: #333;
    color: white;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 14px;
}

/* Alert/Message styling */
.alert {
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-error {
    background-color: rgba(244, 67, 54, 0.1);
    color: #f44336;
    border-left: 4px solid #f44336;
}

.alert-success {
    background-color: rgba(76, 175, 80, 0.1);
    color: #4caf50;
    border-left: 4px solid #4caf50;
}

/* Grid system */
.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px;
}

.col {
    flex: 1;
    padding: 0 10px;
}

.col-6 {
    flex: 0 0 50%;
    padding: 0 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    
    .main-container {
        margin-left: 0;
        width: 100%;
    }
    
    .stats-container {
        flex-direction: column;
    }
    
    .col-6 {
        flex: 0 0 100%;
    }
    
    .table {
        display: block;
        overflow-x: auto;
    }
}

/* Badge styling */
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-active {
    background-color: rgba(76, 175, 80, 0.2);
    color: #4caf50;
}

.badge-inactive {
    background-color: rgba(244, 67, 54, 0.2);
    color: #f44336;
}

/* Action buttons container */
.action-buttons {
    display: flex;
    gap: 8px;
}

/* === Popup 2FA styles === */
#authPopup {
    display:none;
    position:fixed;
    top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.7);
    justify-content:center;
    align-items:center;
    z-index:9999;
}
#authPopup .popup-box {
    background:#1e1e1e;
    padding:25px;
    border-radius:10px;
    width:360px;
    text-align:center;
    color:#fff;
    border:1px solid #333;
}
#authPopup img.qr {
    width:180px; height:180px; margin:10px 0;
}
#authPopup .secret-box {
    background:#000; padding:10px; border-radius:6px; margin-bottom:12px; color:#d32f2f; font-weight:bold;
}
#authPopup input.form-control { text-align:center; margin-bottom:10px; }

</style>
</head>
<body>
<!-- ==== SIDEBAR ==== -->
<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../uploads/logoN.png" class="sidebar-logo" alt="Logo">
        <h2 class="sidebar-title">SUPPORTINI<span>.TN</span></h2>
    </div>

    <nav class="sidebar-nav">
        <a href="manageCategories.php" class="sidebar-link active">
            <i class="fa-solid fa-layer-group"></i> Catégories
        </a>

        <a href="http://localhost/Gcategori/view/backoffice/manageEvenements.php" class="sidebar-link">
            <i class="fa-solid fa-calendar"></i> Événements
        </a>

        <a href="http://localhost/Gcategori/view/frontoffice/categories.php" class="sidebar-link">
            <i class="fa-solid fa-list"></i> Frontoffice
        </a>

        <a href="/logout.php" class="sidebar-link">
            <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
        </a>
    </nav>
</div>

<div class="main-container">
    <h1 class="page-title"><i class="fa-solid fa-layer-group"></i> Gestion des Catégories</h1>

    <!-- === STATISTIQUES CATÉGORIES (style identique aux Événements) === -->
    <div class="stats-container">
        <!-- Catégories Total -->
        <div class="stat-card">
            <i class="fa-solid fa-layer-group"></i>
            <h3><?php echo $totalCategories; ?></h3>
            <p>Catégories total</p>
        </div>

        <!-- Places Totales -->
        <div class="stat-card">
            <i class="fa-solid fa-chair"></i>
            <h3><?php echo $totalPlaces; ?></h3>
            <p>Places totales</p>
        </div>

        <!-- Inscrits Totaux -->
        <div class="stat-card">
            <i class="fa-solid fa-users"></i>
            <h3><?php echo $totalInscrits; ?></h3>
            <p>Inscrits total</p>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?>
                <p><?= $err ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- ========================= -->
    <!-- POPUP 2FA (QR + SECRET + INPUT) -->
    <!-- ========================= -->
    <div id="authPopup">
        <div class="popup-box">
            <h2>Vérification Google Authenticator</h2>
            <p>Scanne ce QR Code avec votre application Google Authenticator</p>
            <img class="qr" src="<?= htmlspecialchars($qrCodeUrl) ?>" alt="QR Code 2FA">
            <p><strong>Code secret (si pas de scan) :</strong></p>
            <div class="secret-box"><?= htmlspecialchars($secret) ?></div>

            <p>Entrez le code à 6 chiffres :</p>
            <input id="code2fa" class="form-control" type="number" placeholder="000000" maxlength="6">
            <div style="display:flex; gap:8px; justify-content:center; margin-top:8px;">
                <button class="btn btn-outline" onclick="closeAuthPopup()">Annuler</button>
                <button class="btn btn-primary" onclick="verifyCode2FA()">Vérifier</button>
            </div>
            <p id="authMsg" style="margin-top:10px;"></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Liste des Catégories</h2>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NOM</th>
                    <th>DESCRIPTION</th>
                    <th>DATE CRÉATION</th>
                    <th>ÉTAT</th>
                    <th>NB ÉVÈNEMENTS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($liste as $cat): ?>
                <tr>
                    <td><?= $cat['id_categorie'] ?></td>
                    <td><?= htmlspecialchars($cat['nom_categorie']) ?></td>
                    <td><?= htmlspecialchars($cat['description_categorie']) ?></td>
                    <td><?= date('d/m/Y', strtotime($cat['date_creation'])) ?></td>
                    <td>
                        <span class="badge <?= $cat['etat'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $cat['etat'] ?>
                        </span>
                    </td>
                    <td><?= $cat['nb_evenements'] ?></td>
                    <td>
                        <div class="action-buttons">
                            <a class="btn btn-success btn-sm" href="manageCategories.php?edit=<?= $cat['id_categorie'] ?>">
                                <i class="fa-solid fa-pen"></i> Modifier
                            </a>
                            <a class="btn btn-danger btn-sm" href="manageCategories.php?delete=<?= $cat['id_categorie'] ?>" onclick="return confirm('Supprimer cette catégorie ?');">
                                <i class="fa-solid fa-trash"></i> Supprimer
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title"><?= $categorieToEdit ? "Modifier la Catégorie" : "Ajouter une Catégorie" ?></h2>
        </div>
        
        <!-- IMPORTANT: le formulaire reste en POST 'add'/'update' -->
        <form id="catForm" method="POST" novalidate onsubmit="return validateForm()">
            <?php if ($categorieToEdit): ?>
                <input type="hidden" name="id_categorie" value="<?= $categorieToEdit['id_categorie'] ?>">
                <input type="hidden" name="date_creation" value="<?= $categorieToEdit['date_creation'] ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Nom :</label>
                        <input type="text" id="nom" name="nom_categorie" class="form-control" value="<?= $categorieToEdit['nom_categorie'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Description :</label>
                        <input type="text" id="desc" name="description_categorie" class="form-control" value="<?= $categorieToEdit['description_categorie'] ?? '' ?>">
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top:10px;">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">État :</label>
                        <select name="etat" class="form-control">
                            <option value="active" <?= ($categorieToEdit['etat'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="désactivée" <?= ($categorieToEdit['etat'] ?? '') === 'désactivée' ? 'selected' : '' ?>>Désactivée</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Nombre d'événements :</label>
                        <input type="number" id="nb_evenements" name="nb_evenements" class="form-control" value="<?= $categorieToEdit['nb_evenements'] ?? '0' ?>">
                    </div>
                </div>
            </div>

            <!-- BOUTON AJOUTER/MODIFIER -->
            <div class="form-group" style="margin-top:15px;">
                <?php if ($categorieToEdit): ?>
                    <button class="btn btn-primary" type="submit" name="update">
                        <i class="fa-solid fa-check"></i> Mettre à jour
                    </button>
                    <a href="manageCategories.php" class="btn btn-outline">Annuler</a>
                <?php else: ?>
                    <!-- Bouton Ajout : on ouvre le popup 2FA au lieu d'envoyer directement -->
                    <button class="btn btn-primary" type="button" id="addBtn" onclick="openAuthPopup()">
                        <i class="fa-solid fa-plus"></i> Ajouter
                    </button>
                <?php endif; ?>
            </div>
            
            <div id="msg"></div>
            <!-- Note: when the 2FA is OK the form will be submitted programmatically with an added hidden input 'add' -->
        </form>
    </div>
</div>

<script>
function validateForm() {
    let nom = document.getElementById("nom");
    let desc = document.getElementById("desc");
    let date = document.getElementById("date");
    let nbEv = document.getElementById("nb_evenements");
    let msg = document.getElementById("msg");

    // Reset styles
    if(nom) nom.style.border = "";
    if(desc) desc.style.border = "";
    if(date) date.style.border = "";
    if(nbEv) nbEv.style.border = "";
    msg.innerHTML = "";
    msg.className = "";

    let isValid = true;

    if (!nom || nom.value.trim().length < 3) {
        msg.innerHTML = "Le nom doit contenir au moins 3 caractères.";
        msg.className = "alert alert-error";
        if(nom) nom.style.border = "1px solid #f44336";
        isValid = false;
    }
    
    if (!desc || desc.value.trim().length < 3) {
        msg.innerHTML = "La description doit contenir au moins 3 caractères.";
        msg.className = "alert alert-error";
        if(desc) desc.style.border = "1px solid #f44336";
        isValid = false;
    }

    let today = new Date().toISOString().split("T")[0];
    if (date && (!date.value || date.value < today)) {
        msg.innerHTML = "La date de création doit être aujourd'hui ou ultérieure.";
        msg.className = "alert alert-error";
        date.style.border = "1px solid #f44336";
        isValid = false;
    }

    if (nbEv && parseInt(nbEv.value) < 0) {
        msg.innerHTML = "Le nombre d'événements doit être supérieur ou égal à 0.";
        msg.className = "alert alert-error";
        nbEv.style.border = "1px solid #f44336";
        isValid = false;
    }

    if (isValid) {
        msg.innerHTML = "Formulaire valide ✔";
        msg.className = "alert alert-success";
    }
    
    return isValid;
}

/* ========= 2FA Popup control ========= */
function openAuthPopup() {
    // Validate client-side basic fields before showing popup
    if (!validateForm()) return;
    document.getElementById("authPopup").style.display = "flex";
    document.getElementById("authMsg").innerHTML = "";
    document.getElementById("code2fa").value = "";
}

function closeAuthPopup() {
    document.getElementById("authPopup").style.display = "none";
}

function verifyCode2FA() {
    let code = document.getElementById("code2fa").value.trim();
    if (!code || code.length < 6) {
        document.getElementById("authMsg").innerHTML = "Entrez un code à 6 chiffres.";
        document.getElementById("authMsg").style.color = "red";
        return;
    }

    // Prépare la donnée POST
    let formData = new FormData();
    formData.append('action', 'verify2fa');
    formData.append('code', code);

    fetch(location.href, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(resp => resp.text())
    .then(text => {
        if (text.trim() === "OK") {
            document.getElementById("authMsg").innerHTML = "✔ Code vérifié, ajout en cours...";
            document.getElementById("authMsg").style.color = "lightgreen";

            // Ajoute un champ caché 'add' pour simuler le submit avec le nom attendu côté serveur
            let form = document.getElementById("catForm");
            let hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = "add";
            hidden.value = "1";
            form.appendChild(hidden);

            // Soumettre le formulaire (après un court délai pour UX)
            setTimeout(function(){
                form.submit();
            }, 700);

        } else {
            document.getElementById("authMsg").innerHTML = "❌ Code incorrect, réessayez.";
            document.getElementById("authMsg").style.color = "red";
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById("authMsg").innerHTML = "Erreur réseau. Réessayez.";
        document.getElementById("authMsg").style.color = "red";
    });
}
</script>
</body>
</html>
