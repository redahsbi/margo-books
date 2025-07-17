<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}

$db = new PDO("sqlite:database.sqlite");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Liste des livres
$books = $db->query("SELECT * FROM books")->fetchAll();

$message = ""; // message de confirmation ou d'erreur

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $book_id = $_POST["book_id"];
    $action = $_POST["action"];
    $date = $_POST["date"];
    $user = $_SESSION["user"];

    // Récupération de la quantité actuelle
    $stmt = $db->prepare("SELECT title, quantity FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if ($book) {
        $quantity = $book["quantity"];
        $title = $book["title"];

        if ($action === "emprunt") {
            if ($quantity > 0) {
                // Diminuer la quantité
                $db->prepare("UPDATE books SET quantity = quantity - 1 WHERE id = ?")->execute([$book_id]);
                $db->prepare("INSERT INTO history (user, book_id, action, date) VALUES (?, ?, 'emprunt', ?)")->execute([$user, $book_id, $date]);
                $message = "✅ Vous avez emprunté : " . htmlspecialchars($title);
            } else {
                $message = "❌ Le livre \"" . htmlspecialchars($title) . "\" n’est plus disponible.";
            }
        } elseif ($action === "retour") {
            // Augmenter la quantité
            $db->prepare("UPDATE books SET quantity = quantity + 1 WHERE id = ?")->execute([$book_id]);
            $db->prepare("INSERT INTO history (user, book_id, action, date) VALUES (?, ?, 'retour', ?)")->execute([$user, $book_id, $date]);
            $message = "🔁 Livre retourné : " . htmlspecialchars($title);
        }

        // Recharge les stocks après modification
        $books = $db->query("SELECT * FROM books")->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Bienvenue, <?= htmlspecialchars($_SESSION["user"]) ?> !</h2>

    <?php if (!empty($message)): ?>
        <p style="color: <?= str_starts_with($message, '❌') ? 'red' : 'green' ?>;">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <h3>Livres disponibles :</h3>
    <ul>
        <?php foreach ($books as $book): ?>
            <li><?= htmlspecialchars($book["title"]) ?> — Stock : <?= $book["quantity"] ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>Emprunter / Retourner un livre :</h3>
    <form method="POST">
        <label>Livre :
            <select name="book_id" required>
                <?php foreach ($books as $book): ?>
                    <option value="<?= $book["id"] ?>"><?= htmlspecialchars($book["title"]) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <label>Action :
            <select name="action" required>
                <option value="emprunt">Emprunter</option>
                <option value="retour">Retourner</option>
            </select>
        </label><br><br>

        <label>Date :
            <input type="date" name="date" required>
        </label><br><br>

        <button type="submit">Valider</button>
    </form>
</body>
</html>
