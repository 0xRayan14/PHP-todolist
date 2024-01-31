<?php
$path = 'data.json';
$allTodos = file_exists($path) ? json_decode(file_get_contents($path), true) : [];

session_start();

$dsn = "sqlite: myDB.db";


$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
$pdo = new PDO($dsn, null, null, $options);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['todo'])) {
        $newTodo = htmlspecialchars($_POST['todo']);
        if (mb_strlen($newTodo) < 3) {
            $_SESSION['errors'] = "Votre todo est trop courte";
        } else {
            $allTodos[] = ['id' => count($allTodos) + 1, 'todo' => $newTodo];
            file_put_contents($path, json_encode($allTodos, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            $_SESSION['validate'] = "Votre todo est validée";
        }
        header('Location: index.php');
        exit();
    }

    if (isset($_POST['delete'])) {
        $deleteId = $_POST['delete'];
        $allTodos = array_values(array_filter($allTodos, function($todo) use ($deleteId) {
            return $todo['id'] != $deleteId;
        }));
        file_put_contents($path, json_encode($allTodos, JSON_PRETTY_PRINT));
        header('Location: index.php');
        exit();
    }

    if (isset($_POST['edit'])) {
        $editId = $_POST['edit'];
        $editedTodo = null;
        foreach ($allTodos as $todo) {
            if ($todo['id'] == $editId) {
                $editedTodo = $todo;
                $_SESSION['validateModification'] = "Votre todo est modifiée";
                break;
            }
        }
    }

    if (isset($_POST['edited_id'])) {
        $editedId = $_POST['edited_id'];
        $updatedTodo = htmlspecialchars($_POST['updated_todo']);
        foreach ($allTodos as &$todo) {
            if ($todo['id'] == $editedId) {
                $todo['todo'] = $updatedTodo;
                break;
            }
        }
        file_put_contents($path, json_encode($allTodos, JSON_PRETTY_PRINT));
        header('Location: index.php');
        exit();
    }

    // Trie les tâches par lettres alphabétiques
    if (isset($_POST['sort_AZ'])) {
        usort($allTodos, function ($a, $b) {
            return strcmp($a['todo'], $b['todo']);
        });
    }

    if (isset($_POST['sort_ZA'])) {
        usort($allTodos, function ($a, $b) {
            return strcmp($b['todo'], $a['todo']);
        });
    }

}
?>

<!DOCTYPE HTML>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>TodoList</title>
</head>
<body>

<form action="index.php" method="post">
    <label>
        Ajouter une todo:
        <input type="text" name="todo">
    </label>
    <input type="submit" value="Ajouter">
</form>

<h2>Toutes les todo's :</h2>
<form action="index.php" method="post">
    <label>
        Trier todos
    </label>
    <input type="submit" value="A-Z" name="sort_AZ">
    <input type="submit" value="Z-A" name="sort_ZA">
</form>
<ul>
    <?php foreach ($allTodos as $position => $todo): ?>
        <li>
            <?= htmlspecialchars($position + 1) ?> - <?= htmlspecialchars($todo['todo']) ?>

            <form action="index.php" method="post" style="display: inline;">
                <input type="hidden" name="edit" value="<?= $todo['id'] ?>">
                <input type="submit" value="Modifier">
            </form>

            <form action="index.php" method="post" style="display: inline;">
                <input type="hidden" name="delete" value="<?= $todo['id'] ?>">
                <input type="submit" value="Supprimer">
            </form>
            <form action="index.php" method="post" style="display: inline;">
                <input type="hidden" name="up" value="<?= $todo['id'] ?>">
                <input type="submit" value="up">
            </form>
            <form action="index.php" method="post" style="display: inline;">
                <input type="hidden" name="down" value="<?= $todo['id'] ?>">
                <input type="submit" value="down">
            </form>
        </li>
    <?php endforeach; ?>
</ul>

<?php if (isset($editedTodo)): ?>
    <form action="index.php" method="post">
        <input type="hidden" name="edited_id" value="<?= $editedTodo['id'] ?>">
        <label>
            Modifier la todo:
            <input type="text" name="updated_todo" value="<?= htmlspecialchars($editedTodo['todo']) ?>">
        </label>
        <input type="submit" name="update" value="Mettre à jour">
    </form>
<?php endif; ?>

</body>
</html>