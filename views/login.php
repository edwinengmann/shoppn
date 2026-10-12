<?php

require_once __DIR__ . '/../core/core.php';

$old = $_SESSION['_old_login'] ?? [];
unset($_SESSION['_old_login']);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login</title>

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars(
            app_url('css/style.css')
                . '?v='
                . filemtime(__DIR__ . '/../css/style.css'),
            ENT_QUOTES,
            'UTF-8'
        ); ?>"
    >

</head>


<body class="auth-page">


<?php require_once __DIR__ . '/layout/header.php'; ?>


<main class="container">


    <h1>Login</h1>


    <?php if (!empty($_SESSION['success'])): ?>

        <div class="success-message">

            <?= htmlspecialchars($_SESSION['success']); ?>

            <?php unset($_SESSION['success']); ?>

        </div>

    <?php elseif (!empty($_SESSION['error'])): ?>

        <div class="error-message">

            <?= htmlspecialchars($_SESSION['error']); ?>

            <?php unset($_SESSION['error']); ?>

        </div>

    <?php endif; ?>


    <form
        action="<?= htmlspecialchars(
            app_url('actions/login_action.php'),
            ENT_QUOTES,
            'UTF-8'
        ); ?>"
        method="POST"
    >


        <div>

            <label for="customer_email">
                Email
            </label>

            <input
                type="email"
                id="customer_email"
                name="customer_email"
                maxlength="100"
                value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                required
            >

        </div>


        <div>

            <label for="customer_pass">
                Password
            </label>

            <input
                type="password"
                id="customer_pass"
                name="customer_pass"
                required
            >

        </div>


        <button type="submit">
            Login
        </button>


    </form>


    <p>

        Don't have an account?

        <a href="register.php">
            Register
        </a>

    </p>


</main>


</body>

</html>