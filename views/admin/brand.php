<?php

require_once __DIR__ . '/../../core/core.php';

require_admin();

require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();
$categories = $controller->getAllCategories();
$editingBrand = false;

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
        $_SESSION['error'] = 'Select a valid brand to edit.';
        redirect(app_url('views/admin/brand.php'));
    }

    $editingBrand = $controller->getBrandById($editId);

    if (!$editingBrand) {
        $_SESSION['error'] = 'The selected brand could not be found.';
        redirect(app_url('views/admin/brand.php'));
    }
}

$brands = $controller->getAllBrands();
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Brands | Shoppn</title>
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
        <h1>Manage Brands</h1>

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

        <section class="admin-panel" aria-labelledby="brand-form-title">
            <h2 id="brand-form-title">
                <?= $editingBrand ? 'Edit brand' : 'Add a brand'; ?>
            </h2>

            <?php if (!$categories): ?>
                <p>Add a category before creating a brand.</p>
                <a
                    class="admin-edit-button"
                    href="<?= htmlspecialchars(
                        app_url('views/admin/category.php'),
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                >Manage categories</a>
            <?php else: ?>
            <form
                class="admin-form"
                method="post"
                action="<?= htmlspecialchars(
                    app_url(
                        $editingBrand
                            ? 'actions/update_brand_action.php'
                            : 'actions/add_brand_action.php'
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>"
                enctype="multipart/form-data"
            >
                <?php if ($editingBrand): ?>
                    <input
                        type="hidden"
                        name="brand_id"
                        value="<?= (int) $editingBrand['brand_id']; ?>"
                    >
                <?php endif; ?>

                <label for="brand-name">Brand name</label>
                <input
                    id="brand-name"
                    type="text"
                    name="brand_name"
                    maxlength="100"
                    required
                    value="<?= htmlspecialchars(
                        $editingBrand ? $editingBrand['brand_name'] : '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                >

                <label for="brand-category">Category</label>
                <select id="brand-category" name="brand_cat" required>
                    <option value="">Choose a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?= (int) $category['cat_id']; ?>"
                            <?= $editingBrand
                                && (int) $editingBrand['brand_cat']
                                    === (int) $category['cat_id']
                                ? 'selected'
                                : ''; ?>
                        >
                            <?= htmlspecialchars(
                                $category['cat_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if ($editingBrand && !empty($editingBrand['brand_image'])): ?>
                    <p>Current logo</p>
                    <img
                        class="admin-brand-logo-preview"
                        src="<?= htmlspecialchars(
                            app_url($editingBrand['brand_image']),
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                        alt="<?= htmlspecialchars(
                            $editingBrand['brand_name'] . ' logo',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                    >
                    <label class="admin-brand-remove">
                        <input type="checkbox" name="remove_brand_image" value="1">
                        Remove current logo
                    </label>
                <?php endif; ?>

                <label for="brand-image">Brand logo</label>
                <input
                    id="brand-image"
                    type="file"
                    name="brand_image"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                >
                <small>Optional. Choose a JPG, PNG, GIF, or WebP image up to 5 MB.</small>

                <div class="admin-form-actions">
                    <button type="submit">
                        <?= $editingBrand ? 'Save changes' : 'Add brand'; ?>
                    </button>
                    <?php if ($editingBrand): ?>
                        <a
                            class="admin-secondary-button"
                            href="<?= htmlspecialchars(
                                app_url('views/admin/brand.php'),
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                        >Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
            <?php endif; ?>
        </section>

        <section class="admin-panel" aria-labelledby="brands-title">
            <h2 id="brands-title">Brands</h2>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Logo</th>
                            <th scope="col">Brand name</th>
                            <th scope="col">Category</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$brands): ?>
                            <tr>
                                <td colspan="4">No brands have been added yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($brands as $brand): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($brand['brand_image'])): ?>
                                            <img
                                                class="admin-brand-logo-thumbnail"
                                                src="<?= htmlspecialchars(
                                                    app_url($brand['brand_image']),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>"
                                                alt="<?= htmlspecialchars(
                                                    $brand['brand_name'] . ' logo',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>"
                                            >
                                        <?php else: ?>
                                            <span class="admin-brand-no-logo">No logo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(
                                            $brand['brand_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(
                                            $brand['cat_name'] ?? 'Unassigned',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>
                                    <td class="admin-row-actions">
                                        <a
                                            class="admin-edit-button"
                                            href="<?= htmlspecialchars(
                                                app_url(
                                                    'views/admin/brand.php?edit_id='
                                                        . (int) $brand['brand_id']
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                        >Edit</a>
                                        <form
                                            method="post"
                                            action="<?= htmlspecialchars(
                                                app_url('actions/delete_brand_action.php'),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                            onsubmit="return confirm('Are you sure you want to delete this brand and all its products? Products in order history will prevent deletion.');"
                                        >
                                            <input
                                                type="hidden"
                                                name="brand_id"
                                                value="<?= (int) $brand['brand_id']; ?>"
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
