<?php

require_once __DIR__ . '/../core/core.php';

require_admin();

$redirectUrl = app_url('views/admin/product.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please submit the product form.';
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
$rawCategoryId = $_POST['product_cat'] ?? null;
$categoryId = is_string($rawCategoryId)
    ? filter_var(
        $rawCategoryId,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;
$rawBrandId = $_POST['product_brand'] ?? null;
$brandId = is_string($rawBrandId)
    ? filter_var(
        $rawBrandId,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;
$submittedTitle = $_POST['product_title'] ?? '';
$title = is_string($submittedTitle)
    ? trim(strip_tags($submittedTitle))
    : '';
$rawPrice = $_POST['product_price'] ?? '';
$price = is_string($rawPrice)
    ? filter_var($rawPrice, FILTER_VALIDATE_FLOAT)
    : false;
$submittedDescription = $_POST['product_desc'] ?? '';
$description = is_string($submittedDescription)
    ? trim(strip_tags($submittedDescription))
    : '';
$submittedKeywords = $_POST['product_keywords'] ?? '';
$keywords = is_string($submittedKeywords)
    ? trim(strip_tags($submittedKeywords))
    : '';

if (
    $productId === false
    || $categoryId === false
    || $brandId === false
) {
    $_SESSION['error'] = 'Select a valid product, category, and brand.';
    redirect($redirectUrl);
}

if ($title === '' || strlen($title) > 200) {
    $_SESSION['error'] = 'Enter a product name between 1 and 200 characters.';
    redirect($redirectUrl . '?edit_id=' . $productId);
}

if ($price === false || $price <= 0 || $price > 999999999) {
    $_SESSION['error'] = 'Enter a valid product price greater than zero.';
    redirect($redirectUrl . '?edit_id=' . $productId);
}

if (strlen($description) > 500 || strlen($keywords) > 100) {
    $_SESSION['error'] = 'The description or search keywords are too long.';
    redirect($redirectUrl . '?edit_id=' . $productId);
}

require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();
$product = $controller->getProductById($productId);
$brand = $controller->getBrandById($brandId);
if (!$product) {
    $_SESSION['error'] = 'The selected product no longer exists.';
    redirect($redirectUrl);
}
if (
    !$controller->getCategoryById($categoryId)
    || !$brand
    || (int) $brand['brand_cat'] !== $categoryId
) {
    $_SESSION['error'] = 'Choose a brand that belongs to the selected category.';
    redirect($redirectUrl . '?edit_id=' . $productId);
}

require_once __DIR__ . '/../core/product_image.php';

$upload = save_product_image_upload($_FILES['product_image'] ?? []);
if ($upload['error'] !== null) {
    $_SESSION['error'] = $upload['error'];
    redirect($redirectUrl . '?edit_id=' . $productId);
}

$removeImage = isset($_POST['remove_product_image'])
    && $_POST['remove_product_image'] === '1';
$imagePath = $upload['path'] ?? (
    $removeImage ? null : ($product['product_image'] ?? null)
);
if (
    !$controller->updateProduct(
        $productId,
        $categoryId,
        $brandId,
        $title,
        (float) $price,
        $description,
        $imagePath,
        $keywords
    )
) {
    if ($upload['path'] !== null) {
        delete_product_image($upload['path']);
    }
    $_SESSION['error'] = 'The product could not be updated. Please try again.';
    redirect($redirectUrl . '?edit_id=' . $productId);
}

if ($upload['path'] !== null || $removeImage) {
    delete_product_image($product['product_image'] ?? null);
}

$_SESSION['success'] = 'Product updated.';
redirect($redirectUrl);
