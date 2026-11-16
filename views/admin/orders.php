<?php

require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../classes/OrderPaymentClass.php';

require_admin();

$orders = (new OrderPaymentClass())->getAllOrders();
$escape = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders &amp; Payments | Shoppn</title>
    <link
        rel="stylesheet"
        href="<?= $escape(
            app_url('css/style.css')
                . '?v='
                . filemtime(__DIR__ . '/../../css/style.css')
        ); ?>"
    >
</head>
<body class="admin-page">
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="admin-content">
        <h1>Orders &amp; Payments</h1>
        <p>
            Paystack transfers funds to the settlement account configured in
            your Paystack Dashboard. Verify that account before accepting live
            payments.
        </p>

        <section class="admin-panel" aria-labelledby="orders-title">
            <h2 id="orders-title">Customer orders</h2>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Customer &amp; delivery</th>
                            <th scope="col">Products</th>
                            <th scope="col">Total</th>
                            <th scope="col">Order status</th>
                            <th scope="col">Payment</th>
                            <th scope="col">Reference / transaction</th>
                            <th scope="col">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$orders): ?>
                            <tr>
                                <td colspan="8">No orders have been placed yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?= (int) $order['order_id']; ?></td>
                                    <td>
                                        <strong><?= $escape($order['customer_name']); ?></strong><br>
                                        <?= $escape($order['customer_email']); ?><br>
                                        <?= $escape($order['customer_contact']); ?><br>
                                        <?= nl2br($escape($order['delivery_address'])); ?><br>
                                        <?= $escape($order['delivery_city']); ?>,
                                        <?= $escape($order['delivery_country']); ?>
                                    </td>
                                    <td>
                                        <?php foreach ($order['items'] as $item): ?>
                                            <?= $escape($item['title']); ?>
                                            <?php if ($item['size'] !== ''): ?>
                                                (<?= $escape($item['size']); ?>)
                                            <?php endif; ?>
                                            × <?= (int) $item['quantity']; ?>
                                            — <?= $escape($order['currency']); ?>
                                            <?= $escape(number_format((float) $item['unit_price'], 2)); ?><br>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <?= $escape($order['currency']); ?>
                                        <?= $escape(number_format((float) $order['amount'], 2)); ?>
                                    </td>
                                    <td><?= $escape(ucfirst($order['order_status'])); ?></td>
                                    <td>
                                        <?= $escape(ucfirst($order['payment_status'])); ?><br>
                                        Available: <?= $escape(str_replace(',', ', ', $order['offered_channels'])); ?><br>
                                        <?php if ($order['payment_method'] !== null): ?>
                                            Method: <?= $escape($order['payment_method']); ?><br>
                                        <?php endif; ?>
                                        <?php if ($order['paid_at'] !== null): ?>
                                            Confirmed: <?= $escape($order['paid_at']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $escape($order['reference']); ?><br>
                                        <?php if ($order['transaction_id'] !== null): ?>
                                            <?= $escape($order['transaction_id']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $escape($order['created_at']); ?></td>
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
