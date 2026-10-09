<?php

class Comment
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByRecipe(int $recipeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                comments.*,
                users.name
             FROM comments
             INNER JOIN users
             ON comments.user_id = users.id
             WHERE comments.recipe_id = :recipe_id
             ORDER BY comments.created_at ASC'
        );

        $stmt->execute([
            ':recipe_id' => $recipeId
        ]);

        return $stmt->fetchAll();
    }

    public function create(int $recipeId, int $userId, string $content): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO comments (recipe_id, user_id, content)
             VALUES (:recipe_id, :user_id, :content)'
        );

        $stmt->execute([
            ':recipe_id' => $recipeId,
            ':user_id' => $userId,
            ':content' => $content
        ]);
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM comments
             WHERE id = :id
             AND user_id = :user_id'
        );

        $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }
}