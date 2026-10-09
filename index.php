<?php

require 'auth.php';
require 'db.php';
require 'classes/Recipe.php';

$recipeModel = new Recipe($pdo);

$keyword = trim($_GET['keyword'] ?? '');
$categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);

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
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Whisk & Share</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <h2>Whisk & Share</h2>

    <div>
        <span>Hello, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
        <a href="create_recipe.php">Share Recipe</a>
        <a href="favorites.php">Favorites</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main>

    <form method="GET" class="search-form">
        <input
            type="text"
            name="keyword"
            placeholder="Search recipes..."
            value="<?= htmlspecialchars($keyword) ?>"
        >

        <select name="category_id">
            <option value="">All Categories</option>

            <?php foreach ($categories as $category): ?>
                <option
                    value="<?= $category['id'] ?>"
                    <?= $categoryId == $category['id'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Search</button>
    </form>

    <div class="recipe-grid">

        <?php foreach ($recipes as $recipe): ?>

            <div class="recipe-card">
                <h2>
                    <a href="recipe.php?id=<?= $recipe['id'] ?>">
                        <?= htmlspecialchars($recipe['title']) ?>
                    </a>
                </h2>

                <p><?= htmlspecialchars($recipe['description']) ?></p>

                <p>
                    By <?= htmlspecialchars($recipe['author_name']) ?>
                </p>

                <p>
                    <?= htmlspecialchars($recipe['category_name']) ?>
                </p>

                <p>
                    <?= $recipe['view_count'] ?> views
                </p>

                <?php if ($recipe['tags']): ?>
                    <p>
                        <?php foreach (explode(',', $recipe['tags']) as $tag): ?>
                            <span class="tag">
                                #<?= htmlspecialchars(trim($tag)) ?>
                            </span>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>

                <?php if ($recipe['is_edited']): ?>
                    <span class="edited">(edited)</span>
                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

</main>

</body>
</html>