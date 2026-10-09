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

$title = '';
$description = '';
$steps = '';
$tags = '';
$categoryId = null;
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

    $imageName = null;

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
    } elseif (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] !== UPLOAD_ERR_OK
    ) {
        $error = 'Recipe image is required.';
    } else {
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        $imageType = mime_content_type(
            $_FILES['image']['tmp_name']
        );

        if (!isset($allowedTypes[$imageType])) {
            $error = 'Only JPG, PNG, and WEBP images are allowed.';
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $error = 'Image must be 5MB or smaller.';
        } else {
            $extension = $allowedTypes[$imageType];

            $imageName =
                uniqid('recipe_', true) .
                '.' .
                $extension;

            $destination =
                'images/recipes/' .
                $imageName;

            if (
                !move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $destination
                )
            ) {
                $error = 'Image upload failed.';
            }
        }
    }

    if ($error === '') {
        $recipeId = $recipeModel->create(
            $_SESSION['user_id'],
            $categoryId,
            $title,
            $description,
            $steps,
            $tags,
            $imageName,
            $ingredients
        );

        header(
            'Location: recipe.php?id=' .
            $recipeId
        );

        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>Share Recipe | Miffy Cafe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <h2>Miffy Cafe</h2>

    <div>
        <a href="index.php">Home</a>
        <a href="favorites.php">Favorites</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="form-card wide">

    <h1>Share a Baking Recipe</h1>

    <?php if ($error): ?>
        <p class="error">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <form
        method="POST"
        enctype="multipart/form-data"
    >

        <label for="title">Title</label>

        <input
            type="text"
            id="title"
            name="title"
            value="<?= htmlspecialchars($title) ?>"
            required
            maxlength="150"
        >

        <label for="description">Description</label>

        <textarea
            id="description"
            name="description"
            required
            maxlength="500"
        ><?= htmlspecialchars($description) ?></textarea>

        <label for="category_id">Category</label>

        <select
            id="category_id"
            name="category_id"
            required
        >
            <option value="">
                Choose category
            </option>

            <?php foreach ($categories as $category): ?>
                <option
                    value="<?= $category['id'] ?>"
                    <?= $categoryId == $category['id'] ? 'selected' : '' ?>
                >
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
                placeholder="e.g. 2 cups flour"
            >
        </div>

        <button
            type="button"
            id="addIngredient"
        >
            Add Ingredient
        </button>

        <label for="steps">Cooking Steps</label>

        <textarea
            id="steps"
            name="steps"
            required
            maxlength="5000"
        ><?= htmlspecialchars($steps) ?></textarea>

        <label for="tags">Tags</label>

        <input
            type="text"
            id="tags"
            name="tags"
            value="<?= htmlspecialchars($tags) ?>"
            maxlength="255"
            placeholder="chocolate, easy, beginner"
        >

        <label for="image">Recipe Photo</label>

        <input
            type="file"
            id="image"
            name="image"
            accept="image/jpeg,image/png,image/webp"
            required
        >

        <button type="submit">
            Share Recipe
        </button>

        <a href="index.php">
            Cancel
        </a>

    </form>

</div>

<script src="script.js"></script>

</body>
</html>