<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) { header("Location: index.html"); exit(); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ouvrier — Uber-Cueillette</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        html, body { height: 100%; overflow: hidden; }
    </style>
</head>
<body>
    <header>🌿 Uber-Cueillette — Espace Ouvrier</header>
    <div class="dashboard-wrapper">
        <nav>
            <ul>
                <li><a href="ouv_profil.php?id=<?php echo $id; ?>" target="content-frame"> Mon Profil</a></li>
                <li><a href="ouv_offres.php?id=<?php echo $id; ?>" target="content-frame"> Trouver des offres</a></li>
                <li><a href="ouv_candidatures.php?id=<?php echo $id; ?>" target="content-frame"> Mes candidatures</a></li>
                <li><a href="ouv_historique.php?id=<?php echo $id; ?>" target="content-frame"> Historique & Gains</a></li>
                <li class="logout-item"><a href="index.html">⬅ Déconnexion</a></li>
            </ul>
        </nav>
        <main>
            <iframe name="content-frame" src="ouv_offres.php?id=<?php echo $id; ?>"></iframe>
        </main>
    </div>
</body>
</html>