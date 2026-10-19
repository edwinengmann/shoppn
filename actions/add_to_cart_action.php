<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/CartClass.php';

require_login();

$productIdValue = $_POST['product_id'] ?? null;
$productId = is_string($productIdValue)
    ? filter_var(
        $productIdValue,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;
$sizeValue = $_POST['size'] ?? '';
$size = is_string($sizeValue) ? $sizeValue : '';
$quantityValue = $_POST['qty'] ?? '1';
$quantity = is_string($quantityValue)
    ? filter_var(
        $quantityValue,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 99]]
    )
    : false;
$productUrl = $productId === false
    ? app_url('views/all_products.php')
    : app_url('views/single_product.php?id=' . $productId);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please use the product form to add an item.';
    redirect(app_url('views/all_products.php'));
}

if ($productId === false || $quantity === false) {
    $_SESSION['error'] = 'Choose a valid quantity for this product.';
    redirect($productUrl);
}

require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/CartController.php';

$products = new ProductController();
$product = $products->getProductById($productId);
if (!$product) {
    $_SESSION['error'] = 'This product is no longer available.';
    redirect(app_url('views/all_products.php'));
}

$sizeOptions = CartClass::getSizeOptions($product);
if (
    ($sizeOptions && !in_array($size, $sizeOptions, true))
    || (!$sizeOptions && $size !== '')
) {
    $_SESSION['error'] = $sizeOptions
        ? 'Choose a valid size for this product.'
        : 'This product does not have size options.';
    redirect($productUrl);
}

$cartSaved = false;
try {
    $cart = new CartController();
    $cartSaved = $cart->addItem(
        (int) $_SESSION['customer_id'],
        $productId,
        $size,
        $quantity,
        get_ip()
    );
} catch (mysqli_sql_exception $exception) {
    error_log('Adding item to shopping cart failed: ' . $exception->getMessage());
}

if (!$cartSaved) {
    $_SESSION['error'] = 'The item could not be saved to your cart. Please try again or contact the store for help.';
    redirect($productUrl);
}

$_SESSION['success'] = 'Added to your shopping bag.';
redirect(app_url('views/cart.php'));