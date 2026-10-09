<?php

require 'auth.php';
require 'db.php';
require 'classes/Comment.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$recipeId = filter_var(
    $_POST['recipe_id'] ?? null,
    FILTER_VALIDATE_INT
);

$content = trim($_POST['content'] ?? '');

if (
    !$recipeId ||
    $content === '' ||
    strlen($content) > 1000
) {
    header('Location: index.php');
    exit;
}

$commentModel = new Comment($pdo);

$commentModel->create(
    $recipeId,
    $_SESSION['user_id'],
    $content
);

header('Location: recipe.php?id=' . $recipeId);
exit;