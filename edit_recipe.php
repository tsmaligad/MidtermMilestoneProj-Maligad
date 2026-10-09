<?php

require 'auth.php';
require 'db.php';
require 'classes/Recipe.php';

$recipeModel = new Recipe($pdo);

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {
    header('Location: index.php');
    exit;
}

$recipe = $recipeModel->getById($id);

if (!$recipe) {
    header('Location: index.php');
    exit;
}

if ($recipe['user_id'] != $_SESSION['user_id']) {
    header('Location: recipe.php?id=' . $id);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT *
     FROM categories
     ORDER BY name ASC'
);

$stmt->execute();

$categories = $stmt->fetchAll();

$currentIngredients = $recipeModel->getIngredients($id);

$title = $recipe['title'];
$description = $recipe['description'];
$categoryId = $recipe['category_id'];
$steps = $recipe['steps'];
$tags = $recipe['tags'] ?? '';
$currentImage = $recipe['image'];
$error = '';

$ingredientValues = [];

foreach ($currentIngredients as $ingredient) {
    $ingredientValues[] = $ingredient['ingredient'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $categoryId = filter_var(
        $_POST['category_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $steps = trim($_POST['steps'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    $ingredientValues = $_POST['ingredients'] ?? [];

    $ingredientValues = array_values(
        array_filter(
            array_map('trim', $ingredientValues),
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
    } elseif (count($ingredientValues) < 1) {
        $error = 'Add at least one ingredient.';
    }

    foreach ($ingredientValues as $ingredient) {
        if (strlen($ingredient) > 255) {
            $error = 'Each ingredient must be 255 characters or less.';
            break;
        }
    }

    if ($error === '') {
        $categoryStmt = $pdo->prepare(
            'SELECT id
             FROM categories
             WHERE id = :id'
        );

        $categoryStmt->execute([
            ':id' => $categoryId
        ]);

        if (!$categoryStmt->fetch()) {
            $error = 'Invalid category.';
        }
    }

    $imageName = $currentImage;
    $newImageName = null;

    if (
        $error === '' &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Image upload failed.';
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

                $newImageName =
                    uniqid('recipe_', true) .
                    '.' .
                    $extension;

                $destination =
                    'images/recipes/' .
                    $newImageName;

                if (
                    move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $destination
                    )
                ) {
                    $imageName = $newImageName;
                } else {
                    $error = 'Image upload failed.';
                }
            }
        }
    }

    if ($error === '') {
        try {
            $updated = $recipeModel->update(
                $id,
                $_SESSION['user_id'],
                $categoryId,
                $title,
                $description,
                $steps,
                $tags,
                $imageName,
                $ingredientValues
            );

            if (!$updated) {
                if (
                    $newImageName &&
                    file_exists('images/recipes/' . $newImageName)
                ) {
                    unlink(
                        'images/recipes/' .
                        $newImageName
                    );
                }

                header('Location: index.php');
                exit;
            }

            if (
                $newImageName &&
                $currentImage &&
                file_exists('images/recipes/' . $currentImage)
            ) {
                unlink(
                    'images/recipes/' .
                    $currentImage
                );
            }

            header(
                'Location: recipe.php?id=' .
                $id
            );

            exit;
        } catch (Throwable $e) {
            if (
                $newImageName &&
                file_exists('images/recipes/' . $newImageName)
            ) {
                unlink(
                    'images/recipes/' .
                    $newImageName
                );
            }

            $error = 'Unable to update recipe.';
        }
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
    <title>Edit Recipe | Miffy Cafe</title>
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

    <h1>Edit Recipe</h1>

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

            <?php foreach ($ingredientValues as $ingredient): ?>

                <input
                    type="text"
                    name="ingredients[]"
                    value="<?= htmlspecialchars($ingredient) ?>"
                    required
                    maxlength="255"
                >

            <?php endforeach; ?>

        </div>

        <button
            type="button"
            id="addIngredient"
        >
            Add Ingredient
        </button>

        <label for="steps">
            Cooking Steps
        </label>

        <textarea
            id="steps"
            name="steps"
            required
            maxlength="5000"
        ><?= htmlspecialchars($steps) ?></textarea>

        <label for="tags">
            Tags
        </label>

        <input
            type="text"
            id="tags"
            name="tags"
            value="<?= htmlspecialchars($tags) ?>"
            maxlength="255"
            placeholder="chocolate, easy, beginner"
        >

        <?php if ($currentImage): ?>

            <label>
                Current Recipe Photo
            </label>

            <img
                src="images/recipes/<?= htmlspecialchars($currentImage) ?>"
                alt="<?= htmlspecialchars($title) ?>"
                class="edit-recipe-image"
            >

        <?php endif; ?>

        <label for="image">
            Change Recipe Photo
        </label>

        <input
            type="file"
            id="image"
            name="image"
            accept="image/jpeg,image/png,image/webp"
        >

        <button type="submit">
            Save Changes
        </button>

        <a href="recipe.php?id=<?= $id ?>">
            Cancel
        </a>

    </form>

</div>

<script src="script.js"></script>

</body>
</html>