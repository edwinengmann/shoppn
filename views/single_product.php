<?php

require_once __DIR__ . '/../core/core.php';

require_login();

require_once __DIR__ . '/../controllers/ProductController.php';

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
} else {
    $controller = new ProductController();
    $product = $controller->getProductById($productId);
    if (!$product) {
        http_response_code(404);
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