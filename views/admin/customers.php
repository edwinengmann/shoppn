<?php

require_once __DIR__ . '/../../core/core.php';

require_admin();

require_once __DIR__ . '/../../controllers/CustomerController.php';

$controller = new CustomerController();
$customers = $controller->getAllCustomers();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Customers | Shoppn</title>
    <link
        rel="stylesheet"
        href="<?= htmlspecialchars(
            app_url('css/style.css')
                . '?v='
                . filemtime(__DIR__ . '/../../css/style.css'),
            ENT_QUOTES,
            'UTF-8'
        ); ?>"
    >
</head>
<body class="admin-page">
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="admin-content">
        <h1>Registered Customers</h1>

        <section class="admin-panel" aria-labelledby="customers-title">
            <h2 id="customers-title">Customer details</h2>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Country</th>
                            <th scope="col">City</th>
                            <th scope="col">Contact number</th>
                            <th scope="col">Account type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$customers): ?>
                            <tr>
                                <td colspan="6">No customers have registered yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?= htmlspecialchars($customer['customer_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($customer['customer_email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($customer['customer_country'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($customer['customer_city'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($customer['customer_contact'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= (int) $customer['user_role'] === 1 ? 'Admin' : 'Customer'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
</body>
</html>
