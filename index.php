<?php

require 'auth.php';
require 'db.php';
require 'classes/Recipe.php';
require 'classes/Favorite.php';

$recipeModel = new Recipe($pdo);
$favoriteModel = new Favorite($pdo);

$keyword = trim($_GET['keyword'] ?? '');

$categoryId = filter_input(
    INPUT_GET,
    'category_id',
    FILTER_VALIDATE_INT
);

$recipes = $recipeModel->getAll(
    $keyword,
    $categoryId ?: null
);

$categoryStmt = $pdo->prepare(
    'SELECT * FROM categories ORDER BY name ASC'
);

$categoryStmt->execute();

$categories = $categoryStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>Miffy Cafe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <h2>Miffy Cafe</h2>

    <div>
        <span>
            Hello,
            <?= htmlspecialchars($_SESSION['user_name']) ?>
        </span>

        <a href="create_recipe.php">
            Share Recipe
        </a>

        <a href="favorites.php">
            Favorites
        </a>

        <a href="logout.php">
            Logout
        </a>
    </div>
</nav>

<main>

    <div class="home-heading">
        <p class="home-eyebrow">BAKING COMMUNITY</p>
        <h1>Discover Something Sweet</h1>
        <p>
            Browse recipes shared by fellow bakers.
        </p>
    </div>

    <form
        method="GET"
        class="search-form"
    >

        <input
            type="text"
            name="keyword"
            placeholder="Search recipes..."
            value="<?= htmlspecialchars($keyword) ?>"
        >

        <select name="category_id">

            <option value="">
                All Categories
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

        <button type="submit">
            Search
        </button>

    </form>

    <?php if (!$recipes): ?>

        <div class="empty-state">
            <h2>No recipes found</h2>
            <p>
                Try another keyword or category.
            </p>
        </div>

    <?php else: ?>

        <div class="recipe-grid">

            <?php foreach ($recipes as $recipe): ?>

                <?php
                $isFavorite = $favoriteModel->exists(
                    $_SESSION['user_id'],
                    $recipe['id']
                );
                ?>

                <div class="recipe-card">

                    <a
                        href="recipe.php?id=<?= $recipe['id'] ?>"
                        class="recipe-image-link"
                    >

                        <?php if ($recipe['image']): ?>

                            <img
                                class="recipe-card-image"
                                src="images/recipes/<?= htmlspecialchars($recipe['image']) ?>"
                                alt="<?= htmlspecialchars($recipe['title']) ?>"
                            >

                        <?php else: ?>

                            <div class="recipe-image-placeholder">
                                No Image
                            </div>

                        <?php endif; ?>

                    </a>

                    <div class="recipe-card-content">

                        <p class="recipe-category">
                            <?= htmlspecialchars($recipe['category_name']) ?>
                        </p>

                        <h2 class="recipe-card-title">

                            <a href="recipe.php?id=<?= $recipe['id'] ?>">
                                <?= htmlspecialchars($recipe['title']) ?>
                            </a>

                        </h2>

                        <p class="recipe-card-description">
                            <?= htmlspecialchars($recipe['description']) ?>
                        </p>

                        <div class="recipe-card-meta">

                            <span>
                                By <?= htmlspecialchars($recipe['author_name']) ?>
                            </span>

                            <span>
                                <?= $recipe['view_count'] ?> views
                            </span>

                        </div>

                        <?php if ($recipe['tags']): ?>

                            <div class="recipe-tags">

                                <?php foreach (array_slice(explode(',', $recipe['tags']), 0, 3) as $tag): ?>

                                    <span class="tag">
                                        #<?= htmlspecialchars(trim($tag)) ?>
                                    </span>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>

                        <div class="recipe-card-footer">

                            <a
                                href="recipe.php?id=<?= $recipe['id'] ?>"
                                class="view-recipe-button"
                            >
                                View Recipe
                            </a>

                            <button
                                type="button"
                                class="heart-button <?= $isFavorite ? 'active' : '' ?>"
                                data-recipe-id="<?= $recipe['id'] ?>"
                                aria-label="Toggle favorite"
                            >
                                <?= $isFavorite ? '♥' : '♡' ?>
                            </button>

                        </div>

                        <?php if ($recipe['is_edited']): ?>

                            <span class="edited">
                                edited
                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

<script src="script.js"></script>

</body>
</html>