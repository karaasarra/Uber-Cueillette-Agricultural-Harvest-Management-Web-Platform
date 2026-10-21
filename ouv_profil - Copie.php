<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) { header("Location: index.html"); exit(); }

try {
    $conn = new PDO("mysql:host=localhost;dbname=uber-cueillette;charset=utf8mb4", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("SELECT * FROM ouvrier WHERE id_ouvrier = ?");
    $stmt->execute([$id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) { header("Location: index.html"); exit(); }
} catch (PDOException $e) { die("Erreur de connexion."); }

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = "nom=?, prenom=?, cin=?, email=?, discription=?";
    $params = [$_POST['nom'], $_POST['prenom'], $_POST['cin'], $_POST['email'], $_POST['desc']];

    if (!empty($_POST['pwd'])) {
        $fields .= ", password=?";
        $params[] = $_POST['pwd'];
    }
    $params[] = $id;
    $conn->prepare("UPDATE ouvrier SET $fields WHERE id_ouvrier=?")->execute($params);

    if (!empty($_FILES['photo']['tmp_name'])) {
        $conn->prepare("UPDATE ouvrier SET photo=? WHERE id_ouvrier=?")
             ->execute([file_get_contents($_FILES['photo']['tmp_name']), $id]);
    }

    $stmt2 = $conn->prepare("SELECT * FROM ouvrier WHERE id_ouvrier = ?");
    $stmt2->execute([$id]);
    $u = $stmt2->fetch(PDO::FETCH_ASSOC);
    $msg = '<div class="msg-success">✔ Profil mis à jour !</div>';
}

$photo = !empty($u['photo'])
    ? 'data:image/jpeg;base64,'.base64_encode($u['photo'])
    : 'https://ui-avatars.com/api/?name='.urlencode($u['prenom'].' '.$u['nom']).'&background=67944C&color=fff&size=130';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="dashboard.css">
    <title>Mon Profil</title>
</head>
<body class="inner-body">
    <?php echo $msg; ?>
    <form method="POST" action="ouv_profil.php?id=<?php echo $id; ?>" enctype="multipart/form-data" class="form-card">
        <h2 style="text-align:center;margin-bottom:16px;"> Mon Profil Ouvrier</h2>

        <div style="text-align:center;margin-bottom:18px;">
            <img src="<?php echo $photo; ?>" id="preview" class="p-img"><br>
            <input type="file" name="photo" id="photo-input" accept="image/*" style="margin-top:8px;">
        </div>

        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" value="<?php echo htmlspecialchars($u['nom']); ?>" required>
        </div>
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" value="<?php echo htmlspecialchars($u['prenom']); ?>" required>
        </div>
        <div class="form-group">
            <label>CIN</label>
            <input type="text" name="cin" value="<?php echo htmlspecialchars($u['cin']); ?>" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($u['email']); ?>" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="desc" rows="3"><?php echo htmlspecialchars($u['discription'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label>Mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" name="pwd" placeholder="Nouveau mot de passe...">
        </div>

        <button type="submit" class="btn btn-green" style="width:100%;margin-top:10px;"> Enregistrer</button>
    </form>

    <script>
    document.getElementById('photo-input').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
    </script>
</body>
</html>
