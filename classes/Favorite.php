<?php

class Favorite
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function exists(int $userId, int $recipeId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT id FROM favorites
             WHERE user_id = :user_id
             AND recipe_id = :recipe_id'
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':recipe_id' => $recipeId
        ]);

        return (bool) $stmt->fetch();
    }

    public function add(int $userId, int $recipeId): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO favorites (user_id, recipe_id)
             VALUES (:user_id, :recipe_id)'
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':recipe_id' => $recipeId
        ]);
    }

    public function remove(int $userId, int $recipeId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM favorites
             WHERE user_id = :user_id
             AND recipe_id = :recipe_id'
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':recipe_id' => $recipeId
        ]);
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                recipes.*,
                users.name AS author_name,
                categories.name AS category_name
             FROM favorites
             INNER JOIN recipes
             ON favorites.recipe_id = recipes.id
             INNER JOIN users
             ON recipes.user_id = users.id
             INNER JOIN categories
             ON recipes.category_id = categories.id
             WHERE favorites.user_id = :user_id
             ORDER BY favorites.created_at DESC'
        );

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll();
    }
}