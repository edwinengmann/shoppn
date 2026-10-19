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
$cartUrl = app_url('views/cart.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please submit the cart removal form.';
    redirect($cartUrl);
}

if (
    $productId === false
) {
    $_SESSION['error'] = 'Select a valid item to remove.';
    redirect($cartUrl);
}

require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/CartController.php';

$productController = new ProductController();
$product = $productController->getProductById($productId);
if (!$product) {
    $_SESSION['error'] = 'This product is no longer available.';
    redirect($cartUrl);
}

$sizeOptions = CartClass::getSizeOptions($product);
if (
    ($sizeOptions && !in_array($size, $sizeOptions, true))
    || (!$sizeOptions && $size !== '')
) {
    $_SESSION['error'] = 'Select a valid item to remove.';
    redirect($cartUrl);
}

$cart = new CartController();
if (!$cart->removeItem((int) $_SESSION['customer_id'], $productId, $size)) {
    $_SESSION['error'] = 'The item could not be removed from your bag.';
    redirect($cartUrl);
}

$_SESSION['success'] = 'Item removed from your shopping bag.';
redirect($cartUrl);