<?php

require 'auth.php';
require 'db.php';
require 'classes/Favorite.php';

header('Content-Type: application/json');

$recipeId = filter_input(
    INPUT_POST,
    'recipe_id',
    FILTER_VALIDATE_INT
);

if (!$recipeId) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid recipe.'
    ]);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id FROM recipes WHERE id = :id'
);

$stmt->execute([
    ':id' => $recipeId
]);

if (!$stmt->fetch()) {
    echo json_encode([
        'success' => false,
        'message' => 'Recipe not found.'
    ]);
    exit;
}

$favoriteModel = new Favorite($pdo);

$userId = $_SESSION['user_id'];

if ($favoriteModel->exists($userId, $recipeId)) {
    $favoriteModel->remove(
        $userId,
        $recipeId
    );

    echo json_encode([
        'success' => true,
        'favorited' => false
    ]);

    exit;
}

$favoriteModel->add(
    $userId,
    $recipeId
);

echo json_encode([
    'success' => true,
    'favorited' => true
]);

exit;