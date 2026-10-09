<?php

require 'auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$commentId = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

$recipeId = filter_input(
    INPUT_POST,
    'recipe_id',
    FILTER_VALIDATE_INT
);

if (!$commentId || !$recipeId) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare(
    'DELETE FROM comments
     WHERE id = :id
     AND user_id = :user_id'
);

$stmt->execute([
    ':id' => $commentId,
    ':user_id' => $_SESSION['user_id']
]);

header('Location: recipe.php?id=' . $recipeId);
exit;