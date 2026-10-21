<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) { header("Location: index.html"); exit(); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Agriculteur — Uber-Cueillette</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        html, body { height: 100%; overflow: hidden; }
    </style>
</head>
<body>
    <header>🌾 Uber-Cueillette — Espace Agriculteur</header>
    <div class="dashboard-wrapper">
        <nav>
            <ul>
                <li><a href="ag_profil.php?id=<?php echo $id; ?>" target="content-frame"> Mon Profil</a></li>
                <li><a href="ag_publier.php?id=<?php echo $id; ?>" target="content-frame"> Publier une offre</a></li>
                <li><a href="ag_mes_offres.php?id=<?php echo $id; ?>" target="content-frame"> Mes offres</a></li>
                <li><a href="ag_candidatures.php?id=<?php echo $id; ?>" target="content-frame"> Candidatures reçues</a></li>
                <li><a href="ag_evaluation.php?id=<?php echo $id; ?>" target="content-frame"> Noter les ouvriers</a></li>
                <li class="logout-item"><a href="index.html">⬅ Déconnexion</a></li>
            </ul>
        </nav>
        <main>
            <iframe name="content-frame" src="ag_profil.php?id=<?php echo $id; ?>"></iframe>
        </main>
    </div>
</body>
</html>