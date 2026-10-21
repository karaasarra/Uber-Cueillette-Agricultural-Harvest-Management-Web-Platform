<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) { header("Location: index.html"); exit(); }

try {
    $conn = new PDO("mysql:host=localhost;dbname=uber-cueillette;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Erreur de connexion."); }

$q = $conn->prepare("
    SELECT c.note, c.comentaire, 
           f.libelle AS fruit, g.libelle AS gouv,
           o.date_debut, o.date_fin, o.prix_journee
    FROM candidature c
    JOIN offre o       ON c.id_offre       = o.id_offre
    JOIN type_fruit f  ON o.id_type_fruit  = f.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    WHERE c.id_ouvrier = ? AND c.decision = 'Accepté'
    ORDER BY o.date_fin DESC
");
$q->execute([$id]);
$rows = $q->fetchAll(PDO::FETCH_ASSOC);

$total_gagne_global = 0;
foreach ($rows as $row) {
    $d1 = new DateTime($row['date_debut']);
    $d2 = new DateTime($row['date_fin']);
    $jours = $d1->diff($d2)->days + 1;
    $total_gagne_global += ($jours * $row['prix_journee']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="dashboard.css">
    <title>Historique & Gains</title>
</head>
<body class="inner-body">
    <h2> Historique de travail & Revenus</h2>

    <div style="background:#e6f4d0;padding:18px 22px;border-radius:10px;margin-bottom:20px;border-left:5px solid #67944C;">
        <h3 style="margin:0;color:#37633F;"> Total gagné à ce jour : <?php echo $total_gagne_global; ?> DT</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th>Récolte</th>
                <th>Lieu</th>
                <th>Période</th>
                <th>Note /10</th>
                <th>Commentaire</th>
                <th>Gain</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($rows) === 0): ?>
            <tr><td colspan="6" style="text-align:center;padding:20px;color:#999;">Aucun chantier terminé.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $r): 
                $date_deb = new DateTime($r['date_debut']);
                $date_fin = new DateTime($r['date_fin']);
                $nb_jours = $date_deb->diff($date_fin)->days + 1;
                $gain_actuel = $nb_jours * $r['prix_journee'];
            ?>
            <tr>
                <td><?php echo htmlspecialchars($r['fruit']); ?></td>
                <td><?php echo htmlspecialchars($r['gouv']); ?></td>
                <td>
                    <?php echo htmlspecialchars($r['date_debut']); ?> → <?php echo htmlspecialchars($r['date_fin']); ?>
                    <br><small>(<?php echo $nb_jours; ?> j x <?php echo $r['prix_journee']; ?> DT)</small>
                </td>
                <td style="font-weight:bold;color:#d4a017;">
                    <?php echo ($r['note'] > 0) ? $r['note'].'/10' : '<span style="color:#999;">—</span>'; ?>
                </td>
                <td><em><?php echo htmlspecialchars($r['comentaire'] ?? '—'); ?></em></td>
                <td style="color:green;font-weight:bold;">
                    <?php echo $gain_actuel; ?> DT
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>