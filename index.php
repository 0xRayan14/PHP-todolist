<?php


session_start();

$dsn = "sqlite:myDB.db";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, null, null, $options);

$pdo->exec("CREATE TABLE IF NOT EXISTS todo (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                todo TEXT NOT NULL,
                position INTEGER NOT NULL,
                date INTEGER NOT NULL
            )");

$query = $pdo->query("SELECT * FROM todo ORDER BY id ASC");
$allTodos = $query->fetchAll();
$dateTime = date("m-d\\:i:s");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['todo'])) {
        $newTodo = htmlspecialchars($_POST['todo']);
        if (mb_strlen($newTodo) < 3) {
            $_SESSION['errors'] = "Votre todo est trop courte";
        } else {
            $stmt = $pdo->query("SELECT MAX(position) FROM todo");
            $maxPosition = $stmt->fetchColumn();

            $position = $maxPosition + 1;

            $stmt = $pdo->prepare("INSERT INTO todo (todo, position) VALUES (?, ?)");
            $stmt->execute([$newTodo, $position]);

            $_SESSION['validate'] = "Votre todo est validée";
        }
        header('Location: index.php');
        exit();
    }

    if (isset($_POST['delete'])) {
        $deleteId = $_POST['delete'];
        $stmt = $pdo->prepare("DELETE FROM todo WHERE id = ?");
        $stmt->execute([$deleteId]);
        header('Location: index.php');
        exit();
    }

    if (isset($_POST['edit'])) {
        $editId = $_POST['edit'];
        $stmt = $pdo->prepare("SELECT * FROM todo WHERE id = ?");
        $stmt->execute([$editId]);
        $editedTodo = $stmt->fetch();
        $_SESSION['validateModification'] = "Votre todo est modifiée";
    }

    if (isset($_POST['edited_id'])) {
        $editedId = $_POST['edited_id'];
        $updatedTodo = htmlspecialchars($_POST['updated_todo']);
        $stmt = $pdo->prepare("UPDATE todo SET todo = ? WHERE id = ?");
        $stmt->execute([$updatedTodo, $editedId]);
        header('Location: index.php');
        exit();
    }

    if (isset($_POST['up'])) {
        $selectedId = $_POST['up'];

        // Get the position of the selected todo
        $stmt = $pdo->prepare("SELECT position FROM todo WHERE id = ?");
        $stmt->execute([$selectedId]);
        $currentPosition = $stmt->fetchColumn();

        if ($currentPosition > 1) {
            $stmt = $pdo->prepare("SELECT id, position FROM todo WHERE position = ?");
            $stmt->execute([$currentPosition - 1]);

            // Check if the query returned a result
            if ($aboveTodo = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Swap positions
                $stmt = $pdo->prepare("UPDATE todo SET position = ? WHERE id = ?");
                $stmt->execute([$currentPosition - 1, $selectedId]);
                $stmt = $pdo->prepare("UPDATE todo SET position = ? WHERE id = ?");
                $stmt->execute([$currentPosition, $aboveTodo['id']]);

                header('Location: index.php');
                exit();
            }
        }
    }




} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['search'])) {
            $searchTerm = htmlspecialchars($_GET['search']);
            $allTodos = array_filter($allTodos, static function ($todo) use ($searchTerm) {
            return stripos($todo['todo'], $searchTerm) !== false;
            });
    }

    // Trie les tâches par lettres alphabétiques
    if (isset($_GET['sort_AZ'])) {
            usort($allTodos, static function ($a, $b) {
                return strcmp($a['todo'], $b['todo']);
            });
        }

        if (isset($_GET['sort_ZA'])) {
            usort($allTodos, function ($a, $b) {
                return strcmp($b['todo'], $a['todo']);
            });
        }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>TodoList</title>
</head>
<body>

<div class="container">
    <form action="index.php" method="post" class="todo-form">
        <label for="todoInput">Ajouter une todo:</label>
        <input type="text" id="todoInput" name="todo">
        <input type="submit" value="Ajouter">
        <?php if (isset($_SESSION['validate'])): ?>
            <p class="success-message"><?= $_SESSION['validate']?></p>
            <?php unset($_SESSION['validate']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['errors'])): ?>
            <p class="error-message"><?= $_SESSION['errors']?></p>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>
    </form>

    <form action="index.php" method="get" class="search-form">
        <label for="search">Rechercher une todo:</label>
        <input type="text" name="search" id="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <input type="submit" value="Rechercher">
        <label>Trier todos</label>
        <input type="submit" value="A-Z" name="sort_AZ">
        <input type="submit" value="Z-A" name="sort_ZA">
    </form>
    <h2>Toutes les todo's :</h2>


    <ul class="todo-list">
        <?php foreach ($allTodos as $position => $todo): ?>
            <li>
                <?= htmlspecialchars($dateTime, $position + 1) ?> - <?=($todo['todo'])?>

                <form action="index.php" method="post" class="inline-form">
                    <input type="hidden" name="edit" value="<?= $todo['id'] ?>">
                    <input type="submit" value="Edit">
                </form>

                <form action="index.php" method="post" class="inline-form">
                    <input type="hidden" name="delete" value="<?= $todo['id'] ?>">
                    <input type="submit" value="Delete">
                </form>

                <form action="index.php" method="post" class="inline-form">
                    <input type="hidden" name="up" value="<?= $todo['id'] ?>">
                    <input type="submit" value="up">
                </form>

                <form action="index.php" method="post" class="inline-form">
                    <input type="hidden" name="down" value="<?= $todo['id'] ?>">
                    <input type="submit" value="down">
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (isset($editedTodo)): ?>
        <form action="index.php" method="post" class="edit-form">
            <input type="hidden" name="edited_id" value="<?= $editedTodo['id'] ?>">
            <label for="updated_todo">Modifier la todo:</label>
            <input type="text" name="updated_todo" id="updated_todo" value="<?= htmlspecialchars($editedTodo['todo']) ?>">
            <input type="submit" name="update" value="Mettre à jour">
        </form>
    <?php endif; ?>
</div>

</body>
</html>
