<?php

require 'auth.php';
require 'db.php';
require 'classes/Favorite.php';

header('Content-Type: application/json');

$recipeId = filter_var(
    $_POST['recipe_id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$recipeId) {
    echo json_encode([
        'success' => false
    ]);
    exit;
}

$favoriteModel = new Favorite($pdo);

$userId = $_SESSION['user_id'];

if ($favoriteModel->exists($userId, $recipeId)) {
    $favoriteModel->remove($userId, $recipeId);

    echo json_encode([
        'success' => true,
        'favorite' => false
    ]);

    exit;
}

$favoriteModel->add($userId, $recipeId);

echo json_encode([
    'success' => true,
    'favorite' => true
]);