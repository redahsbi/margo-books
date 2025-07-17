<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

$db = new PDO('sqlite:database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"];
    $book_id = $_POST["book_id"];
    $nom = $_POST["nom"];
    $date = $_POST["date"];

    // Vérifier la quantité
    $stmt = $db->prepare("SELECT quantity FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if (!$book) {
        $message = "Livre introuvable.";
    } elseif ($action === "emprunt") {
        if ($book["quantity"] > 0) {
            $db->prepare("UPDATE books SET quantity = quantity - 1 WHERE id = ?")->execute([$book_id]);
            $db->prepare("INSERT INTO history (user, book_id, action, date) VALUES (?, ?, 'emprunt', ?)")->execute([$nom, $book_id, $date]);
            $message = "Livre emprunté avec succès.";
        } else {
            $message = "Ce livre n'est plus disponible.";
        }
    } elseif ($action === "retour") {
        $db->prepare("UPDATE books SET quantity = quantity + 1 WHERE id = ?")->execute([$book_id]);
        $db->prepare("INSERT INTO history (user, book_id, action, date) VALUES (?, ?, 'retour', ?)")->execute([$nom, $book_id, $date]);
        $message = "Livre rendu avec succès.";
    }
}

// Récupérer la liste des livres
$books = $db->query("SELECT * FROM books")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION["user"]); ?> !</h2>
    <h3>Livres disponibles :</h3>
    <ul>
        <?php foreach ($books as $book): ?>
            <li><?php echo $book["title"] . " (stock : " . $book["quantity"] . ")"; ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Emprunter ou rendre un livre</h3>
    <form method="POST">
        <label>Nom : <input name="nom" required></label><br>
        <label>Date : <input name="date" type="date" required></label><br>
        <label>Livre :
            <select name="book_id">
                <?php foreach ($books as $book): ?>
                    <option value="<?php echo $book["id"]; ?>"><?php echo $book["title"]; ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <label>Action :
            <select name="action">
                <option value="emprunt">Emprunter</option>
                <option value="retour">Rendre</option>
            </select>
        </label><br>
        <button type="submit">Valider</button>
    </form>

    <?php if (isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
</body>
</html>
