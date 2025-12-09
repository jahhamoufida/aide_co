<?php
require_once __DIR__ . '/../../controller/EvenementC.php';
$ec = new EvenementC();
$liste = $ec->listEvenements();

// ---------------- CALCULS ----------------
$totalEvents = count($liste);
$totalPlaces = 0;
$totalInscrits = 0;
$availableEvents = 0;

$labels = [];
$inscritsData = [];
$placesData = [];

foreach ($liste as $ev) {
    $totalPlaces += $ev['nombre_places'];
    $totalInscrits += $ev['nombre_inscrits'];

    if ($ev['nombre_inscrits'] < $ev['nombre_places']) {
        $availableEvents++;
    }

    // données pour graphes
    $labels[] = $ev['nom_evenement'];
    $inscritsData[] = $ev['nombre_inscrits'];
    $placesData[] = $ev['nombre_places'];
}

$fillPercent = $totalPlaces > 0 ? round(($totalInscrits / $totalPlaces) * 100) : 0;

// top events
$topEvents = $liste;
usort($topEvents, fn($a,$b)=> $b['nombre_inscrits'] - $a['nombre_inscrits']);
$topEvents = array_slice($topEvents, 0, 5);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques des événements</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { background: #121212; color: #f5f5f5; font-family: 'Montserrat', sans-serif; }
        .container { max-width: 1100px; margin: 30px auto; padding: 20px; }
        .title { font-size: 32px; font-weight: 700; margin-bottom: 30px; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit,minmax(240px,1fr));
            gap: 25px;
        }
        .card {
            background: #1e1e1e; border-radius: 12px; padding: 25px; text-align: center;
            border-left: 4px solid #d32f2f; box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .card i { font-size: 36px; margin-bottom: 15px; color: #d32f2f; }
        .card-value { font-size: 32px; font-weight: 700; margin-bottom: 5px; }
        .card-label { color: #aaaaaa; font-size: 15px; }

        .chart-box {
    background: #1e1e1e;
    padding: 15px;             /* PLUS PETIT */
    border-radius: 10px;
    margin-top: 25px;          /* RÉDUIT */
    box-shadow: 0 3px 12px rgba(0,0,0,0.25);

    max-width: 700px;          /* LARGEUR MAX */
    margin-left: auto;
    margin-right: auto;
}
    .chart-box canvas {
    max-height: 260px !important;     /* HAUTEUR RÉDUITE */
    height: 260px !important;
}
        table { width: 100%; margin-top: 40px; border-collapse: collapse; }
        table th, table td { padding: 14px; border-bottom: 1px solid #333; text-align: left; color: #ddd; }
        table th { background: #1e1e1e; }
        a.back-btn { display: inline-block; margin-top: 30px; color: #d32f2f; font-weight: 600; text-decoration: none; }
        a.back-btn:hover { text-decoration: underline; }
    </style>
</head>

<body>
<div class="container">

    <h1 class="title"><i class="fa-solid fa-chart-column"></i> Statistiques des Événements</h1>

    <!-- CARDS -->
    <div class="stats-grid">
        <div class="card"><i class="fa-solid fa-calendar-days"></i><div class="card-value"><?= $totalEvents ?></div><div class="card-label">Événements total</div></div>
        <div class="card"><i class="fa-solid fa-ticket"></i><div class="card-value"><?= $totalPlaces ?></div><div class="card-label">Places totales</div></div>
        <div class="card"><i class="fa-solid fa-users"></i><div class="card-value"><?= $totalInscrits ?></div><div class="card-label">Inscrits total</div></div>
        <div class="card"><i class="fa-solid fa-percent"></i><div class="card-value"><?= $fillPercent ?>%</div><div class="card-label">Taux de remplissage moyen</div></div>
        <div class="card"><i class="fa-solid fa-check-circle"></i><div class="card-value"><?= $availableEvents ?></div><div class="card-label">Événements disponibles</div></div>
    </div>

    <!-- GRAPHIQUE 1 : COURBE INSCRITS -->
    <div class="chart-box">
        <h2>Évolution des inscrits par événement</h2>
        <canvas id="chartInscrits"></canvas>
    </div>

    <!-- GRAPHIQUE 2 : COURBE PLACES vs INSCRITS -->
    <div class="chart-box">
        <h2>Comparaison : Places disponibles vs Inscrits</h2>
        <canvas id="chartComparaison"></canvas>
    </div>

    <!-- GRAPHIQUE 3 : PIE CHART -->
    <div class="chart-box">
        <h2>Répartition : Disponibles / Remplis</h2>
        <canvas id="pieChart"></canvas>
    </div>

    <h2 style="margin-top:40px;">Top 5 des événements les plus populaires</h2>
    <table>
        <tr><th>Nom</th><th>Date</th><th>Inscrits</th><th>Places</th></tr>
        <?php foreach ($topEvents as $ev): ?>
        <tr>
            <td><?= htmlspecialchars($ev['nom_evenement']) ?></td>
            <td><?= date('d/m/Y', strtotime($ev['date_evenement'])) ?></td>
            <td><?= $ev['nombre_inscrits'] ?></td>
            <td><?= $ev['nombre_places'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <a href="evenements.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Retour à la liste</a>
</div>

<script>
// ------------------ DONNÉES PHP → JS -------------------
const labels = <?= json_encode($labels) ?>;
const inscritsData = <?= json_encode($inscritsData) ?>;
const placesData = <?= json_encode($placesData) ?>;
const filled = <?= $totalInscrits ?>;
const notFilled = <?= max(0, $totalPlaces - $totalInscrits) ?>;

// ------------------ GRAPHIQUE 1 ------------------------
new Chart(document.getElementById('chartInscrits'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Inscrits',
            data: inscritsData,
            borderColor: '#d32f2f',
            borderWidth: 2,
            fill: false,
            tension: 0.3,
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: 'white' }}},
        scales: {
            x: { ticks: { color: '#ccc' }},
            y: { ticks: { color: '#ccc' }}
        }
    }
});

// ------------------ GRAPHIQUE 2 ------------------------
new Chart(document.getElementById('chartComparaison'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Places',
                data: placesData,
                borderColor: '#888',
                borderWidth: 2,
                fill: false
            },
            {
                label: 'Inscrits',
                data: inscritsData,
                borderColor: '#d32f2f',
                borderWidth: 2,
                fill: false
            }
        ]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: 'white' }}},
        scales: {
            x: { ticks: { color: '#ccc' }},
            y: { ticks: { color: '#ccc' }}
        }
    }
});

// ------------------ PIE CHART --------------------------
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: ['Occupées', 'Disponibles'],
        datasets: [{
            data: [filled, notFilled],
            backgroundColor: ['#d32f2f', '#444']
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: 'white' }}},
    }
});

</script>

</body>
</html>
