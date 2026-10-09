<?php

require 'auth.php';
require 'db.php';
require 'classes/Comment.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

$recipeId = filter_var(
    $_POST['recipe_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id || !$recipeId) {
    header('Location: index.php');
    exit;
}

$commentModel = new Comment($pdo);

$commentModel->delete(
    $id,
    $_SESSION['user_id']
);

header('Location: recipe.php?id=' . $recipeId);
exit;