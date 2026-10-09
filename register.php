<?php

require 'guest.php';
require 'db.php';
require 'classes/User.php';

$userModel = new User($pdo);

$name = '';
$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (strlen($name) > 100) {
        $error = 'Name is too long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!$userModel->register($name, $email, $password)) {
        $error = 'Email already exists.';
    } else {
        header('Location: login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Miffy Cafe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">

    <div class="auth-image-panel">
        <img src="images/register-baking.jpg" alt="Baking">

        <div class="auth-image-overlay">
            <h1>Miffy Cafe</h1>
            <p>Share something sweet.</p>
        </div>
    </div>

    <div class="auth-form-panel">

        <div class="auth-form-content">

            <div class="auth-heading">
                <p class="auth-eyebrow">WELCOME, BAKER</p>
                <h2>Create Account</h2>
                <p class="auth-subtitle">
                    Join our baking community and start sharing your favorite recipes.
                </p>
            </div>

            <?php if ($error): ?>
                <div class="auth-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">

                <div class="form-group">
                    <label for="name">Name</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your name"
                        value="<?= htmlspecialchars($name) ?>"
                        required
                        maxlength="100"
                        autocomplete="name"
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?= htmlspecialchars($email) ?>"
                        required
                        maxlength="255"
                        autocomplete="email"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="At least 8 characters"
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                </div>

                <button type="submit" class="auth-submit">
                    Create Account
                </button>

            </form>

            <p class="auth-switch">
                Already have an account?
                <a href="login.php">Login</a>
            </p>

        </div>

    </div>

</div>

</body>
</html>