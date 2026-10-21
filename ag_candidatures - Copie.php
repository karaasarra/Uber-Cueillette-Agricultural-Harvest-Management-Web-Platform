<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) { header("Location: index.html"); exit(); }

try {
    $conn = new PDO("mysql:host=localhost;dbname=uber-cueillette;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Erreur de connexion."); }

$msg = '';

// Action : Accepter ou Refuser
if (isset($_GET['action'], $_GET['c_id'])) {
    $status  = ($_GET['action'] === 'accept') ? 'Accepté' : 'Refusé';
    $id_cand = intval($_GET['c_id']);
    // BUG FIX : was "id_candidatur" (missing e)
    $conn->prepare("UPDATE candidature SET decision = ? WHERE id_candidature = ?")
         ->execute([$status, $id_cand]);
    $msg = '<div class="msg-success">✔ Décision enregistrée.</div>';
}

$q = $conn->prepare("
    SELECT c.id_candidature, c.decision,
           ouv.nom, ouv.prenom,
           f.libelle AS fruit,
           (SELECT AVG(c2.note) FROM candidature c2 WHERE c2.id_ouvrier = ouv.id_ouvrier AND c2.note IS NOT NULL) AS moy
    FROM candidature c
    JOIN ouvrier ouv   ON c.id_ouvrier    = ouv.id_ouvrier
    JOIN offre off     ON c.id_offre      = off.id_offre
    JOIN type_fruit f  ON off.id_type_fruit = f.id_type_fruit
    WHERE off.id_agriculteur = ?
    ORDER BY c.id_candidature DESC
");
$q->execute([$id]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="dashboard.css">
    <title>Candidatures reçues</title>
</head>
<body class="inner-body">
    <h2> Candidatures reçues</h2>
    <?php echo $msg; ?>
    <table>
        <thead>
            <tr>
                <th>Ouvrier</th>
                <th>Fruit</th>
                <th>Moy. notes</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) === 0) {
            echo '<tr><td colspan="5" style="text-align:center;padding:20px;color:#999;">Aucune candidature reçue.</td></tr>';
        }
        foreach ($rows as $r):
            $dec = $r['decision'];
            if ($dec === 'Accepté')     $badge = '<span class="badge badge-accept">Accepté</span>';
            elseif ($dec === 'Refusé')  $badge = '<span class="badge badge-refuse">Refusé</span>';
            else                        $badge = '<span class="badge badge-pending">En attente</span>';
            $moy = $r['moy'] !== null ? round($r['moy'], 1).'/10' : '—';
        ?>
        <tr>
            <td><?php echo htmlspecialchars($r['prenom'].' '.$r['nom']); ?></td>
            <td><?php echo htmlspecialchars($r['fruit']); ?></td>
            <td><?php echo $moy; ?></td>
            <td><?php echo $badge; ?></td>
            <td style="white-space:nowrap;">
                <a href="ag_candidatures.php?id=<?php echo $id; ?>&c_id=<?php echo $r['id_candidature']; ?>&action=accept" class="btn btn-light btn-sm"> Accepter</a>
                <a href="ag_candidatures.php?id=<?php echo $id; ?>&c_id=<?php echo $r['id_candidature']; ?>&action=refuse" class="btn btn-red btn-sm" style="margin-left:5px;"> Refuser</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
