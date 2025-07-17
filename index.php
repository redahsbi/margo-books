<?php
session_start();

// === Remplis ici avec tes vraies infos ===
$host = 'sql303.infinityfree.com ';       // ex : sql307.epizy.com
$dbname = 'if0_39493991_margo ';
$user = 'if0_39493991';
$pass = 'tuSJ8GtBHEUqW';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = $_POST["login"];
    $password = $_POST["password"];

    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT * FROM users WHERE login = ? AND password = ?");
        $stmt->execute([$login, $password]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION["user"] = $user["login"];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Identifiants incorrects.";
        }

    } catch (PDOException $e) {
        $error = "Erreur de connexion à la base : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Margo Books</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Connexion à Margo Books</h2>
    <form method="POST">
        <input type="text" name="login" placeholder="Login" required><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br>
        <button type="submit">Se connecter</button>
    </form>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>
