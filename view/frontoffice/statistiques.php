<?php
require_once '../../controller/CategorieC.php';

$cc = new CategorieC();
$liste = $cc->listCategories();

// Préparer les données pour Chart.js
$labels = [];
$nbEvents = [];
$states = [];

foreach($liste as $cat){
    $labels[] = $cat['nom_categorie'];
    $nbEvents[] =  (int)$cat['nb_evenements'];
    $states[] = $cat['etat'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Statistiques des Catégories</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('categoriesChart').getContext('2d');
const categoriesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: "Nombre d'événements",
            data: <?= json_encode($nbEvents) ?>,
            backgroundColor: 'rgba(211, 47, 47, 0.7)',
            borderColor: 'rgba(211, 47, 47, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                precision: 0 // pour éviter les valeurs décimales si inutile
            }
        }
    }
});
</script>
<style>
body {
    font-family: Arial, sans-serif;
    background:#000;
    color:#fff;
    padding:40px;
}

.container {
    max-width:900px;
    margin:auto;
    background:#111;
    padding:20px;
    border-radius:10px;
}

h1 {
    text-align:center;
    margin-bottom:20px;
    color:#d32f2f;
}

button.back-btn {
    display: block;
    margin: 20px auto;
    padding: 10px 20px;
    background-color: #d32f2f;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

button.back-btn:hover {
   background-color: #b71c1c;
}

canvas {
    margin: 40px 0;
    background:#fff;
    padding:20px;
    border-radius:8px;
}
</style>
</head>

<body>

<div class="container">
    <!-- Bouton de retour -->
    <button class="back-btn" onclick="window.location.href='http://localhost/Gcategori/view/frontoffice/categories.php'">
        ← Retour aux Catégories
    </button>

    <h1>Statistiques des Catégories</h1>

    <h2>Événements par Catégorie</h2>
    <canvas id="eventsChart"></canvas>

    <h2>Taux Catégories Actives vs Inactives</h2>
    <canvas id="etatChart"></canvas>
</div>


<script>
// ---------------- COURBE DES EVENEMENTS ----------------
new Chart(document.getElementById('eventsChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: "Nombre d'événements",
            data: <?= json_encode($nbEvents) ?>,
            backgroundColor: 'rgba(211,47,47,0.7)',
            borderColor: 'rgba(211,47,47,1)',
            borderWidth: 2
        }]
    }
});

// ---------------- CAMEMBERT ACTIVE / INACTIVE ----------------
const etatData = {
    active: <?= count(array_filter($states, fn($s)=>$s=='active')) ?>,
    inactive: <?= count(array_filter($states, fn($s)=>$s!='active')) ?>
};

new Chart(document.getElementById('etatChart'), {
    type: 'pie',
    data: {
        labels: ["Actives", "Inactives"],
        datasets: [{
            data: [etatData.active, etatData.inactive],
            backgroundColor: [
                'rgba(76,175,80,0.8)',
                'rgba(244,67,54,0.8)'
            ]
        }]
    }
});
</script>

</body>
</html>
