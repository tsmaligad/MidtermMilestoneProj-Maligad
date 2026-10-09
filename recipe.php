<?php

require 'auth.php';
require 'db.php';
require 'classes/Recipe.php';
require 'classes/Comment.php';
require 'classes/Favorite.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

$recipeModel = new Recipe($pdo);
$commentModel = new Comment($pdo);
$favoriteModel = new Favorite($pdo);

$recipe = $recipeModel->getById($id);

if (!$recipe) {
    header('Location: index.php');
    exit;
}

$recipeModel->incrementView($id);

$recipe = $recipeModel->getById($id);

$ingredients = $recipeModel->getIngredients($id);
$comments = $commentModel->getByRecipe($id);

$isFavorite = $favoriteModel->exists(
    $_SESSION['user_id'],
    $id
);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title><?= htmlspecialchars($recipe['title']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <a href="index.php">Home</a>
    <a href="favorites.php">Favorites</a>
    <a href="logout.php">Logout</a>
</nav>

<main class="recipe-detail">

    <h1><?= htmlspecialchars($recipe['title']) ?></h1>

    <?php if ($recipe['is_edited']): ?>
        <span class="edited">(edited)</span>
    <?php endif; ?>

    <p>
        By <?= htmlspecialchars($recipe['author_name']) ?>
    </p>

    <p>
        Category:
        <?= htmlspecialchars($recipe['category_name']) ?>
    </p>

    <p>
        <?= $recipe['view_count'] ?> views
    </p>

    <button
        class="favorite-button"
        data-recipe-id="<?= $recipe['id'] ?>"
    >
        <?= $isFavorite ? 'Remove Favorite' : 'Save Favorite' ?>
    </button>

    <h3>Description</h3>

    <p>
        <?= nl2br(htmlspecialchars($recipe['description'])) ?>
    </p>

    <h3>Ingredients</h3>

    <ul>
        <?php foreach ($ingredients as $ingredient): ?>
            <li>
                <?= htmlspecialchars($ingredient['ingredient']) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <h3>Steps</h3>

    <p>
        <?= nl2br(htmlspecialchars($recipe['steps'])) ?>
    </p>

    <?php if ($recipe['tags']): ?>

        <h3>Tags</h3>

        <?php foreach (explode(',', $recipe['tags']) as $tag): ?>
            <span class="tag">
                #<?= htmlspecialchars(trim($tag)) ?>
            </span>
        <?php endforeach; ?>

    <?php endif; ?>

    <?php if ($recipe['user_id'] == $_SESSION['user_id']): ?>

        <p>
            <a href="edit_recipe.php?id=<?= $recipe['id'] ?>">
                Edit Recipe
            </a>
        </p>

        <form method="POST" action="delete_recipe.php">
            <input
                type="hidden"
                name="id"
                value="<?= $recipe['id'] ?>"
            >

            <button type="submit">
                Delete Recipe
            </button>
        </form>

    <?php endif; ?>

    <h2>Comments</h2>

    <?php foreach ($comments as $comment): ?>

        <div class="comment">

            <strong>
                <?= htmlspecialchars($comment['name']) ?>
            </strong>

            <p>
                <?= nl2br(htmlspecialchars($comment['content'])) ?>
            </p>

            <?php if ($comment['is_edited']): ?>
                <span class="edited">(edited)</span>
            <?php endif; ?>

            <?php if ($comment['user_id'] == $_SESSION['user_id']): ?>

                <a href="edit_comment.php?id=<?= $comment['id'] ?>">
                    Edit
                </a>

                <form
                    method="POST"
                    action="delete_comment.php"
                    class="inline"
                >
                    <input
                        type="hidden"
                        name="id"
                        value="<?= $comment['id'] ?>"
                    >

                    <input
                        type="hidden"
                        name="recipe_id"
                        value="<?= $recipe['id'] ?>"
                    >

                    <button type="submit">
                        Delete
                    </button>
                </form>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>

    <form method="POST" action="create_comment.php">

        <input
            type="hidden"
            name="recipe_id"
            value="<?= $recipe['id'] ?>"
        >

        <textarea
            name="content"
            required
            maxlength="1000"
            placeholder="Leave feedback..."
        ></textarea>

        <button type="submit">
            Comment
        </button>

    </form>

</main>

<script src="script.js"></script>

</body>
</html>