<?php

class Recipe
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(string $keyword = '', ?int $categoryId = null): array
    {
        $sql =
            'SELECT
                recipes.*,
                users.name AS author_name,
                categories.name AS category_name
             FROM recipes
             INNER JOIN users
             ON recipes.user_id = users.id
             INNER JOIN categories
             ON recipes.category_id = categories.id
             WHERE 1 = 1';

        $params = [];

        if ($keyword !== '') {
            $sql .= ' AND (
                recipes.title LIKE :keyword
                OR recipes.description LIKE :keyword
                OR recipes.tags LIKE :keyword
            )';

            $params[':keyword'] = '%' . $keyword . '%';
        }

        if ($categoryId) {
            $sql .= ' AND recipes.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        $sql .= ' ORDER BY recipes.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                recipes.*,
                users.name AS author_name,
                categories.name AS category_name
             FROM recipes
             INNER JOIN users
             ON recipes.user_id = users.id
             INNER JOIN categories
             ON recipes.category_id = categories.id
             WHERE recipes.id = :id'
        );

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch();
    }

    public function getIngredients(int $recipeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM ingredients
             WHERE recipe_id = :recipe_id
             ORDER BY id ASC'
        );

        $stmt->execute([
            ':recipe_id' => $recipeId
        ]);

        return $stmt->fetchAll();
    }

    public function create(
        int $userId,
        int $categoryId,
        string $title,
        string $description,
        string $steps,
        string $tags,
        array $ingredients
    ): int {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO recipes
                (user_id, category_id, title, description, steps, tags)
                VALUES
                (:user_id, :category_id, :title, :description, :steps, :tags)'
            );

            $stmt->execute([
                ':user_id' => $userId,
                ':category_id' => $categoryId,
                ':title' => $title,
                ':description' => $description,
                ':steps' => $steps,
                ':tags' => $tags
            ]);

            $recipeId = (int) $this->pdo->lastInsertId();

            $ingredientStmt = $this->pdo->prepare(
                'INSERT INTO ingredients (recipe_id, ingredient)
                 VALUES (:recipe_id, :ingredient)'
            );

            foreach ($ingredients as $ingredient) {
                $ingredientStmt->execute([
                    ':recipe_id' => $recipeId,
                    ':ingredient' => $ingredient
                ]);
            }

            $this->pdo->commit();

            return $recipeId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function incrementView(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE recipes
             SET view_count = view_count + 1
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id
        ]);
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM recipes
             WHERE id = :id
             AND user_id = :user_id'
        );

        $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }
}