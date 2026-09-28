<?php

require_once __DIR__ . '/../core/core.php';

if (!is_logged_in()) {
    $_SESSION['error'] = 'Please log in to continue.';
    redirect(app_url('views/login.php'));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Shoppn</title>
</head>
<body>
    <?php require_once __DIR__ . '/layout/header.php'; ?>

    <main>
        <h1>Welcome to Shoppn</h1>
        <p>Hello, <?= htmlspecialchars($_SESSION['customer_name'] ?? 'Customer'); ?>! Your account is ready.</p>
        <p>You must log in before accessing this page.</p>
    </main>

    <?php require_once __DIR__ . '/layout/footer.php'; ?>
</body>
</html>
