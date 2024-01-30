<?php
$path = 'data.json';
$allTodos = file_exists($path) ? json_decode(file_get_contents($path), true) : [];

session_start();



if (isset($_POST['todo'])) {
    header('Location: index.php');
    $newTodo = htmlspecialchars($_POST['todo']);
    if (strlen($_POST['todo']) < 3){
        $_SESSION['error'] = "Todo trop courte";
        header('Location: index.php');
    } else {
        $allTodos[] = ['id' => count($allTodos) + 1, 'todo' => $newTodo];
        file_put_contents($path, json_encode($allTodos, JSON_PRETTY_PRINT));
    }
}

if (isset($_POST['delete'])) {
    header('Location: index.php');
    $deleteId = $_POST['delete'];

    $allTodos = array_values(array_filter($allTodos, function($todo) use ($deleteId) {
        return $todo['id'] != $deleteId;
    }));

    file_put_contents($path, json_encode($allTodos, JSON_PRETTY_PRINT));
}

if (isset($_POST['edit'])) {
    $editId = $_POST['edit'];

    $editedTodo = null;
    foreach ($allTodos as $todo) {
        if ($todo['id'] == $editId) {
            $editedTodo = $todo;
            break;
        }
    }
}

if (isset($_POST['edited_id'])) {
    header('Location: index.php');
    $editedId = $_POST['edited_id'];
    $updatedTodo = htmlspecialchars($_POST['updated_todo']);

    foreach ($allTodos as &$todo) {
        if ($todo['id'] == $editedId) {
            $todo['todo'] = $updatedTodo;
            break;
        }
    }

    file_put_contents($path, json_encode($allTodos, JSON_PRETTY_PRINT));
}


?>

<!DOCTYPE HTML>
<html>
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
    <input type="button" value="A-Z">
    <input type="button" value="Z-A">
</form>
<ul>
    <?php foreach ($allTodos as $todo): ?>
        <li>
            <?= htmlspecialchars($todo['id']) ?> - <?= htmlspecialchars($todo['todo']) ?>

            <form action="index.php" method="post" style="display: inline;">
                <input type="hidden" name="edit" value="<?= $todo['id'] ?>">
                <input type="submit" value="Modifier">
            </form>

            <form action="index.php" method="post" style="display: inline;">
                <input type="hidden" name="delete" value="<?= $todo['id'] ?>">
                <input type="submit" value="Supprimer">
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