<?php
require_once __DIR__ . '/../../controller/EvenementC.php';
$ec = new EvenementC();

// Récupérer l'ID depuis l'URL
$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID de l'événement manquant.");
}

// Récupérer les détails de l'événement
$ev = $ec->getEvenementById($id);

if (!$ev) {
    die("Événement non trouvé.");
}

// Chemins pour images
$upload_web_dir = '/categories/uploads/'; // Chemin public pour le navigateur
$upload_dir = realpath(__DIR__ . '/../../uploads/') . '/'; // Chemin serveur pour file_exists()
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($ev['nom_evenement']) ?> | Supportini</title>
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
            padding: 20px 0;
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
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .site-title {
            font-size: 24px;
            font-weight: 700;
            color: white;
        }

        .site-title span {
            color: #ffccbc;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 14px;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Main Content */
        .main-content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }

        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: 30px;
        }

        .breadcrumb a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .breadcrumb a:hover {
            color: var(--primary-red);
        }

        /* Event Detail Card */
        .event-detail-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }

        .event-hero {
            position: relative;
            height: 400px;
            overflow: hidden;
        }

        .event-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .event-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            z-index: 2;
        }

        .badge-available {
            background-color: #4caf50;
        }

        .badge-full {
            background-color: var(--primary-red);
        }

        .event-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            padding: 40px 30px 30px;
            color: white;
        }

        .event-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .event-date {
            font-size: 18px;
            color: #ffccbc;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .event-details {
            padding: 40px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .detail-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            border-left: 4px solid var(--primary-red);
        }

        .detail-icon {
            font-size: 32px;
            margin-bottom: 15px;
            color: var(--primary-red);
        }

        .detail-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-light);
        }

        .detail-label {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
        }

        .description-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary-red);
        }

        .description-content {
            background: rgba(255, 255, 255, 0.03);
            padding: 25px;
            border-radius: 12px;
            line-height: 1.8;
            color: var(--text-light);
        }

        .action-section {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--primary-red);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--dark-red);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(211, 47, 47, 0.4);
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
                gap: 15px;
                text-align: center;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .event-hero {
                height: 300px;
            }

            .event-title {
                font-size: 24px;
            }

            .event-details {
                padding: 25px;
            }

            .details-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .action-section {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .main-header {
                padding: 15px 0;
            }

            .logo-section {
                flex-direction: column;
                gap: 8px;
            }

            .site-title {
                font-size: 20px;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
            }

            .nav-link {
                text-align: center;
            }

            .event-hero {
                height: 250px;
            }

            .event-overlay {
                padding: 30px 20px 20px;
            }

            .event-title {
                font-size: 20px;
            }

            .event-date {
                font-size: 16px;
            }
        }

        /* Placeholder image styling */
        .image-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #2a2a2a, #1a1a1a);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
        }

        .image-placeholder i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--border-color);
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
                <a href="http://localhost/Gcategori/view/frontoffice/evenements.php" class="nav-link">
                    <i class="fa-solid fa-calendar-days"></i> Événements
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
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="http://localhost/eventsCopy/view/frontoffice/evenements.php">
                <i class="fa-solid fa-arrow-left"></i> Retour aux événements
            </a>
        </div>

        <!-- Event Detail Card -->
        <div class="event-detail-card">
            <div class="event-hero">
                <?php
// $upload_dir doit être le chemin SUR LE SERVEUR (ex : /var/www/htdocs/Gcategori/uploads/)
$server_path = $upload_dir . ($ev['image'] ?? '');
$browser_url = 'http://localhost/Gcategori/uploads/' . rawurlencode($ev['image'] ?? '');
?>
<?php if (!empty($ev['image']) && file_exists($server_path)): ?>
    <img src="<?= $browser_url ?>"
         class="event-image"
         alt="<?= htmlspecialchars($ev['nom_evenement']) ?>"
         onerror="this.onerror=null; this.src='https://via.placeholder.com/1200x400/1e1e1e/666666?text=Image+non+disponible'">
<?php else: ?>
    <div class="image-placeholder">
        <i class="fa-regular fa-image"></i>
        <span>Pas d'image disponible</span>
    </div>
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
                
                <div class="event-overlay">
                    <h1 class="event-title"><?= htmlspecialchars($ev['nom_evenement']) ?></h1>
                    <div class="event-date">
                        <i class="fa-regular fa-calendar"></i>
                        <?= date('d/m/Y', strtotime($ev['date_evenement'])) ?>
                    </div>
                </div>
            </div>
            
            <div class="event-details">
                <!-- Statistics Grid -->
                <div class="details-grid">
                    <div class="detail-card">
                        <div class="detail-icon">
                            <i class="fa-solid fa-chair"></i>
                        </div>
                        <div class="detail-value"><?= (int)$ev['nombre_places'] ?></div>
                        <div class="detail-label">Places totales</div>
                    </div>
                    
                    <div class="detail-card">
                        <div class="detail-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="detail-value"><?= (int)$ev['nombre_inscrits'] ?></div>
                        <div class="detail-label">Personnes inscrites</div>
                    </div>
                    
                    <div class="detail-card">
                        <div class="detail-icon">
                            <i class="fa-solid fa-ticket"></i>
                        </div>
                        <div class="detail-value"><?= (int)$ev['nombre_places'] - (int)$ev['nombre_inscrits'] ?></div>
                        <div class="detail-label">Places restantes</div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="description-section">
                    <h2 class="section-title">
                        <i class="fa-regular fa-file-lines"></i> Description
                    </h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($ev['description'] ?? 'Aucune description disponible pour cet événement.')) ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-section">
                    <?php if ($ev['nombre_inscrits'] < $ev['nombre_places']): ?>
                        <a href="#" class="btn btn-primary">
                            <i class="fa-solid fa-ticket"></i> Réserver maintenant
                        </a>
                    <?php else: ?>
                        <button class="btn btn-primary" disabled style="opacity: 0.6; cursor: not-allowed;">
                            <i class="fa-solid fa-xmark"></i> Complet - Réservation fermée
                        </button>
                    <?php endif; ?>
                    
                    <a href="http://localhost/Gcategori/view/frontoffice/evenements.php" class="btn btn-outline">
                        <i class="fa-solid fa-arrow-left"></i> Retour aux événements
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <p class="footer-text">&copy; 2024 Supportini.tn - Tous droits réservés</p>
        </div>
    </footer>

    <script>
        // Gestion améliorée des erreurs d'images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.event-image');
            images.forEach(img => {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                    const placeholder = document.createElement('div');
                    placeholder.className = 'image-placeholder';
                    placeholder.innerHTML = '<i class="fa-regular fa-image"></i><span>Image non disponible</span>';
                    this.parentElement.appendChild(placeholder);
                });
            });
        });
    </script>
</body>
</html>