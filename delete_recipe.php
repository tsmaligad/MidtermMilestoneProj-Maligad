<?php

require 'auth.php';
require 'db.php';
require 'classes/Recipe.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id) {
    header('Location: index.php');
    exit;
}

$recipeModel = new Recipe($pdo);

$recipeModel->delete(
    $id,
    $_SESSION['user_id']
);

header('Location: index.php');
exit;