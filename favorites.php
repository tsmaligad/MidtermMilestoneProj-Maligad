<?php

require 'auth.php';
require 'db.php';
require 'classes/Favorite.php';

$favoriteModel = new Favorite($pdo);

$favorites = $favoriteModel->getByUser(
    $_SESSION['user_id']
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>Favorites | Miffy Cafe</title>
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

        <a href="index.php">
            Home
        </a>

        <a href="create_recipe.php">
            Share Recipe
        </a>

        <a href="logout.php">
            Logout
        </a>
    </div>
</nav>

<main>

    <div class="home-heading">
        <p class="home-eyebrow">SAVED RECIPES</p>

        <h1>My Favorites</h1>

        <p>
            All your favorite baking recipes in one place.
        </p>
    </div>

    <?php if (!$favorites): ?>

        <div class="empty-state">

            <h2>No favorites yet</h2>

            <p>
                Save recipes you love and they will appear here.
            </p>

            <a
                href="index.php"
                class="empty-state-link"
            >
                Browse Recipes
            </a>

        </div>

    <?php else: ?>

        <div class="recipe-grid">

            <?php foreach ($favorites as $recipe): ?>

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
                                class="heart-button active"
                                data-recipe-id="<?= $recipe['id'] ?>"
                                aria-label="Remove from favorites"
                            >
                                ♥
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