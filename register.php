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
        $error = 'Invalid email.';
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
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-card">
    <h1>Create Account</h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input
            type="text"
            name="name"
            placeholder="Name"
            value="<?= htmlspecialchars($name) ?>"
            required
            maxlength="100"
        >

        <input
            type="email"
            name="email"
            placeholder="Email"
            value="<?= htmlspecialchars($email) ?>"
            required
            maxlength="255"
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
            minlength="8"
        >

        <button type="submit">Register</button>
    </form>

    <p>Already registered? <a href="login.php">Login</a></p>
</div>

</body>
</html>