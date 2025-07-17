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

$message = ""; // Message à afficher à l'utilisateur

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $book_id = $_POST["book_id"];
    $action = $_POST["action"];
    $date = $_POST["date"];
    $user = $_SESSION["user"];

    // Récupérer quantité actuelle
    $stmt = $db->prepare("SELECT title, quantity FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if ($book) {
        $quantity = $book["quantity"];
        $title = $book["title"];

        if ($action === "emprunt") {
            if ($quantity > 0) {
                $db->prepare("UPDATE books SET quantity = quantity - 1 WHERE id = ?")->execute([$book_id]);
                $db->prepare("INSERT INTO history (user, book_id, action, date) VALUES (?, ?, 'emprunt', ?)")->execute([$user, $book_id, $date]);
                $message = "✅ Livre emprunté : " . htmlspecialchars($title);
            } else {
                $message = "❌ Le livre \"" . htmlspecialchars($title) . "\" n’est plus disponible.";
            }
        } elseif ($action === "retour") {
            $db->prepare("UPDATE books SET quantity = quantity + 1 WHERE id = ?")->execute([$book_id]);
            $db->prepare("INSERT INTO history (user, book_id, action, date) VALUES (?, ?, 'retour', ?)")->execute([$user, $book_id, $date]);
            $message = "🔁 Livre retourné : " . htmlspecialchars($title);
        }

        // Rafraîchir la liste des livres
        $books = $db->query("SELECT * FROM books")->fetchAll();
    }
}

// Récupérer les 50 dernières actions
$history = $db->query("
    SELECT h.user, b.title AS book_title, h.action, h.date
    FROM history h
    JOIN books b ON h.book_id = b.id
    ORDER BY h.id DESC
    LIMIT 50
")->fetchAll();
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
    <p><a href="logout.php" style="color:red;">🔓 Se déconnecter</a></p>


    <?php if (!empty($message)): ?>
        <p style="color: <?= str_starts_with($message, '❌') ? 'red' : 'green' ?>;">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <h3>📚 Livres disponibles :</h3>
    <ul>
        <?php foreach ($books as $book): ?>
            <li><?= htmlspecialchars($book["title"]) ?> — Stock : <?= $book["quantity"] ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>➕ Emprunter / 🔄 Retourner un livre :</h3>
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

    <h3>🕘 50 derniers emprunts / retours :</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Utilisateur</th>
            <th>Livre</th>
            <th>Action</th>
            <th>Date</th>
        </tr>
        <?php foreach ($history as $entry): ?>
        <tr>
            <td><?= htmlspecialchars($entry["user"]) ?></td>
            <td><?= htmlspecialchars($entry["book_title"]) ?></td>
            <td><?= htmlspecialchars($entry["action"]) ?></td>
            <td><?= htmlspecialchars($entry["date"]) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
