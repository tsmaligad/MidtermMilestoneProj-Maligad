<?php

require 'guest.php';
require 'db.php';
require 'classes/User.php';

$userModel = new User($pdo);

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (!$userModel->login($email, $password)) {
        $error = 'Invalid email or password.';
    } else {
        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Miffy Cafe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">

    <div class="auth-image-panel">
        <img src="images/login-baking.jpg" alt="Baking">

        <div class="auth-image-overlay">
            <h1>Miffy Cafe</h1>
            <p>Come back and bake something good.</p>
        </div>
    </div>

    <div class="auth-form-panel">

        <div class="auth-form-content">

            <div class="auth-heading">
                <p class="auth-eyebrow">WELCOME BACK</p>
                <h2>Login</h2>
                <p class="auth-subtitle">
                    Sign in to discover, save, and share baking recipes with the community.
                </p>
            </div>

            <?php if ($error): ?>
                <div class="auth-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">

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
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="auth-submit">
                    Login
                </button>

            </form>

            <p class="auth-switch">
                Don't have an account?
                <a href="register.php">Create Account</a>
            </p>

        </div>

    </div>

</div>

</body>
</html>