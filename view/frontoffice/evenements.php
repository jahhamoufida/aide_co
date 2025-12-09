<?php
require_once __DIR__ . '/../../controller/EvenementC.php';
$ec = new EvenementC();

$name = $_GET['name'] ?? null;
$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;
$availability = $_GET['availability'] ?? null;

if ($name || $date_from || $date_to || $availability) {
    $liste = $ec->filterEvenements($name, $date_from, $date_to, $availability);
} else {
    $liste = $ec->listEvenements();
}

// chemin public
$upload_web_dir = '/events/uploads/';
$base_url = '/events';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Événements | Supportini</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
            min-height: 100vh;
        }

        /* Header */
        .main-header {
            background: linear-gradient(135deg, var(--dark-red), var(--primary-red));
            padding: 25px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .site-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
        }

        .site-title span {
            color: #ffccbc;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 36px;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .page-title i {
            color: var(--primary-red);
            margin-right: 15px;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 18px;
        }

        /* Search and Filters */
        .search-container {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            margin-bottom: 40px;
        }

        .search-title {
            font-size: 22px;
            margin-bottom: 25px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text-light);
            font-size: 14px;
        }

        .form-control {
            padding: 14px 16px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-light);
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.2);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--primary-red);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--dark-red);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.4);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--border-color);
            color: var(--text-light);
        }

        .btn-outline:hover {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: var(--primary-red);
            transform: translateY(-2px);
        }

        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--card-bg), #252525);
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-left: 4px solid var(--primary-red);
        }

        .stat-icon {
            font-size: 32px;
            margin-bottom: 15px;
            color: var(--primary-red);
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-light);
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .event-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
            border-color: var(--primary-red);
        }

        .event-image-container {
            position: relative;
            overflow: hidden;
            height: 220px;
        }

        .event-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .event-card:hover .event-image {
            transform: scale(1.05);
        }

        .event-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }

        .badge-available {
            background-color: #4caf50;
        }

        .badge-full {
            background-color: var(--primary-red);
        }

        .event-content {
            padding: 25px;
        }

        .event-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-light);
            line-height: 1.3;
        }

        .event-meta {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .event-meta-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .event-meta-item i {
            color: var(--primary-red);
            width: 16px;
            font-size: 16px;
        }

        .event-places {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 6px;
        }

        .places-info {
            font-size: 14px;
            color: var(--text-muted);
        }

        .places-remaining {
            font-weight: 600;
            font-size: 16px;
            color: var(--text-light);
        }

        .highlight {
            color: var(--primary-red);
            font-weight: 700;
        }

        .event-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-details {
            background-color: transparent;
            border: 2px solid var(--primary-red);
            color: var(--primary-red);
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-details:hover {
            background-color: var(--primary-red);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--border-color);
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: var(--text-light);
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 25px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Footer */
        .main-footer {
            background-color: var(--card-bg);
            padding: 30px 0;
            margin-top: 60px;
            border-top: 1px solid var(--border-color);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }

        .footer-text {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .page-title {
                font-size: 28px;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .event-actions {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }

            .btn-details {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .main-header {
                padding: 20px 0;
            }

            .logo-section {
                flex-direction: column;
                gap: 10px;
            }

            .site-title {
                font-size: 24px;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
            }

            .nav-link {
                text-align: center;
            }

            .page-title {
                font-size: 24px;
            }

            .search-container {
                padding: 20px;
            }

            .stats-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <img src="http://localhost/Gcategori/uploads/logoN.png" class="logo" alt="Supportini Logo">
                <h1 class="site-title">SUPPORTINI<span>.TN</span></h1>
            </div>
            
            <nav class="nav-links">
                <a href="#" class="nav-link">
                    <i class="fa-solid fa-house"></i> Accueil
                </a>
                <a href="http://localhost/Gcategori/view/frontoffice/evenements.php" class="nav-link active">
                    <i class="fa-solid fa-calendar-days"></i> Événements
                </a>
                <a href="http://localhost/Gcategori/view/frontoffice/statistique.php" class="nav-link">
                     <i class="fa-solid fa-chart-column"></i> Statistiques
                </a>

                <a href="http://localhost/Gcategori/view/frontoffice/categories.php" class="nav-link active">
                     <i class="fa-solid fa-layer-group"></i> Catégories
                </a>

                <a href="#" class="nav-link">
                    <i class="fa-solid fa-ticket"></i> Mes Réservations
                </a>
                <a href="/logout.php" class="nav-link">
                    <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><i class="fa-solid fa-calendar-days"></i> Liste des Événements</h1>
            <p class="page-subtitle">Découvrez tous nos événements et réservez vos places</p>
        </div>

        <!-- Stats Section -->
        <?php
        $totalEvents = count($liste);
        $totalPlaces = 0;
        $totalInscrits = 0;
        $availableEvents = 0;
        
        foreach ($liste as $ev) {
            $totalPlaces += $ev['nombre_places'];
            $totalInscrits += $ev['nombre_inscrits'];
            if ($ev['nombre_inscrits'] < $ev['nombre_places']) {
                $availableEvents++;
            }
        }
        ?>
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-film"></i>
                </div>
                <div class="stat-value"><?= $totalEvents ?></div>
                <div class="stat-label">Événements total</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div class="stat-value"><?= $totalPlaces ?></div>
                <div class="stat-label">Places totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-value"><?= $totalInscrits ?></div>
                <div class="stat-label">Inscrits total</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div class="stat-value"><?= $availableEvents ?></div>
                <div class="stat-label">Événements disponibles</div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-container">
            <h2 class="search-title"><i class="fa-solid fa-filter"></i> Filtrer les événements</h2>
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label class="form-label" for="name">Nom de l'événement</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           placeholder="Rechercher par nom..." value="<?= htmlspecialchars($name ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="date_from">Date de début</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" 
                           value="<?= htmlspecialchars($date_from ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="date_to">Date de fin</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" 
                           value="<?= htmlspecialchars($date_to ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="availability">Disponibilité</label>
                    <select id="availability" name="availability" class="form-control">
                        <option value="">Tous les événements</option>
                        <option value="available" <?= ($availability=='available')?'selected':'' ?>>Disponibles</option>
                        <option value="full" <?= ($availability=='full')?'selected':'' ?>>Complets</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i> Appliquer les filtres
                    </button>
                    <a href="evenements.php" class="btn btn-outline">
                        <i class="fa-solid fa-rotate-left"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Events Grid -->
        <div class="events-grid">
            <?php if (count($liste) == 0): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-calendar-xmark"></i>
                    <h3>Aucun événement trouvé</h3>
                    <p>Aucun événement ne correspond à vos critères de recherche. Essayez de modifier vos filtres ou réinitialisez la recherche.</p>
                    <a href="evenements.php" class="btn btn-primary">
                        <i class="fa-solid fa-rotate-left"></i> Voir tous les événements
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($liste as $ev): ?>
                    <div class="event-card">
                        <div class="event-image-container">
                            <?php if (!empty($ev['image'])): 
    // build URL and escape filename safely
    $img_filename = $ev['image'];
    $img_url = 'http://localhost/Gcategori/uploads/' . rawurlencode($img_filename);
?>
    <img src="<?= $img_url ?>"
         class="event-image"
         alt="<?= htmlspecialchars($ev['nom_evenement']) ?>"
         onerror="this.src='https://via.placeholder.com/400x220/1e1e1e/666666?text=Image+non+disponible'">
<?php else: ?>
    <img src="https://via.placeholder.com/400x220/1e1e1e/666666?text=Pas+d%27image"
         class="event-image"
         alt="Aucune image">
<?php endif; ?>

                            
                            <?php if ($ev['nombre_inscrits'] < $ev['nombre_places']): ?>
                                <span class="event-badge badge-available">
                                    <i class="fa-solid fa-check"></i> Disponible
                                </span>
                            <?php else: ?>
                                <span class="event-badge badge-full">
                                    <i class="fa-solid fa-xmark"></i> Complet
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="event-content">
                            <h3 class="event-title"><?= htmlspecialchars($ev['nom_evenement']) ?></h3>
                            
                            <div class="event-meta">
                                <div class="event-meta-item">
                                    <i class="fa-regular fa-calendar"></i>
                                    <span><?= date('d/m/Y', strtotime($ev['date_evenement'])) ?></span>
                                </div>
                                <div class="event-meta-item">
                                    <i class="fa-regular fa-user"></i>
                                    <span><?= (int)$ev['nombre_inscrits'] ?> inscrits</span>
                                </div>
                                <div class="event-meta-item">
                                    <i class="fa-solid fa-chair"></i>
                                    <span><?= (int)$ev['nombre_places'] ?> places totales</span>
                                </div>
                            </div>
                            
                            <div class="event-places">
                                <span class="places-info">Places restantes :</span>
                                <span class="places-remaining highlight"><?= (int)$ev['nombre_places'] - (int)$ev['nombre_inscrits'] ?></span>
                            </div>
                            
                            <div class="event-actions">
                                <a class="btn-details" 
                                   href="http://localhost/Gcategori/view/frontoffice/evenement_detail.php?id=<?= $ev['id_evenement'] ?>">
                                    <i class="fa-regular fa-eye"></i> Voir détails
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2024 Supportini.tn - Tous droits réservés</p>
        </div>
    </footer>

    <script>
        // Gestion des erreurs d'images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.event-image');
            images.forEach(img => {
                img.addEventListener('error', function() {
                    this.src = 'https://via.placeholder.com/400x220/1e1e1e/666666?text=Image+non+disponible';
                });
            });
        });
    </script>
</body>
</html>