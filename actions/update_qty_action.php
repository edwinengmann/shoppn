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
$currentSizeValue = $_POST['cart_size'] ?? '';
$currentSize = is_string($currentSizeValue) ? $currentSizeValue : '';
$newSizeValue = $_POST['size'] ?? '';
$newSize = is_string($newSizeValue) ? $newSizeValue : '';
$quantityValue = $_POST['qty'] ?? null;
$quantity = is_string($quantityValue)
    ? filter_var(
        $quantityValue,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 99]]
    )
    : false;
$cartUrl = app_url('views/cart.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please submit the cart update form.';
    redirect($cartUrl);
}

if (
    $productId === false
    || $quantity === false
) {
    $_SESSION['error'] = 'Choose a valid quantity.';
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
$validCurrentSize = $sizeOptions
    ? in_array($currentSize, $sizeOptions, true)
    : $currentSize === '';
$validNewSize = $sizeOptions
    ? in_array($newSize, $sizeOptions, true)
    : $newSize === '';
if (!$validCurrentSize || !$validNewSize) {
    $_SESSION['error'] = 'Choose a valid size for this product.';
    redirect($cartUrl);
}

$cart = new CartController();
if (
    !$cart->updateItem(
        (int) $_SESSION['customer_id'],
        $productId,
        $currentSize,
        $newSize,
        $quantity
    )
) {
    $_SESSION['error'] = 'The item could not be updated. Check the quantity and try again.';
    redirect($cartUrl);
}

$_SESSION['success'] = 'Your shopping bag has been updated.';
redirect($cartUrl);