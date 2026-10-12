<?php

require_once __DIR__ . '/../core/core.php';

require_admin();

$redirectUrl = app_url('views/admin/brand.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please submit the brand deletion form.';
    redirect($redirectUrl);
}

$rawId = $_POST['brand_id'] ?? null;
$brandId = is_string($rawId)
    ? filter_var(
        $rawId,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;

if ($brandId === false) {
    $_SESSION['error'] = 'Select a valid brand to delete.';
    redirect($redirectUrl);
}

require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../core/brand_image.php';
require_once __DIR__ . '/../core/product_image.php';

$result = (new ProductController())->deleteBrand($brandId);
if (!$result['success']) {
    $_SESSION['error'] = $result['error'];
    redirect($redirectUrl);
}

foreach ($result['images'] as $image) {
    delete_brand_image($image);
    delete_product_image($image);
}

$_SESSION['success'] = 'Brand and its products deleted.';
redirect($redirectUrl);
