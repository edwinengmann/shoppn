<?php

require_once __DIR__ . '/../core/core.php';

$errorMessage = $_SESSION['error'] ?? 'Your payment was not confirmed.';
unset($_SESSION['error']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment not confirmed | Shoppn</title>
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
<body class="catalog-page cart-page">
    <?php require __DIR__ . '/layout/header.php'; ?>
    <main class="catalog-content cart-empty-state">
        <h1>Payment not confirmed</h1>
        <p role="alert"><?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        <a class="cart-primary-button" href="<?= htmlspecialchars(
            app_url('views/cart.php'),
            ENT_QUOTES,
            'UTF-8'
        ); ?>">Return to your bag</a>
    </main>
    <?php require __DIR__ . '/layout/footer.php'; ?>
</body>
</html>