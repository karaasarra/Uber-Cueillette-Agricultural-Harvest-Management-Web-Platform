<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$conn = new PDO("mysql:host=localhost;dbname=uber-cueillette;charset=utf8mb4", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Récupération du fruit tapé et conversion en majuscules pour le traitement PHP
        $fruit_saisi = strtoupper(trim($_POST['fruit']));
        $gouv_saisi = strtoupper(trim($_POST['gouv']));

        // Recherche du fruit en utilisant UPPER() dans la requête SQL
        $st = $conn->prepare("SELECT id_type_fruit FROM type_fruit WHERE UPPER(libelle) = ?");
        $st->execute([$fruit_saisi]);
        $f_res = $st->fetch();
        
        if ($f_res) {
            $f_id = $f_res['id_type_fruit'];
        } else {
            // Si le fruit n'existe pas, on l'insère directement en majuscules
            $ins = $conn->prepare("INSERT INTO type_fruit (libelle) VALUES (?)");
            $ins->execute([$fruit_saisi]);
            $f_id = $conn->lastInsertId();
        }

        // Même logique pour le Gouvernorat
        $st_g = $conn->prepare("SELECT id_gouvernorat FROM gouvernorat WHERE UPPER(libelle) = ?");
        $st_g->execute([$gouv_saisi]);
        $g_res = $st_g->fetch();
        
        if ($g_res) {
            $g_id = $g_res['id_gouvernorat'];
        } else {
            $ins_g = $conn->prepare("INSERT INTO gouvernorat (libelle) VALUES (?)");
            $ins_g->execute([$gouv_saisi]);
            $g_id = $conn->lastInsertId();
        }

        // Insertion de l'offre
        $sql = "INSERT INTO offre (id_type_fruit, id_gouvernorat, adresse, date_debut, date_fin, nombre_ouvriers, prix_journee, date_limite, id_agriculteur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $conn->prepare($sql)->execute([
            $f_id, 
            $g_id, 
            $_POST['adresse'], 
            $_POST['debut'], 
            $_POST['fin'], 
            $_POST['nb'], 
            $_POST['prix'], 
            $_POST['limite'], 
            $id
        ]);
        
        $conn->commit();
        echo "<script>alert('Offre publiée avec succès !');</script>";
    } catch (Exception $e) { 
        $conn->rollBack(); 
        die("Erreur : " . $e->getMessage()); 
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="dashboard.css">
    <title>Publier une offre</title>
</head>
<body class="inner-body">
    <form method="POST" class="form-card">
        <h2> Publier une nouvelle offre</h2>
        
        <div class="form-group">
            <label>Type de Fruit (ex: Pomme, Orange...)</label>
            <input type="text" name="fruit" placeholder="Entrez le nom du fruit" required>
        </div>

        <div class="form-group">
            <label>Gouvernorat / Ville</label>
            <input type="text" name="gouv" placeholder="Entrez la ville" required>
        </div>

        <div class="form-group">
            <label>Adresse précise</label>
            <input type="text" name="adresse" required>
        </div>

        <div style="display:flex; gap:10px;">
            <div class="form-group" style="flex:1;">
                <label>Date Début</label>
                <input type="date" name="debut" required>
            </div>
            <div class="form-group" style="flex:1;">
                <label>Date Fin</label>
                <input type="date" name="fin" required>
            </div>
        </div>

        <div style="display:flex; gap:10px;">
            <div class="form-group" style="flex:1;">
                <label>Nombre d'ouvriers</label>
                <input type="number" name="nb" min="1" required>
            </div>
            <div class="form-group" style="flex:1;">
                <label>Prix par jour (DT)</label>
                <input type="number" name="prix" min="1" required>
            </div>
        </div>

        <div class="form-group">
            <label>Date limite de candidature</label>
            <input type="date" name="limite" required>
        </div>

        <button type="submit" class="btn btn-green" style="width:100%;">🚀 Publier l'offre</button>
    </form>
</body>
</html>