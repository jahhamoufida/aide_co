<?php
require_once __DIR__ . '/../../controller/CategorieC.php';

$cc = new CategorieC();

if (!empty($_GET['search'])) {
    $liste = $cc->searchCategories($_GET['search']);
} else {
    $liste = $cc->listCategories();
}



?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Nos Catégories - Supportini</title>
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
        background: 
            radial-gradient(circle at 10% 20%, rgba(211, 47, 47, 0.1) 0%, transparent 20%),
            radial-gradient(circle at 90% 40%, rgba(211, 47, 47, 0.08) 0%, transparent 20%),
            radial-gradient(circle at 50% 80%, rgba(211, 47, 47, 0.05) 0%, transparent 20%),
            linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 25%, #0c0c0c 50%, #1a1a1a 75%, #0a0a0a 100%);
        color: var(--white);
        line-height: 1.6;
        min-height: 100vh;
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
        background-color: transparent;
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

    /* Cards Grid */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 50px;
    }

    .category-card {
        background: var(--white);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
    }

    .category-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    .card-header {
        background-color: var(--primary);
        color: var(--white);
        padding: 20px;
        position: relative;
    }

    .card-title {
        font-size: 1.5rem;
        margin: 0;
        font-weight: 600;
    }

    .card-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: var(--white);
        color: var(--primary);
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .card-body {
        padding: 20px;
    }

    .card-description {
        color: var(--text-light);
        margin-bottom: 15px;
        line-height: 1.5;
    }

    .card-details {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 0.9rem;
    }

    .card-detail-item {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .detail-label {
        color: var(--text-light);
        font-size: 0.8rem;
        margin-bottom: 5px;
    }

    .detail-value {
        font-weight: 600;
        color: var(--secondary);
    }

    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
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

    /* Table View Toggle */
    .view-toggle {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
        gap: 10px;
    }

    .toggle-btn {
        background: var(--white);
        border: 2px solid var(--medium-gray);
        padding: 10px 20px;
        border-radius: 30px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .toggle-btn.active {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
    }

    /* Table View */
    .table-container {
        background: var(--white);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin-bottom: 50px;
        display: none;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        background-color: var(--primary);
        color: var(--white);
        padding: 15px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid var(--medium-gray);
        color: var(--text);
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    .table tr:hover {
        background-color: rgba(0,0,0,0.02);
    }

    /* Statistics Section */
    .stats-container {
        background: var(--white);
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 50px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        display: none;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--light-gray);
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 10px;
    }

    .stat-label {
        font-size: 1rem;
        color: var(--text-light);
    }

    .chart-container {
        background: var(--light-gray);
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }

    .chart-title {
        text-align: center;
        margin-bottom: 20px;
        color: var(--secondary);
        font-weight: 600;
    }

    .chart-placeholder {
        height: 300px;
        background: linear-gradient(45deg, var(--medium-gray), var(--light-gray));
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--dark-gray);
        font-weight: 600;
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
        
        .cards-grid {
            grid-template-columns: 1fr;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .table {
            min-width: 600px;
        }
        
        .footer-links {
            flex-direction: column;
            gap: 15px;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
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
            <a href="#" class="nav-link active"><i class="fa-solid fa-house"></i> Accueil</a>
            <a href="http://localhost/Gcategori/view/frontoffice/evenements.php" class="nav-link">
                <i class="fa-solid fa-calendar"></i> Événements
            </a>
           <a href="statistiques.php" class="nav-link">
    <i class="fa-solid fa-chart-bar"></i> Statistiques
</a>

            <a href="#" class="nav-link"><i class="fa-solid fa-address-card"></i> Contact</a>
        </nav>
    </div>
</header>

<section class="hero">
    <h1 class="hero-title">NOS CATÉGORIES</h1>
    <p class="hero-subtitle">Découvrez toutes nos catégories d'événements. Trouvez ce qui vous intéresse et réservez vos places en ligne.</p>
</section>

<div class="main-container">
    <h2 class="section-title">Explorez nos catégories</h2>

    <!-- Formulaire de recherche -->
    <form method="GET" style="margin-bottom:20px; text-align:center;">
        <input type="text" name="search" placeholder="Rechercher une catégorie..." 
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
               style="padding:8px 12px; border-radius:5px; border:1px solid #ccc; width:250px;">
        <button type="submit" style="padding:8px 12px; border:none; background:#d32f2f; color:white; border-radius:5px; cursor:pointer;">
            <i class="fa-solid fa-magnifying-glass"></i> Rechercher
        </button>
    </form>

    <div class="view-toggle">
        <button class="toggle-btn active" id="cardViewBtn">
            <i class="fa-solid fa-grip"></i> Vue Cartes
        </button>
        <button class="toggle-btn" id="tableViewBtn">
            <i class="fa-solid fa-table"></i> Vue Tableau
        </button>
    </div>


    <!-- Vue Cartes (par défaut) -->
    <div class="cards-grid" id="cardsView">
        <?php foreach($liste as $cat): ?>
        <div class="category-card">
            <div class="card-header">
                <h3 class="card-title"><?= htmlspecialchars($cat['nom_categorie']) ?></h3>
                <span class="card-badge"><?= $cat['nb_evenements'] ?> événements</span>
            </div>
            <div class="card-body">
                <p class="card-description"><?= htmlspecialchars($cat['description_categorie']) ?></p>
                <div class="card-details">
                    <div class="card-detail-item">
                        <span class="detail-label">Date création</span>
                        <span class="detail-value"><?= $cat['date_creation'] ?></span>
                    </div>
                    <div class="card-detail-item">
                        <span class="detail-label">Statut</span>
                        <span class="status-badge <?= $cat['etat'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                            <?= ucfirst($cat['etat']) ?>
                        </span>
                    </div>
                </div>
                <a href="categorie_detail.php?id=<?= $cat['id_categorie'] ?>" class="toggle-btn" style="width:100%; display:flex; justify-content:center; margin-top:10px; text-decoration:none;">
                    <i class="fa-solid fa-eye"></i> Voir les détails
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Vue Tableau -->
    <div class="table-container" id="tableView">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Date Création</th>
                    <th>État</th>
                    <th>Nb Événements</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($liste as $cat): ?>
                <tr>
                    <td><?= $cat['id_categorie'] ?></td>
                    <td><strong><?= htmlspecialchars($cat['nom_categorie']) ?></strong></td>
                    <td><?= htmlspecialchars($cat['description_categorie']) ?></td>
                    <td><?= $cat['date_creation'] ?></td>
                    <td>
                        <span class="status-badge <?= $cat['etat'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                            <?= ucfirst($cat['etat']) ?>
                        </span>
                    </td>
                    <td><?= $cat['nb_evenements'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Section Statistiques -->
    <div class="stats-container" id="statsView">
        <h2 class="section-title">Statistiques des Catégories</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= count($liste) ?></div>
                <div class="stat-label">Total Catégories</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php 
                    $activeCount = 0;
                    foreach($liste as $cat) {
                        if($cat['etat'] === 'active') $activeCount++;
                    }
                    echo $activeCount;
                    ?>
                </div>
                <div class="stat-label">Catégories Actives</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php 
                    $totalEvents = 0;
                    foreach($liste as $cat) {
                        $totalEvents += $cat['nb_evenements'];
                    }
                    echo $totalEvents;
                    ?>
                </div>
                <div class="stat-label">Total Événements</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php 
                    $avgEvents = count($liste) > 0 ? round($totalEvents / count($liste), 1) : 0;
                    echo $avgEvents;
                    ?>
                </div>
                <div class="stat-label">Moyenne d'événements par catégorie</div>
            </div>
        </div>
        
        <div class="chart-container">
            <h3 class="chart-title">Répartition des événements par catégorie</h3>
            <div class="chart-placeholder">
                Graphique des événements par catégorie
                <!-- Ici vous pourriez intégrer une bibliothèque de graphiques comme Chart.js -->
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

<script>
    // Toggle entre les vues cartes et tableau
    document.getElementById('cardViewBtn').addEventListener('click', function() {
        document.getElementById('cardsView').style.display = 'grid';
        document.getElementById('tableView').style.display = 'none';
        document.getElementById('statsView').style.display = 'none';
        document.getElementById('cardViewBtn').classList.add('active');
        document.getElementById('tableViewBtn').classList.remove('active');
        document.getElementById('statsBtn').classList.remove('active');
    });

    document.getElementById('tableViewBtn').addEventListener('click', function() {
        document.getElementById('cardsView').style.display = 'none';
        document.getElementById('tableView').style.display = 'block';
        document.getElementById('statsView').style.display = 'none';
        document.getElementById('tableViewBtn').classList.add('active');
        document.getElementById('cardViewBtn').classList.remove('active');
        document.getElementById('statsBtn').classList.remove('active');
    });

    // Affichage des statistiques
    document.getElementById('statsBtn').addEventListener('click', function() {
        document.getElementById('cardsView').style.display = 'none';
        document.getElementById('tableView').style.display = 'none';
        document.getElementById('statsView').style.display = 'block';
        document.getElementById('statsBtn').classList.add('active');
        document.getElementById('cardViewBtn').classList.remove('active');
        document.getElementById('tableViewBtn').classList.remove('active');
    });

    // Par défaut, afficher la vue cartes
    document.getElementById('cardsView').style.display = 'grid';
    document.getElementById('tableView').style.display = 'none';
    document.getElementById('statsView').style.display = 'none';
</script>

</body>
</html>