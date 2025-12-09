<?php
require_once __DIR__ . '/../../controller/CategorieC.php';
$cc = new CategorieC();

if(!isset($_GET['id'])) die("Catégorie non définie");

$id = intval($_GET['id']);
$cat = $cc->getCategorie($id);

if(!$cat) die("Catégorie introuvable");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Détails Catégorie - Supportini</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    :root {
        --primary: #d32f2f;
        --primary-dark: #b71c1c;
        --secondary: #212121;
        --light-gray: #f5f5f5;
        --medium-gray: #e0e0e0;
        --dark-gray: #757575;
        --white: #ffffff;
        --text: #212121;
        --text-light: #757575;
        --success: #4caf50;
        --warning: #ff9800;
        --error: #f44336;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background-color: #000000;
        color: var(--white);
        line-height: 1.6;
    }

    /* Header style Pathé */
    .header {
        background-color: var(--primary);
        color: var(--white);
        padding: 15px 0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .logo {
        font-size: 28px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .nav-menu {
        display: flex;
        gap: 25px;
    }

    .nav-link {
        color: var(--white);
        text-decoration: none;
        font-weight: 500;
        padding: 8px 12px;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .nav-link:hover, .nav-link.active {
        background-color: rgba(255,255,255,0.1);
    }

    /* Hero Section */
    .hero {
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://via.placeholder.com/1920x600') center/cover no-repeat;
        color: var(--white);
        padding: 80px 20px;
        text-align: center;
    }

    .hero-title {
        font-size: 3rem;
        margin-bottom: 20px;
        font-weight: 700;
    }

    .hero-subtitle {
        font-size: 1.2rem;
        max-width: 600px;
        margin: 0 auto 30px;
        opacity: 0.9;
    }

    /* Main content */
    .main-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        background-color: #000000;
    }

    .section-title {
        font-size: 2rem;
        text-align: center;
        margin-bottom: 40px;
        color: #b0b0b0;
        position: relative;
        padding-bottom: 15px;
    }

    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background-color: var(--primary);
    }

    /* Category Details Card */
    .category-detail-card {
        background: var(--white);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin-bottom: 50px;
        color: var(--text);
    }

    .detail-header {
        background-color: var(--primary);
        color: var(--white);
        padding: 30px;
        position: relative;
    }

    .detail-title {
        font-size: 2rem;
        margin: 0;
        font-weight: 600;
    }

    .detail-badge {
        position: absolute;
        top: 30px;
        right: 30px;
        background: var(--white);
        color: var(--primary);
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 1rem;
        font-weight: 600;
    }

    .detail-body {
        padding: 30px;
    }

    .detail-section {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--medium-gray);
    }

    .detail-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .section-heading {
        font-size: 1.3rem;
        color: var(--primary);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-heading i {
        font-size: 1.1rem;
    }

    .detail-description {
        color: var(--text-light);
        line-height: 1.7;
        font-size: 1.05rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 15px;
    }

    .stat-card {
        background: var(--light-gray);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        transition: transform 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .status-active {
        background-color: #e8f5e9;
        color: var(--success);
    }

    .status-inactive {
        background-color: #ffebee;
        color: var(--error);
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        flex-wrap: wrap;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 25px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }

    .btn-primary {
        background: var(--primary);
        color: var(--white);
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .btn-secondary {
        background: var(--medium-gray);
        color: var(--text);
    }

    .btn-secondary:hover {
        background: var(--dark-gray);
        color: var(--white);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* Footer */
    .footer {
        background-color: var(--secondary);
        color: var(--white);
        padding: 40px 20px;
        text-align: center;
    }

    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
    }

    .footer-links {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin: 20px 0;
    }

    .footer-link {
        color: var(--white);
        text-decoration: none;
        transition: color 0.3s;
    }

    .footer-link:hover {
        color: var(--primary);
    }

    .copyright {
        margin-top: 20px;
        color: var(--dark-gray);
        font-size: 0.9rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .header-container {
            flex-direction: column;
            gap: 15px;
        }
        
        .nav-menu {
            gap: 10px;
        }
        
        .hero-title {
            font-size: 2rem;
        }
        
        .detail-header {
            padding: 20px;
        }
        
        .detail-title {
            font-size: 1.5rem;
        }
        
        .detail-badge {
            position: static;
            display: inline-block;
            margin-top: 10px;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .footer-links {
            flex-direction: column;
            gap: 15px;
        }
    }
</style>
</head>
<body>

<header class="header">
    <div class="header-container">
        <div class="logo">
            <img src="../../uploads/logoN.png" class="sidebar-logo" alt="Logo" style="height:40px; vertical-align:middle; margin-right:10px;">
            SUPPORTINI.TN
        </div>
        <nav class="nav-menu">
            <a href="#" class="nav-link"><i class="fa-solid fa-house"></i> Accueil</a>
            <a href="http://localhost/Gcategori/view/frontoffice/evenements.php" class="nav-link">
                <i class="fa-solid fa-calendar"></i> Événements
            </a>
            <a href="#" class="nav-link active"><i class="fa-solid fa-list"></i> Catégories</a>
            <a href="#" class="nav-link"><i class="fa-solid fa-address-card"></i> Contact</a>
        </nav>
    </div>
</header>

<section class="hero">
    <h1 class="hero-title">DÉTAILS DE LA CATÉGORIE</h1>
    <p class="hero-subtitle">Consultez toutes les informations détaillées sur cette catégorie d'événements.</p>
</section>

<div class="main-container">
    <h2 class="section-title">Informations détaillées</h2>

    <div class="category-detail-card">
        <div class="detail-header">
            <h1 class="detail-title"><?= htmlspecialchars($cat['nom_categorie']) ?></h1>
            <span class="detail-badge"><?= $cat['nb_evenements'] ?> événements</span>
        </div>
        
        <div class="detail-body">
            <div class="detail-section">
                <h3 class="section-heading"><i class="fa-solid fa-info-circle"></i> Description</h3>
                <p class="detail-description"><?= htmlspecialchars($cat['description_categorie']) ?></p>
            </div>
            
            <div class="detail-section">
                <h3 class="section-heading"><i class="fa-solid fa-chart-bar"></i> Statistiques</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= $cat['nb_evenements'] ?></div>
                        <div class="stat-label">Événements</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= date('d/m/Y', strtotime($cat['date_creation'])) ?></div>
                        <div class="stat-label">Date de création</div>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h3 class="section-heading"><i class="fa-solid fa-toggle-on"></i> État de la catégorie</h3>
                <span class="status-badge <?= $cat['etat'] == 'actif' ? 'status-active' : 'status-inactive' ?>">
                    <?= ucfirst($cat['etat']) ?>
                </span>
                <p style="margin-top: 10px; color: var(--text-light); font-size: 0.9rem;">
                    <?= $cat['etat'] == 'actif' ? 
                        'Cette catégorie est actuellement active et visible par les utilisateurs.' : 
                        'Cette catégorie est actuellement inactive et n\'est pas visible par les utilisateurs.' ?>
                </p>
            </div>
            
            <div class="action-buttons">
                <a href="http://localhost/Gcategori/view/frontoffice/categories.php" class="action-btn btn-primary">
                    <i class="fa-solid fa-arrow-left"></i> Retour aux catégories
                </a>
                <a href="http://localhost/eventsCopy/view/frontoffice/evenements.php?categorie=<?= $id ?>" class="action-btn btn-secondary">
                    <i class="fa-solid fa-calendar-day"></i> Voir les événements
                </a>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-links">
            <a href="#" class="footer-link">Mentions légales</a>
            <a href="#" class="footer-link">Politique de confidentialité</a>
            <a href="#" class="footer-link">Conditions générales</a>
            <a href="#" class="footer-link">Contact</a>
        </div>
        <p class="copyright">© 2023 Supportini.tn - Tous droits réservés</p>
    </div>
</footer>

</body>
</html>