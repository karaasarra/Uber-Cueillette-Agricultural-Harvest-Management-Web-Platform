<?php
session_start(); // ✅ ADDED

$servername = 'localhost'; $username = 'root'; $password = ''; $dbname = 'uber-cueillette';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $photoBin = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $photoBin = file_get_contents($_FILES['photo']['tmp_name']);
        }

        $sql = "INSERT INTO agriculteur (nom, prenom, cin, email, photo, adresse, pseudo, password) 
                VALUES (:nom, :prenom, :cin, :email, :photo, :adresse, :pseudo, :pwd)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nom'    => $_POST['nom'],
            ':prenom' => $_POST['prenom'],
            ':cin'    => $_POST['cin'],
            ':email'  => $_POST['email'],
            ':photo'  => $photoBin,
            ':adresse'   => $_POST['adresse'],
            ':pseudo' => $_POST['pseudo'],
            ':pwd'    => $_POST['pwd']
        ]);

        // ✅ After insert, fetch the new user to fill the session
        $newId = $conn->lastInsertId();

        $query = "SELECT id_agriculteur, nom, prenom, cin, email, photo, discription, pseudo 
                  FROM agriculteur 
                  WHERE id_agriculteur = :id";
        $stmt2 = $conn->prepare($query);
        $stmt2->execute([':id' => $newId]);
        $agriculteur = $stmt2->fetch(PDO::FETCH_ASSOC);

        // ✅ Fill session exactly like login
        $_SESSION['id']          = $agriculteur['id_agriculteur'];
        $_SESSION['nom']         = $agriculteur['nom'];
        $_SESSION['prenom']      = $agriculteur['prenom'];
        $_SESSION['cin']         = $agriculteur['cin'];
        $_SESSION['email']       = $agriculteur['email'];
        $_SESSION['pseudo']      = $agriculteur['pseudo'];
        $_SESSION['photo']       = $agriculteur['photo'];
        $_SESSION['adresse']     = $agriculteur['adresse'];
        $_SESSION['role']        = 'agriculteur';

        // ✅ Redirect to dashboard
        header("Location: dashboard_agriculteur.php");
        exit();

    } catch(PDOException $e) {
        $message = "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="formul.css">
    <script src="test1.js"></script>
    <title>Inscription Agriculteur</title>
</head>
<body>
    <form action="signAG.php" method="POST" enctype="multipart/form-data">
        <fieldset>
            <legend>Inscription Agriculteur</legend>
            <?php echo $message; ?>
            <table>
                <tr><td><label>Nom :</label></td><td><input type="text" name="nom" required></td></tr>
                <tr><td><label>Prénom :</label></td><td><input type="text" name="prenom" required></td></tr>
                <tr><td><label>Photo :</label></td><td><input type="file" name="photo" accept="image/*" required></td></tr>
                <tr><td><label>CIN :</label></td><td><input type="text" name="cin" required></td></tr>
                <tr><td><label>Email :</label></td><td><input type="email" name="email" required></td></tr>
                <tr><td><label>Address :</label></td><td><input type="text" name="description"></td></tr>
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
