<?php

require_once __DIR__ . '/../core/core.php';

$old = $_SESSION['_old_register'] ?? [];
unset($_SESSION['_old_register']);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Register</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>


<body>


<?php require_once __DIR__ . '/layout/header.php'; ?>
<main class="container">

    <h1>Create Account</h1>


    <?php if (!empty($_SESSION['error'])): ?>

        <div class="error-message">

            <?= nl2br(htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8')); ?>

            <?php unset($_SESSION['error']); ?>

        </div>

    <?php endif; ?>


    <form
        id="register-form"
        action="../actions/register_action.php"
        method="POST"
        novalidate
    >


        <!-- Full Name -->

        <div>

            <label for="customer_name">
                Full Name
            </label>

            <input
                type="text"
                id="customer_name"
                name="customer_name"
                maxlength="100"
                value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                required
            >

            <small
                id="name-error"
                class="field-error"
            ></small>

        </div>


        <!-- Email -->

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

            <small
                id="email-error"
                class="field-error"
            ></small>

        </div>


        <!-- Password -->

        <div>

            <label for="customer_pass">
                Password
            </label>

            <input
                type="password"
                id="customer_pass"
                name="customer_pass"
                minlength="8"
                required
            >

            <small
                id="password-error"
                class="field-error"
            ></small>

        </div>


        <!-- Country -->

        <div>

            <label for="customer_country">
                Country
            </label>

            <select
                id="customer_country"
                name="customer_country"
                required
            >

                <option value="">
                    -- Select Country --
                </option>

                <option value="Ghana" <?= ($old['country'] ?? '') === 'Ghana' ? 'selected' : ''; ?>>
                    Ghana
                </option>

                <option value="Nigeria" <?= ($old['country'] ?? '') === 'Nigeria' ? 'selected' : ''; ?>>
                    Nigeria
                </option>

                <option value="Kenya" <?= ($old['country'] ?? '') === 'Kenya' ? 'selected' : ''; ?>>
                    Kenya
                </option>

                <option value="South Africa" <?= ($old['country'] ?? '') === 'South Africa' ? 'selected' : ''; ?>>
                    South Africa
                </option>

                <option value="United Kingdom" <?= ($old['country'] ?? '') === 'United Kingdom' ? 'selected' : ''; ?>>
                    United Kingdom
                </option>

                <option value="United States" <?= ($old['country'] ?? '') === 'United States' ? 'selected' : ''; ?>>
                    United States
                </option>

            </select>

            <small
                id="country-error"
                class="field-error"
            ></small>

        </div>


        <!-- City -->

        <div>

            <label for="customer_city">
                City
            </label>

            <input
                type="text"
                id="customer_city"
                name="customer_city"
                maxlength="100"
                value="<?= htmlspecialchars($old['city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                required
            >

            <small
                id="city-error"
                class="field-error"
            ></small>

        </div>


        <!-- Contact -->

        <div>

            <label for="customer_contact">
                Contact Number
            </label>

            <input
                type="text"
                id="customer_contact"
                name="customer_contact"
                maxlength="15"
                value="<?= htmlspecialchars($old['contact'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                required
            >

            <small
                id="contact-error"
                class="field-error"
            ></small>

        </div>


        <button
            type="submit"
            id="register-button"
        >
            Register
        </button>


    </form>


    <p>

        Already have an account?

        <a href="login.php">
            Login
        </a>

    </p>


</main>


<script src="../js/validate.js"></script>


</body>

</html>