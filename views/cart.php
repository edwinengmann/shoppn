<caption></caption>
<?php

require_once __DIR__ . '/../core/core.php';

require_login();

require_once __DIR__ . '/../controllers/CartController.php';

$cartLoadFailed = false;
try {
    $cartController = new CartController();
    $items = $cartController->getCartItems((int) $_SESSION['customer_id']);
} catch (mysqli_sql_exception $exception) {
    error_log('Loading shopping cart failed: ' . $exception->getMessage());
    $items = [];
    $cartLoadFailed = true;
}
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += (float) $item['product_price'] * (int) $item['qty'];
}

function cart_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your shopping bag | Shoppn</title>
    <link
        rel="stylesheet"
        href="<?= cart_escape(
            app_url('css/style.css')
                . '?v='
                . filemtime(__DIR__ . '/../css/style.css')
        ); ?>"
    >
</head>
<body class="catalog-page cart-page">
    <?php require __DIR__ . '/layout/header.php'; ?>

    <main class="catalog-content cart-content">
        <?php if ($successMessage !== ''): ?>
            <p class="cart-message cart-message-success" role="status">
                <?= cart_escape($successMessage); ?>
            </p>
        <?php endif; ?>
        <?php if ($errorMessage !== ''): ?>
            <p class="cart-message cart-message-error" role="alert">
                <?= cart_escape($errorMessage); ?>
            </p>
        <?php endif; ?>

        <?php if ($cartLoadFailed): ?>
            <section class="cart-empty-state" aria-labelledby="cart-heading">
                <h1 id="cart-heading">Your cart is unavailable</h1>
                <p>
                    We couldn't load your cart. Please contact the store for help.
                </p>
                <a
                    class="cart-primary-button cart-start-shopping"
                    href="<?= cart_escape(app_url('views/all_products.php')); ?>"
                >
                    Continue shopping
                </a>
            </section>
        <?php elseif (!$items): ?>
            <section class="cart-empty-state" aria-labelledby="cart-heading">
                <svg class="cart-empty-icon" viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M10 16h28l2 25H8l2-25Z"></path>
                    <path d="m10 16 5-8h18l5 8"></path>
                    <path d="M18 22a6 6 0 0 0 12 0"></path>
                </svg>
                <h1 id="cart-heading">Your cart is empty</h1>
                <p>Add something you love to get started.</p>
                <a
                    class="cart-primary-button cart-start-shopping"
                    href="<?= cart_escape(app_url('views/all_products.php')); ?>"
                >
                    Start shopping
                </a>
            </section>
        <?php else: ?>
            <header class="cart-heading">
                <p class="catalog-eyebrow">YOUR PICKS, ALL IN ONE PLACE</p>
                <div class="cart-heading-row">
                    <h1>Shopping bag</h1>
                    <p class="cart-item-count">
                        <?= count($items); ?>
                        <?= count($items) === 1 ? 'item' : 'items'; ?>
                    </p>
                </div>
            </header>

            <div class="cart-layout">
                <section class="cart-items" aria-label="Items in your shopping bag">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $productId = (int) $item['product_id'];
                        $price = (float) $item['product_price'];
                        $quantity = (int) $item['qty'];
                        $size = (string) $item['size'];
                        $sizeOptions = CartClass::getSizeOptions($item);
                        ?>
                        <article class="cart-item">
                            <a
                                class="cart-item-image"
                                href="<?= cart_escape(
                                    app_url(
                                        'views/single_product.php?id=' . $productId
                                    )
                                ); ?>"
                                aria-label="<?= cart_escape($item['product_title']); ?>"
                            >
                                <?php if (!empty($item['product_image'])): ?>
                                    <img
                                        src="<?= cart_escape(app_url($item['product_image'])); ?>"
                                        alt=""
                                    >
                                <?php else: ?>
                                    <span>Image coming soon</span>
                                <?php endif; ?>
                            </a>

                            <div class="cart-item-details">
                                <p class="cart-item-brand">
                                    <?= cart_escape($item['brand_name']); ?>
                                </p>
                                <h2>
                                    <a href="<?= cart_escape(
                                        app_url(
                                            'views/single_product.php?id=' . $productId
                                        )
                                    ); ?>">
                                        <?= cart_escape($item['product_title']); ?>
                                    </a>
                                </h2>
                                <?php if ($sizeOptions): ?>
                                    <p class="cart-item-size-label">
                                        <?= in_array(
                                            $sizeOptions[0],
                                            CartClass::FOOTWEAR_SIZES,
                                            true
                                        )
                                            ? 'Footwear size (EU): '
                                            : 'Clothing size: '; ?>
                                        <?= cart_escape($size); ?>
                                    </p>
                                <?php endif; ?>

                                <form
                                    class="cart-update-form"
                                    method="post"
                                    action="<?= cart_escape(
                                        app_url('actions/update_qty_action.php')
                                    ); ?>"
                                >
                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?= $productId; ?>"
                                    >
                                    <input
                                        type="hidden"
                                        name="cart_size"
                                        value="<?= cart_escape($size); ?>"
                                    >
                                    <label
                                        class="visually-hidden"
                                        for="cart-quantity-<?= $productId . '-' . cart_escape(
                                            strtolower(str_replace(' ', '-', $size))
                                        ); ?>"
                                    >
                                        Quantity for <?= cart_escape($item['product_title']); ?>
                                    </label>
                                    <div class="cart-item-controls">
                                        <div class="cart-quantity-control">
                                            <button
                                                type="button"
                                                data-quantity-step="-1"
                                                aria-label="Decrease quantity"
                                            >−</button>
                                            <input
                                                id="cart-quantity-<?= $productId . '-' . cart_escape(
                                                    strtolower(str_replace(' ', '-', $size))
                                                ); ?>"
                                                type="number"
                                                name="qty"
                                                min="1"
                                                max="<?= CartClass::MAX_QUANTITY; ?>"
                                                value="<?= $quantity; ?>"
                                                required
                                            >
                                            <button
                                                type="button"
                                                data-quantity-step="1"
                                                aria-label="Increase quantity"
                                            >+</button>
                                        </div>
                                        <?php if ($sizeOptions): ?>
                                            <label class="cart-size-select">
                                                <span class="visually-hidden">
                                                    <?= in_array(
                                                        $sizeOptions[0],
                                                        CartClass::FOOTWEAR_SIZES,
                                                        true
                                                    )
                                                        ? 'Footwear size (EU)'
                                                        : 'Clothing size'; ?>
                                                </span>
                                                <select name="size" aria-label="Choose size">
                                                    <?php foreach ($sizeOptions as $availableSize): ?>
                                                        <option
                                                            value="<?= cart_escape($availableSize); ?>"
                                                            <?= $availableSize === $size
                                                                ? 'selected'
                                                                : ''; ?>
                                                        >
                                                            <?= cart_escape($availableSize); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </label>
                                        <?php else: ?>
                                            <input type="hidden" name="size" value="">
                                        <?php endif; ?>
                                        <button class="cart-update-button" type="submit">
                                            Update
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="cart-item-price">
                                <p class="cart-item-line-total">
                                    ₵<?= cart_escape(number_format($price * $quantity, 2)); ?>
                                </p>
                                <p>₵<?= cart_escape(number_format($price, 2)); ?> each</p>
                                <form
                                    method="post"
                                    action="<?= cart_escape(
                                        app_url('actions/remove_from_cart_action.php')
                                    ); ?>"
                                >
                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?= $productId; ?>"
                                    >
                                    <input
                                        type="hidden"
                                        name="size"
                                        value="<?= cart_escape($size); ?>"
                                    >
                                    <button class="cart-remove-button" type="submit">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>

                <aside class="cart-summary" aria-labelledby="cart-summary-heading">
                    <div class="cart-summary-row">
                        <h2 id="cart-summary-heading">Subtotal</h2>
                        <p>₵<?= cart_escape(number_format($subtotal, 2)); ?></p>
                    </div>
                    <p class="cart-summary-note">
                        Shipping and taxes are calculated at checkout.
                    </p>
                    <a
                        class="cart-primary-button cart-checkout-button"
                        href="<?= cart_escape(app_url('views/checkout.php')); ?>"
                    >
                        Proceed to checkout
                        <span>· ₵<?= cart_escape(number_format($subtotal, 2)); ?></span>
                    </a>
                    <a
                        class="cart-continue-link"
                        href="<?= cart_escape(app_url('views/all_products.php')); ?>"
                    >
                        Continue shopping
                    </a>
                </aside>
            </div>
        <?php endif; ?>
    </main>

    <?php require __DIR__ . '/layout/footer.php'; ?>
    <script>
        document.querySelectorAll('.cart-quantity-control').forEach((control) => {
            const input = control.querySelector('input[name="qty"]');
            control.querySelectorAll('[data-quantity-step]').forEach((button) => {
                button.addEventListener('click', () => {
                    const step = Number(button.dataset.quantityStep);
                    const minimum = Number(input.min);
                    const maximum = Number(input.max);
                    input.value = Math.min(
                        maximum,
                        Math.max(minimum, Number(input.value) + step)
                    );
                    input.form.requestSubmit();
                });
            });
        });
    </script>
</body>
</html>