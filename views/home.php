<?php

require_once __DIR__ . '/../core/core.php';

require_login();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Shoppn</title>

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


<body class="home-page">


    <?php require_once __DIR__ . '/layout/header.php'; ?>


    <main class="home-hero">

        <div class="home-hero-content">

            <p class="home-welcome">
                WELCOME TO
            </p>

            <h1>
                SHOPPN
            </h1>

            <a
                href="<?= htmlspecialchars(
                    app_url('views/all_products.php'),
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>"
                class="shop-now-button"
            >
                SHOP NOW
            </a>

        </div>

    </main>

    <?php require_once __DIR__ . '/layout/footer.php'; ?>


</body>

</html>