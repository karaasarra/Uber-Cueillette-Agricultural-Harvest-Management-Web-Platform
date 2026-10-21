<?php
$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'uber-cueillette';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pseudo = $_POST['pseudo'];
        $pwd    = $_POST['pwd'];

        $query = "SELECT id_ouvrier 
                  FROM ouvrier 
                  WHERE pseudo = :pseudo AND password = :pwd";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':pseudo' => $pseudo,
            ':pwd'    => $pwd
        ]);

        $ouvrier = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ouvrier) {
            header("Location: dashboard_ouv.php?id=" . $ouvrier['id_ouvrier']);
            exit();
        } else {
            $error_message = "Identifiants invalides.";
        }

    } catch (PDOException $e) {
        $error_message = "Une erreur technique est survenue. Veuillez réessayer plus tard.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="formul.css">
    <script src="test1.js" defer></script>
    <title>Connexion Ouvrier</title>
</head>
<body>
    <form action="ouvrierLOG.php" method="POST">
        <fieldset>
            <legend>Connectez-vous à votre compte</legend>

            <?php if (!empty($error_message)): ?>
                <div id="js-errors" style="color:red; font-weight:bold; margin-bottom:10px; text-align:center;">
                    <p style="margin:4px 0;">&#9888; <?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <table>
                <tr>
                    <td><label for="pseudo">Pseudo :</label></td>
                    <td><input type="text" name="pseudo" id="pseudo" required></td>
                </tr>
                <tr>
                    <td><label for="pwd">Mot de passe :</label></td>
                    <td><input type="password" name="pwd" id="pwd" required></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="Login"></td>
                </tr>
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
