<?php
$servername = 'localhost'; $username = 'root'; $password = ''; $dbname = 'uber-cueillette';
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $photoBin = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $photoBin = file_get_contents($_FILES['photo']['tmp_name']);
        }

        $sql = "INSERT INTO ouvrier (nom, prenom, cin, email, photo, discription, pseudo, password) 
                VALUES (:nom, :prenom, :cin, :email, :photo, :desc, :pseudo, :pwd)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nom'    => $_POST['nom'],
            ':prenom' => $_POST['prenom'],
            ':cin'    => $_POST['cin'],
            ':email'  => $_POST['email'],
            ':photo'  => $photoBin,
            ':desc'   => $_POST['description'],
            ':pseudo' => $_POST['pseudo'],
            ':pwd'    => $_POST['pwd']
        ]);

        $newId = $conn->lastInsertId();

        header("Location: dashboard_ou.php?id=" . $newId);
        exit();

    } catch(PDOException $e) {
        $error_message = "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="formul.css">
    <script src="test1.js" defer></script>
    <title>Inscription Ouvrier</title>
</head>
<body>
    <form action="signINOUV.php" method="POST" enctype="multipart/form-data">
        <fieldset>
            <legend>Inscription Ouvrier</legend>

            <?php if (!empty($error_message)): ?>
                <div id="js-errors" style="color:red; font-weight:bold; margin-bottom:10px; text-align:center;">
                    <p style="margin:4px 0;">&#9888; <?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <table>
                <tr><td><label>Nom :</label></td><td><input type="text" name="nom" required></td></tr>
                <tr><td><label>Prénom :</label></td><td><input type="text" name="prenom" required></td></tr>
                <tr><td><label>Photo :</label></td><td><input type="file" name="photo" accept="image/*" required></td></tr>
                <tr><td><label>CIN :</label></td><td><input type="text" name="cin" required></td></tr>
                <tr><td><label>Email :</label></td><td><input type="email" name="email" required></td></tr>
                <tr><td><label>Description :</label></td><td><input type="text" name="description"></td></tr>
                <tr><td><label>Pseudo :</label></td><td><input type="text" name="pseudo" required></td></tr>
                <tr><td><label>Mot de passe :</label></td><td><input type="password" name="pwd" required></td></tr>
                <tr><td></td><td><input type="submit" value="S'inscrire"></td></tr>
            </table>
        </fieldset>
    </form>

    <a href="index.html" class="back-arrow-link">
        <img src="left.png" alt="Retour" class="back-arrow-img">
    </a>

    <style>
    .back-arrow-link {
        position: fixed; top: 20px; left: 20px;
        z-index: 1000; transition: transform 0.3s ease;
    }
    .back-arrow-link:hover { transform: scale(1.1); }
    .back-arrow-img {
        width: 50px; height: auto; cursor: pointer;
        filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.3));
    }
    </style>
</body>
</html>
