<?php

require_once __DIR__ . '/../core/core.php';

require_login();

require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

$customerId = (int) $_SESSION['customer_id'];
$cartController = new CartController();
$items = $cartController->getCartItems($customerId);

if (!$items) {
    $_SESSION['error'] = 'Add an item to your shopping bag before checkout.';
    redirect(app_url('views/cart.php'));
}

$customerController = new CustomerController();
$customer = $customerController->getCustomerById($customerId);
if (!$customer) {
    $_SESSION['error'] = 'Your account could not be found. Please log in again.';
    redirect(app_url('logout.php'));
}

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += (float) $item['product_price'] * (int) $item['qty'];
}

function checkout_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Shoppn</title>
    <link
        rel="stylesheet"
        href="<?= checkout_escape(
            app_url('css/style.css')
                . '?v='
                . filemtime(__DIR__ . '/../css/style.css')
        ); ?>"
    >
</head>
<body class="catalog-page cart-page">
    <?php require __DIR__ . '/layout/header.php'; ?>

    <main class="catalog-content cart-checkout-content">
        <header class="cart-heading">
            <p class="catalog-eyebrow">ALMOST YOURS</p>
            <h1>Checkout</h1>
            <p>Review your order and delivery details.</p>
        </header>

        <div class="checkout-review">
            <section class="checkout-panel" aria-labelledby="checkout-items-heading">
                <h2 id="checkout-items-heading">Your order</h2>
                <?php foreach ($items as $item): ?>
                    <div class="checkout-item">
                        <span>
                            <?= checkout_escape($item['product_title']); ?>
                            <br>
                            <?= (int) $item['qty']; ?>
                            <?php if ($item['size'] !== ''): ?>
                                × <?= checkout_escape($item['size']); ?>
                            <?php endif; ?>
                        </span>
                        <strong>
                            ₵<?= checkout_escape(
                                number_format(
                                    (float) $item['product_price'] * (int) $item['qty'],
                                    2
                                )
                            ); ?>
                        </strong>
                    </div>
                <?php endforeach; ?>
                <div class="cart-summary-row checkout-subtotal">
                    <h2>Subtotal</h2>
                    <p>₵<?= checkout_escape(number_format($subtotal, 2)); ?></p>
                </div>
                <p class="checkout-note">
                    Shipping and taxes are calculated at checkout.
                </p>
            </section>

            <section class="checkout-panel" aria-labelledby="checkout-delivery-heading">
                <h2 id="checkout-delivery-heading">Delivery details</h2>
                <p class="checkout-address">
                    <strong><?= checkout_escape($customer['customer_name']); ?></strong><br>
                    <?= checkout_escape($customer['customer_email']); ?><br>
                    <?= checkout_escape($customer['customer_contact']); ?><br>
                    <?php if (!empty($customer['customer_address'])): ?>
                        <?= nl2br(checkout_escape($customer['customer_address'])); ?><br>
                    <?php endif; ?>
                    <?= checkout_escape($customer['customer_city']); ?>,
                    <?= checkout_escape($customer['customer_country']); ?>
                </p>
                <a
                    class="catalog-back-link"
                    href="<?= checkout_escape(app_url('views/account/my_account.php')); ?>"
                >
                    Edit account details
                </a>
                <p class="checkout-note">
                    Online payment is not configured yet. Your bag is saved while
                    you review your details.
                </p>
                <a
                    class="cart-primary-button"
                    href="<?= checkout_escape(app_url('views/cart.php')); ?>"
                >
                    Return to your bag
                </a>
            </section>
        </div>
    </main>

    <?php require __DIR__ . '/layout/footer.php'; ?>
</body>
</html>