<?php

require_once __DIR__ . '/../core/core.php';

require_admin();

$redirectUrl = app_url('views/admin/brand.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please submit the brand form.';
    redirect($redirectUrl);
}

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

if ($name === '' || strlen($name) > 100) {
    $_SESSION['error'] = 'Enter a brand name between 1 and 100 characters.';
    redirect($redirectUrl);
}

if ($categoryId === false) {
    $_SESSION['error'] = 'Select a category for this brand.';
    redirect($redirectUrl);
}

require_once __DIR__ . '/../core/brand_image.php';

$upload = save_brand_image_upload($_FILES['brand_image'] ?? []);
if ($upload['error'] !== null) {
    $_SESSION['error'] = $upload['error'];
    redirect($redirectUrl);
}

require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

if (!$controller->getCategoryById($categoryId)) {
    if ($upload['path'] !== null) {
        delete_brand_image($upload['path']);
    }
    $_SESSION['error'] = 'The selected category could not be found.';
    redirect($redirectUrl);
}

if (!$controller->addBrand($name, $categoryId, $upload['path'])) {
    if ($upload['path'] !== null) {
        delete_brand_image($upload['path']);
    }
    $_SESSION['error'] = 'The brand could not be added. Please try again.';
    redirect($redirectUrl);
}

$_SESSION['success'] = 'Brand added.';
redirect($redirectUrl);

?>
