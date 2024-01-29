<?php
$path = 'data.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['todo'])) {
    $allTodos = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
    $newTodo = htmlspecialchars($_POST['todo']);
    $allTodos[] = ['id' => count($allTodos) + 1, 'todo' => $newTodo];
    file_put_contents($path, json_encode($allTodos, JSON_PRETTY_PRINT));
}

if (isset($_POST['delete'])) {
    $deleteId = $_POST['delete'];
    $allTodos = file_exists($path) ? json_decode(file_get_contents($path), true) : [];

    $allTodos = array_values(array_filter($allTodos, function($todo) use ($deleteId) {
        return $todo['id'] != $deleteId;
    }));

    file_put_contents($path, json_encode($allTodos, JSON_PRETTY_PRINT));
}

$allTodos = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
?>

<!DOCTYPE HTML>
<html>
<body>

<form action="index.php" method="post">
    <label>
        Ajouter une todo:
        <input type="text" name="todo">
    </label><br>
    <input type="submit" value="Ajouter">
</form>

<form action="index.php" method="post">
    <label>
        <input type="button" value="Tout supprimer">
    </label>
</form>

<h2>Toutes les todo's :</h2>
<ul>
    <?php foreach ($allTodos as $todo): ?>
        <li>
            <?= htmlspecialchars($todo['id']) ?> - <?= htmlspecialchars($todo['todo']) ?>

            <form action="index.php" method="post" style="display: inline;">
                <input type="hidden" name="delete" value="<?= $todo['id'] ?>">
                <input type="submit" value="Supprimer">
            </form>
        </li>
    <?php endforeach; ?>
</ul>

</body>
</html>
