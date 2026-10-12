<?php

require_once __DIR__ . '/../../core/core.php';

require_admin();

require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();
$editingCategory = false;

if (isset($_GET['edit_id'])) {
    $rawId = $_GET['edit_id'];
    $editId = is_string($rawId)
        ? filter_var(
            $rawId,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        )
        : false;

    if ($editId === false) {
        $_SESSION['error'] = 'Select a valid category to edit.';
        redirect(app_url('views/admin/category.php'));
    }

    $editingCategory = $controller->getCategoryById($editId);

    if (!$editingCategory) {
        $_SESSION['error'] = 'The selected category could not be found.';
        redirect(app_url('views/admin/category.php'));
    }
}

$categories = $controller->getAllCategories();
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories | Shoppn</title>
    <link
        rel="stylesheet"
        href="<?= htmlspecialchars(
            app_url('css/style.css')
                . '?v='
                . filemtime(__DIR__ . '/../../css/style.css'),
            ENT_QUOTES,
            'UTF-8'
        ); ?>"
    >
</head>
<body class="admin-page">
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="admin-content">
        <h1>Manage Categories</h1>

        <?php if ($successMessage !== ''): ?>
            <p class="success-message" role="status">
                <?= htmlspecialchars((string) $successMessage, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <p class="error-message" role="alert">
                <?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <section class="admin-panel" aria-labelledby="category-form-title">
            <h2 id="category-form-title">
                <?= $editingCategory ? 'Edit category' : 'Add a category'; ?>
            </h2>

            <form
                class="admin-form"
                method="post"
                action="<?= htmlspecialchars(
                    app_url(
                        $editingCategory
                            ? 'actions/update_category_action.php'
                            : 'actions/add_category_action.php'
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>"
            >
                <?php if ($editingCategory): ?>
                    <input
                        type="hidden"
                        name="cat_id"
                        value="<?= (int) $editingCategory['cat_id']; ?>"
                    >
                <?php endif; ?>

                <label for="category-name">Category name</label>
                <input
                    id="category-name"
                    type="text"
                    name="cat_name"
                    maxlength="100"
                    required
                    value="<?= htmlspecialchars(
                        $editingCategory ? $editingCategory['cat_name'] : '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                >

                <div class="admin-form-actions">
                    <button type="submit">
                        <?= $editingCategory ? 'Save changes' : 'Add category'; ?>
                    </button>
                    <?php if ($editingCategory): ?>
                        <a
                            class="admin-secondary-button"
                            href="<?= htmlspecialchars(
                                app_url('views/admin/category.php'),
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                        >Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="admin-panel" aria-labelledby="categories-title">
            <h2 id="categories-title">Categories</h2>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Category name</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$categories): ?>
                            <tr>
                                <td colspan="2">No categories have been added yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            $category['cat_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>
                                    <td class="admin-row-actions">
                                        <a
                                            class="admin-edit-button"
                                            href="<?= htmlspecialchars(
                                                app_url(
                                                    'views/admin/category.php?edit_id='
                                                        . (int) $category['cat_id']
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                        >Edit</a>
                                        <form
                                            method="post"
                                            action="<?= htmlspecialchars(
                                                app_url('actions/delete_category_action.php'),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                            onsubmit="return confirm('Are you sure you want to delete this category, all its brands, and their products? Products in order history will prevent deletion.');"
                                        >
                                            <input
                                                type="hidden"
                                                name="cat_id"
                                                value="<?= (int) $category['cat_id']; ?>"
                                            >
                                            <button
                                                class="admin-delete-button"
                                                type="submit"
                                            >Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
</body>
</html>
