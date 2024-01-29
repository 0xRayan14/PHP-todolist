<?php
$path = 'data.json';

if (isset($_POST)) {
    $data = [
        'todo' => htmlspecialchars($_POST['todo']),

    ];

    $moyenne[] = $data;

    $jsonData = json_encode($moyenne);

    $fp = fopen($path, 'w');
    fwrite($fp, $jsonData);
    fclose($fp);
}
?>

<!DOCTYPE HTML>
<html>
<body>

<h2>Toutes les todo's :<br></h2>

<form action="index.php" method="post">
    <label>
        Ajouter une todo:
        <input type="text" name="todo">
    </label><br>
</form>
<?= json_encode(htmlspecialchars($_POST['todo'])) ?>


</body>
</html>

