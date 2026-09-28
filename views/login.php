<?php

require_once __DIR__ . '/../core/core.php';

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
        href="../css/style.css"
    >

</head>


<body>


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
        action="../actions/login_action.php"
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