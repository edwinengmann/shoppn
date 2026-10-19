<?php

require_once __DIR__ . '/../core/core.php';

require_login();

require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../classes/CartClass.php';

$rawId = $_GET['id'] ?? null;
$productId = is_string($rawId)
    ? filter_var(
        $rawId,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;

if ($productId === false) {
    http_response_code(404);
    $product = false;
    $sizeOptions = [];
} else {
    $controller = new ProductController();
    $product = $controller->getProductById($productId);
    if (!$product) {
        http_response_code(404);
        $sizeOptions = [];
    } else {
        $sizeOptions = CartClass::getSizeOptions($product);
    }
}

function product_detail_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product
        ? product_detail_escape($product['product_title']) . ' | Shoppn'
        : 'Product not found | Shoppn'; ?></title>
    <link
        rel="stylesheet"
        href="<?= product_detail_escape(
            app_url('css/style.css')
                . '?v='
                . filemtime(__DIR__ . '/../css/style.css')
        ); ?>"
    >
</head>
<body class="catalog-page">
    <?php require __DIR__ . '/layout/header.php'; ?>

    <main class="catalog-content catalog-detail-content">
        <?php if (!$product): ?>
            <section class="catalog-section">
                <h1>Product not found</h1>
                <p class="catalog-empty">This product is no longer available.</p>
                <a class="catalog-back-link" href="<?= product_detail_escape(
                    app_url('views/all_products.php')
                ); ?>">Return to the shop</a>
            </section>
        <?php else: ?>
            <p class="catalog-breadcrumbs">
                <a href="<?= product_detail_escape(app_url('views/all_products.php')); ?>">
                    Shop
                </a>
                <span aria-hidden="true">/</span>
                <a href="<?= product_detail_escape(
                    app_url(
                        'views/all_products.php?category='
                            . (int) $product['product_cat']
                    )
                ); ?>">
                    <?= product_detail_escape($product['cat_name']); ?>
                </a>
                <span aria-hidden="true">/</span>
                <a href="<?= product_detail_escape(
                    app_url(
                        'views/all_products.php?category='
                            . (int) $product['product_cat']
                            . '&brand='
                            . (int) $product['product_brand']
                    )
                ); ?>">
                    <?= product_detail_escape($product['brand_name']); ?>
                </a>
            </p>

            <article class="catalog-product-detail">
                <div class="catalog-product-detail-image">
                    <?php if (!empty($product['product_image'])): ?>
                        <img
                            src="<?= product_detail_escape(app_url($product['product_image'])); ?>"
                            alt="<?= product_detail_escape($product['product_title']); ?>"
                        >
                    <?php else: ?>
                        <div class="catalog-product-image-placeholder">
                            Image coming soon
                        </div>
                    <?php endif; ?>
                </div>
                <div class="catalog-product-detail-copy">
                    <p class="catalog-product-brand">
                        <?= product_detail_escape($product['brand_name']); ?>
                    </p>
                    <h1><?= product_detail_escape($product['product_title']); ?></h1>
                    <p class="catalog-product-category">
                        <?= product_detail_escape($product['cat_name']); ?>
                    </p>
                    <p class="catalog-product-detail-price">
                        ₵
                        <?= product_detail_escape(
                            number_format((float) $product['product_price'], 2)
                        ); ?>
                    </p>
                    <?php if (!empty($product['product_desc'])): ?>
                        <p class="catalog-product-description">
                            <?= nl2br(product_detail_escape($product['product_desc'])); ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['error'])): ?>
                        <p class="cart-message cart-message-error" role="alert">
                            <?= product_detail_escape($_SESSION['error']); ?>
                        </p>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    <form
                        class="cart-add-form"
                        method="post"
                        action="<?= product_detail_escape(
                            app_url('actions/add_to_cart_action.php')
                        ); ?>"
                    >
                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int) $product['product_id']; ?>"
                        >
                        <?php if ($sizeOptions): ?>
                            <div class="cart-add-field">
                                <label for="product-size">
                                    <?= in_array(
                                        $sizeOptions[0],
                                        CartClass::FOOTWEAR_SIZES,
                                        true
                                    )
                                        ? 'Footwear size (EU)'
                                        : 'Clothing size'; ?>
                                </label>
                                <select id="product-size" name="size" required>
                                    <option value="">Choose a size</option>
                                    <?php foreach ($sizeOptions as $size): ?>
                                        <option value="<?= product_detail_escape($size); ?>">
                                            <?= product_detail_escape($size); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="size" value="">
                        <?php endif; ?>
                        <div class="cart-add-field">
                            <label for="product-quantity">Quantity</label>
                            <input
                                id="product-quantity"
                                type="number"
                                name="qty"
                                min="1"
                                max="<?= CartClass::MAX_QUANTITY; ?>"
                                value="1"
                                required
                            >
                        </div>
                        <button type="submit">Add to cart</button>
                    </form>
                    <a class="catalog-back-link" href="<?= product_detail_escape(
                        app_url(
                            'views/all_products.php?category='
                                . (int) $product['product_cat']
                                . '&brand='
                                . (int) $product['product_brand']
                        )
                    ); ?>">More from <?= product_detail_escape($product['brand_name']); ?></a>
                </div>
            </article>
        <?php endif; ?>
    </main>

    <?php require __DIR__ . '/layout/footer.php'; ?>
</body>
</html>