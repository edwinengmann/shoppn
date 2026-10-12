<?php

require_once __DIR__ . '/../core/core.php';

require_admin();

$redirectUrl = app_url('views/admin/category.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please submit the category form.';
    redirect($redirectUrl);
}

$rawId = $_POST['cat_id'] ?? null;
$categoryId = is_string($rawId)
    ? filter_var(
        $rawId,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;
$submittedName = $_POST['cat_name'] ?? '';
$name = is_string($submittedName)
    ? strip_tags(trim($submittedName))
    : '';

if ($categoryId === false) {
    $_SESSION['error'] = 'Select a valid category to update.';
    redirect($redirectUrl);
}

if ($name === '' || strlen($name) > 100) {
    $_SESSION['error'] = 'Enter a category name between 1 and 100 characters.';
    redirect($redirectUrl);
}

require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

if (!$controller->getCategoryById($categoryId)) {
    $_SESSION['error'] = 'The selected category no longer exists.';
    redirect($redirectUrl);
}

if (!$controller->updateCategory($categoryId, $name)) {
    $_SESSION['error'] = 'The category could not be updated. Please try again.';
    redirect($redirectUrl);
}

$_SESSION['success'] = 'Category updated.';
redirect($redirectUrl);

?>
