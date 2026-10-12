<?php

require_once __DIR__ . '/../core/core.php';

require_admin();

$redirectUrl = app_url('views/admin/brand.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please submit the brand form.';
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
$submittedName = $_POST['brand_name'] ?? '';
$name = is_string($submittedName)
    ? strip_tags(trim($submittedName))
    : '';
$rawCategoryId = $_POST['brand_cat'] ?? null;
$categoryId = is_string($rawCategoryId)
    ? filter_var(
        $rawCategoryId,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;

if ($brandId === false) {
    $_SESSION['error'] = 'Select a valid brand to update.';
    redirect($redirectUrl);
}

if ($name === '' || strlen($name) > 100) {
    $_SESSION['error'] = 'Enter a brand name between 1 and 100 characters.';
    redirect($redirectUrl);
}

if ($categoryId === false) {
    $_SESSION['error'] = 'Select a category for this brand.';
    redirect($redirectUrl);
}

require_once __DIR__ . '/../core/brand_image.php';

require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

$brand = $controller->getBrandById($brandId);
if (!$brand) {
    $_SESSION['error'] = 'The selected brand no longer exists.';
    redirect($redirectUrl);
}

if (!$controller->getCategoryById($categoryId)) {
    $_SESSION['error'] = 'The selected category could not be found.';
    redirect($redirectUrl);
}

$removeImage = isset($_POST['remove_brand_image'])
    && $_POST['remove_brand_image'] === '1';
$upload = save_brand_image_upload($_FILES['brand_image'] ?? []);
if ($upload['error'] !== null) {
    $_SESSION['error'] = $upload['error'];
    redirect($redirectUrl);
}

$imagePath = $upload['path'] ?? (
    $removeImage ? null : ($brand['brand_image'] ?? null)
);

if (!$controller->updateBrand($brandId, $name, $categoryId, $imagePath)) {
    if ($upload['path'] !== null) {
        delete_brand_image($upload['path']);
    }
    $_SESSION['error'] = 'The brand could not be updated. Please try again.';
    redirect($redirectUrl);
}

if ($upload['path'] !== null || $removeImage) {
    delete_brand_image($brand['brand_image'] ?? null);
}

$_SESSION['success'] = 'Brand updated.';
redirect($redirectUrl);

?>
