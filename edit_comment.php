<?php

require 'auth.php';
require 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT * FROM comments
     WHERE id = :id
     AND user_id = :user_id'
);

$stmt->execute([
    ':id' => $id,
    ':user_id' => $_SESSION['user_id']
]);

$comment = $stmt->fetch();

if (!$comment) {
    header('Location: index.php');
    exit;
}

$content = $comment['content'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');

    if ($content === '') {
        $error = 'Comment is required.';
    } elseif (strlen($content) > 1000) {
        $error = 'Comment is too long.';
    } else {
        $stmt = $pdo->prepare(
            'UPDATE comments
             SET content = :content,
                 is_edited = 1
             WHERE id = :id
             AND user_id = :user_id'
        );

        $stmt->execute([
            ':content' => $content,
            ':id' => $id,
            ':user_id' => $_SESSION['user_id']
        ]);

        header(
            'Location: recipe.php?id=' .
            $comment['recipe_id']
        );

        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>Edit Comment</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-card">

    <h1>Edit Comment</h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">

        <textarea
            name="content"
            required
            maxlength="1000"
        ><?= htmlspecialchars($content) ?></textarea>

        <button type="submit">
            Save Changes
        </button>

    </form>

</div>

</body>
</html>