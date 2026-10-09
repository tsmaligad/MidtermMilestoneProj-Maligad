<?php

require 'auth.php';
require 'db.php';
require 'classes/Recipe.php';

$recipeModel = new Recipe($pdo);

$stmt = $pdo->prepare(
    'SELECT * FROM categories ORDER BY name ASC'
);

$stmt->execute();

$categories = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = filter_var(
        $_POST['category_id'] ?? null,
        FILTER_VALIDATE_INT
    );
    $steps = trim($_POST['steps'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    $ingredients = $_POST['ingredients'] ?? [];

    $ingredients = array_values(
        array_filter(
            array_map('trim', $ingredients),
            fn($ingredient) => $ingredient !== ''
        )
    );

    if (
        $title === '' ||
        $description === '' ||
        !$categoryId ||
        $steps === ''
    ) {
        $error = 'All required fields must be completed.';
    } elseif (strlen($title) > 150) {
        $error = 'Title is too long.';
    } elseif (strlen($description) > 500) {
        $error = 'Description is too long.';
    } elseif (strlen($steps) > 5000) {
        $error = 'Steps are too long.';
    } elseif (strlen($tags) > 255) {
        $error = 'Tags are too long.';
    } elseif (count($ingredients) < 1) {
        $error = 'Add at least one ingredient.';
    } else {
        $recipeId = $recipeModel->create(
            $_SESSION['user_id'],
            $categoryId,
            $title,
            $description,
            $steps,
            $tags,
            $ingredients
        );

        header('Location: recipe.php?id=' . $recipeId);
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Recipe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-card wide">

    <h1>Share a Baking Recipe</h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>Title</label>

        <input
            type="text"
            name="title"
            required
            maxlength="150"
        >

        <label>Description</label>

        <textarea
            name="description"
            required
            maxlength="500"
        ></textarea>

        <label>Category</label>

        <select name="category_id" required>
            <option value="">Choose category</option>

            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>">
                    <?= htmlspecialchars($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Ingredients</label>

        <div id="ingredients">
            <input
                type="text"
                name="ingredients[]"
                required
                maxlength="255"
            >
        </div>

        <button type="button" id="addIngredient">
            Add Ingredient
        </button>

        <label>Cooking Steps</label>

        <textarea
            name="steps"
            required
            maxlength="5000"
        ></textarea>

        <label>Tags</label>

        <input
            type="text"
            name="tags"
            maxlength="255"
            placeholder="chocolate, easy, beginner"
        >

        <button type="submit">
            Share Recipe
        </button>

        <a href="index.php">Cancel</a>

    </form>

</div>

<script src="script.js"></script>

</body>
</html>