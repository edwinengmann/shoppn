<?php

require_once __DIR__ . '/../core/core.php';

require_admin();

$redirectUrl = app_url('views/admin/category.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please submit the category form.';
    redirect($redirectUrl);
}

$submittedName = $_POST['cat_name'] ?? '';
$name = is_string($submittedName)
    ? strip_tags(trim($submittedName))
    : '';

if ($name === '' || strlen($name) > 100) {
    $_SESSION['error'] = 'Enter a category name between 1 and 100 characters.';
    redirect($redirectUrl);
}

require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();

if (!$controller->addCategory($name)) {
    $_SESSION['error'] = 'The category could not be added. Please try again.';
    redirect($redirectUrl);
}

$_SESSION['success'] = 'Category added.';
redirect($redirectUrl);

?>
