<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) { header("Location: index.html"); exit(); }

try {
    $conn = new PDO("mysql:host=localhost;dbname=uber-cueillette;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Erreur de connexion."); }

$msg = '';

// Suppression sécurisée
if (isset($_GET['del'])) {
    $id_offre = intval($_GET['del']);
    $check = $conn->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre = ?");
    $check->execute([$id_offre]);
    if ($check->fetchColumn() > 0) {
        // BUG FIX : no alert(), use DOM message
        $msg = '<div class="msg-error">⚠ Impossible de supprimer : des candidatures existent pour cette offre.</div>';
    } else {
        $conn->prepare("DELETE FROM offre WHERE id_offre = ? AND id_agriculteur = ?")
             ->execute([$id_offre, $id]);
        $msg = '<div class="msg-success">🗑 Offre supprimée avec succès.</div>';
    }
}

$q = $conn->prepare("
    SELECT o.*, f.libelle AS fruit, g.libelle AS gouv,
           (SELECT COUNT(*) FROM candidature WHERE id_offre = o.id_offre) AS total_c
    FROM offre o
    JOIN type_fruit f  ON o.id_type_fruit  = f.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    WHERE o.id_agriculteur = ?
    ORDER BY o.date_debut DESC
");
$q->execute([$id]);
$rows = $q->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="dashboard.css">
    <title>Mes offres</title>
</head>
<body class="inner-body">
    <h2> Mes offres de récolte</h2>
    <?php echo $msg; ?>
    <div style="max-width:900px;">
        <table>
            <thead>
                <tr>
                    <th>Fruit</th>
                    <th>Lieu</th>
                    <th>Période</th>
                    <th>Prix/Jour</th>
                    <th>Date limite</th>
                    <th>Statut</th>
                    <th>Postulants</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) === 0): ?>
                <tr><td colspan="8" style="text-align:center;padding:20px;color:#999;">Aucune offre publiée.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r):
                $today  = date('Y-m-d');
                $isOpen = ($r['date_limite'] >= $today);
                $statut = $isOpen
                    ? '<span class="badge badge-open">Ouverte</span>'
                    : '<span class="badge badge-closed">Clôturée</span>';
            ?>
            <tr>
                <td><?php echo htmlspecialchars($r['fruit']); ?></td>
                <td><?php echo htmlspecialchars($r['gouv']); ?></td>
                <td><?php echo htmlspecialchars($r['date_debut']); ?> → <?php echo htmlspecialchars($r['date_fin']); ?></td>
                <td><?php echo htmlspecialchars($r['prix_journee']); ?> DT</td>
                <td><?php echo htmlspecialchars($r['date_limite']); ?></td>
                <td><?php echo $statut; ?></td>
                <td>
                    <span class="badge <?php echo ($r['total_c'] > 0) ? 'badge-accept' : 'badge-pending'; ?>">
                        <?php echo $r['total_c']; ?> postulant(s)
                    </span>
                </td>
                <td>
                    <?php if ($r['total_c'] == 0): ?>
                        <a href="ag_mes_offres.php?id=<?php echo $id; ?>&del=<?php echo $r['id_offre']; ?>"
                           class="btn btn-red btn-sm"
                           onclick="return confirm('Supprimer cette offre ?')"> Supprimer</a>
                    <?php else: ?>
                        <span style="color:#999;font-size:0.8em;">🔒 Verrouillée</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
