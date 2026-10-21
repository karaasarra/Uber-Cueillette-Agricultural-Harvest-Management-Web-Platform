<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) { header("Location: index.html"); exit(); }

try {
    $conn = new PDO("mysql:host=localhost;dbname=uber-cueillette;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Erreur de connexion."); }

$msg = '';

// Postuler
if (isset($_GET['postuler'])) {
    $id_offre = intval($_GET['postuler']);
    $check = $conn->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre = ? AND id_ouvrier = ?");
    $check->execute([$id_offre, $id]);
    if ($check->fetchColumn() > 0) {
        // BUG FIX : no alert(), DOM message
        $msg = '<div class="msg-error">⚠ Vous avez déjà postulé à cette offre.</div>';
    } else {
        // BUG FIX : added date_candidature column (NOT NULL in schema)
        $conn->prepare("INSERT INTO candidature (id_offre, id_ouvrier, decision, date_candidature, note, comentaire, remuneration) VALUES (?, ?, 'En attente', NOW(), 0, '', 0)")
             ->execute([$id_offre, $id]);
        $msg = '<div class="msg-success">✔ Candidature envoyée avec succès !</div>';
    }
}

// Filtres
$filtre_fruit = isset($_GET['fruit']) ? intval($_GET['fruit']) : 0;
$filtre_gouv  = isset($_GET['gouv'])  ? intval($_GET['gouv'])  : 0;
$tri_prix     = isset($_GET['tri'])   && $_GET['tri'] === 'desc' ? 'DESC' : 'ASC';

$where  = "WHERE o.date_limite >= CURDATE()";
$params = [];
if ($filtre_fruit) { $where .= " AND o.id_type_fruit = ?";  $params[] = $filtre_fruit; }
if ($filtre_gouv)  { $where .= " AND o.id_gouvernorat = ?"; $params[] = $filtre_gouv; }

$q = $conn->prepare("
    SELECT o.*, f.libelle AS fruit, g.libelle AS gouv, a.nom AS ag_nom
    FROM offre o
    JOIN type_fruit f  ON o.id_type_fruit  = f.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    JOIN agriculteur a ON o.id_agriculteur = a.id_agriculteur
    $where
    ORDER BY o.prix_journee $tri_prix
");
$q->execute($params);
$offres = $q->fetchAll(PDO::FETCH_ASSOC);

// For filter dropdowns
$fruits = $conn->query("SELECT * FROM type_fruit ORDER BY libelle")->fetchAll(PDO::FETCH_ASSOC);
$govs   = $conn->query("SELECT * FROM gouvernorat ORDER BY libelle")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="dashboard.css">
    <title>Offres disponibles</title>
</head>
<body class="inner-body">
    <h2> Offres de récolte disponibles</h2>
    <?php echo $msg; ?>

    <!-- Filtres -->
    <form method="GET" action="ouv_offres.php" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <select name="fruit" class="btn" style="background:white;color:#37633F;border:1.5px solid #c8dfa3;">
            <option value="0"> Tous les fruits</option>
            <?php foreach ($fruits as $f): ?>
                <option value="<?php echo $f['id_type_fruit']; ?>" <?php echo $filtre_fruit == $f['id_type_fruit'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($f['libelle']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="gouv" class="btn" style="background:white;color:#37633F;border:1.5px solid #c8dfa3;">
            <option value="0">📍 Tous les gouvernorats</option>
            <?php foreach ($govs as $g): ?>
                <option value="<?php echo $g['id_gouvernorat']; ?>" <?php echo $filtre_gouv == $g['id_gouvernorat'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($g['libelle']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="tri" class="btn" style="background:white;color:#37633F;border:1.5px solid #c8dfa3;">
            <option value="asc"  <?php echo $tri_prix === 'ASC'  ? 'selected' : ''; ?>> Prix croissant</option>
            <option value="desc" <?php echo $tri_prix === 'DESC' ? 'selected' : ''; ?>> Prix décroissant</option>
        </select>
        <button type="submit" class="btn btn-green">Filtrer</button>
        <a href="ouv_offres.php?id=<?php echo $id; ?>" class="btn" style="background:#eee;color:#37633F;">Réinitialiser</a>
    </form>

    <?php if (count($offres) === 0): ?>
        <div class="card" style="color:#999;text-align:center;">Aucune offre disponible.</div>
    <?php endif; ?>

    <?php foreach ($offres as $r): ?>
    <div class="offer-card">
        <div class="offer-info">
            <b><?php echo htmlspecialchars($r['fruit']); ?></b> — <?php echo htmlspecialchars($r['gouv']); ?>
            <div class="offer-meta">
                👨‍🌾 M. <?php echo htmlspecialchars($r['ag_nom']); ?>
                &nbsp;|&nbsp;  <?php echo htmlspecialchars($r['adresse']); ?>
                &nbsp;|&nbsp;  <?php echo htmlspecialchars($r['date_debut']); ?> → <?php echo htmlspecialchars($r['date_fin']); ?>
                &nbsp;|&nbsp;  <?php echo htmlspecialchars($r['prix_journee']); ?> DT/jour
                &nbsp;|&nbsp;  <?php echo htmlspecialchars($r['nombre_ouvriers']); ?> ouvriers
            </div>
        </div>
        <a href="ouv_offres.php?id=<?php echo $id; ?>&postuler=<?php echo $r['id_offre']; ?>"
           class="btn btn-green btn-sm"
           onclick="return confirm('Postuler à cette offre ?')">Postuler</a>
    </div>
    <?php endforeach; ?>
</body>
</html>
