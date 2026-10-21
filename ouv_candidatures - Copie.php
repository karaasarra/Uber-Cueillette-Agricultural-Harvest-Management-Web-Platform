<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) { header("Location: index.html"); exit(); }

try {
    $conn = new PDO("mysql:host=localhost;dbname=uber-cueillette;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Erreur de connexion."); }

$q = $conn->prepare("
    SELECT c.decision, c.date_candidature,
           f.libelle AS fruit, g.libelle AS gouv,
           o.prix_journee, o.date_debut, o.date_fin
    FROM candidature c
    JOIN offre o       ON c.id_offre      = o.id_offre
    JOIN type_fruit f  ON o.id_type_fruit = f.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    WHERE c.id_ouvrier = ?
    ORDER BY c.date_candidature DESC
");
$q->execute([$id]);
$rows = $q->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="dashboard.css">
    <title>Mes candidatures</title>
</head>
<body class="inner-body">
    <h2> Mes candidatures et statuts</h2>
    <table>
        <thead>
            <tr>
                <th>Récolte</th>
                <th>Lieu</th>
                <th>Période</th>
                <th>Prix/Jour</th>
                <th>Date candidature</th>
                <th>Décision</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($rows) === 0): ?>
            <tr><td colspan="6" style="text-align:center;padding:20px;color:#999;">Aucune candidature.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r):
            $dec = $r['decision'];
            if ($dec === 'Accepté')     $badge = '<span class="badge badge-accept">Accepté</span>';
            elseif ($dec === 'Refusé')  $badge = '<span class="badge badge-refuse">Refusé</span>';
            else                        $badge = '<span class="badge badge-pending">En attente</span>';
        ?>
        <tr>
            <td><?php echo htmlspecialchars($r['fruit']); ?></td>
            <td><?php echo htmlspecialchars($r['gouv']); ?></td>
            <td><?php echo htmlspecialchars($r['date_debut']); ?> → <?php echo htmlspecialchars($r['date_fin']); ?></td>
            <td><?php echo htmlspecialchars($r['prix_journee']); ?> DT</td>
            <td><?php echo htmlspecialchars(substr($r['date_candidature'], 0, 10)); ?></td>
            <td><?php echo $badge; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
