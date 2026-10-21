<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) { header("Location: index.html"); exit(); }

try {
    $conn = new PDO("mysql:host=localhost;dbname=uber-cueillette;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Erreur de connexion."); }

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->prepare("UPDATE candidature SET note = ?, comentaire = ?, remuneration = ? WHERE id_candidature = ?")
         ->execute([intval($_POST['note']), $_POST['com'], floatval($_POST['rem']), intval($_POST['c_id'])]);
    // BUG FIX : no alert(), use DOM message
    $msg = '<div class="msg-success">✔ Évaluation enregistrée.</div>';
}

// BUG FIX : was "id_agriculteure" (typo with extra e) — now id_agriculteur
// Also removed the AND c.note = 0 filter so already-rated workers still show
$q = $conn->prepare("
    SELECT c.id_candidature, c.note, c.comentaire, c.remuneration,
           ouv.nom, ouv.prenom, f.libelle AS fruit, off.date_fin
    FROM candidature c
    JOIN ouvrier ouv   ON c.id_ouvrier     = ouv.id_ouvrier
    JOIN offre off     ON c.id_offre       = off.id_offre
    JOIN type_fruit f  ON off.id_type_fruit = f.id_type_fruit
    WHERE off.id_agriculteur = ? AND c.decision = 'Accepté'
    ORDER BY off.date_fin DESC
");
$q->execute([$id]);
$rows = $q->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="dashboard.css">
    <title>Notation des ouvriers</title>
</head>
<body class="inner-body">
    <h2> Notation des ouvriers</h2>
    <?php echo $msg; ?>
    <?php if (count($rows) === 0): ?>
        <div class="card" style="color:#999;text-align:center;">Aucun ouvrier accepté à évaluer.</div>
    <?php endif; ?>
    <?php foreach ($rows as $r): ?>
    <form method="POST" action="ag_evaluation.php?id=<?php echo $id; ?>" class="form-card" style="margin-bottom:15px; border-left:5px solid #67944C; max-width:600px;">
        <input type="hidden" name="c_id" value="<?php echo intval($r['id_candidature']); ?>">
        <p style="margin-bottom:12px;">
            <strong><?php echo htmlspecialchars($r['prenom'].' '.$r['nom']); ?></strong>
            — <?php echo htmlspecialchars($r['fruit']); ?>
            (fin : <?php echo htmlspecialchars($r['date_fin']); ?>)
        </p>
        <div style="display:flex; gap:10px;">
            <div class="form-group" style="flex:1;">
                <label>Note /10</label>
                <input type="number" name="note" min="0" max="10" value="<?php echo intval($r['note']); ?>" required>
            </div>
            <div class="form-group" style="flex:1;">
                <label>Rémunération (DT)</label>
                <input type="number" name="rem" min="0" step="0.5" value="<?php echo floatval($r['remuneration']); ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label>Commentaire</label>
            <textarea name="com" rows="2"><?php echo htmlspecialchars($r['comentaire'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-green"> Enregistrer</button>
    </form>
    <?php endforeach; ?>
</body>
</html>
