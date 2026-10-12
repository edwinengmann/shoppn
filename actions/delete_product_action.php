<?php

require_once __DIR__ . '/../core/core.php';

require_admin();

$redirectUrl = app_url('views/admin/product.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please submit the product deletion form.';
    redirect($redirectUrl);
}

$rawId = $_POST['product_id'] ?? null;
$productId = is_string($rawId)
    ? filter_var(
        $rawId,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;

if ($productId === false) {
    $_SESSION['error'] = 'Select a valid product to delete.';
    redirect($redirectUrl);
}

require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../core/product_image.php';

$result = (new ProductController())->deleteProduct($productId);
if (!$result['success']) {
    $_SESSION['error'] = $result['error'];
    redirect($redirectUrl);
}

foreach ($result['images'] as $image) {
    delete_product_image($image);
}

$_SESSION['success'] = 'Product and its uploaded image deleted.';
redirect($redirectUrl);
