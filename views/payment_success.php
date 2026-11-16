<?php

require_once __DIR__ . '/../core/core.php';

if (empty($_SESSION['payment_confirmed'])) {
    redirect(app_url('views/all_products.php'));
}
unset($_SESSION['payment_confirmed']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment confirmed | Shoppn</title>
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
        <h1>Payment confirmed</h1>
        <p>Your payment has been verified and your order is recorded.</p>
        <a class="cart-primary-button" href="<?= htmlspecialchars(
            app_url('views/all_products.php'),
            ENT_QUOTES,
            'UTF-8'
        ); ?>">Continue shopping</a>
    </main>
    <?php require __DIR__ . '/layout/footer.php'; ?>
</body>
</html>