<?php

require_once __DIR__ . '/../../core/core.php';

require_admin();

require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();
$categories = $controller->getAllCategories();
$brands = array_values(array_filter(
    $controller->getAllBrands(),
    function ($brand) {
        return !empty($brand['brand_cat']);
    }
));
$products = $controller->getAllProducts();
$editingProduct = false;
if (isset($_GET['edit_id'])) {
    $rawEditId = $_GET['edit_id'];
    $editId = is_string($rawEditId)
        ? filter_var(
            $rawEditId,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        )
        : false;

    if ($editId === false) {
        $_SESSION['error'] = 'Select a valid product to edit.';
        redirect(app_url('views/admin/product.php'));
    }

    $editingProduct = $controller->getProductById($editId);
    if (!$editingProduct) {
        $_SESSION['error'] = 'The selected product could not be found.';
        redirect(app_url('views/admin/product.php'));
    }
}
$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | Shoppn</title>
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
        <h1>Manage Products</h1>

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

        <section class="admin-panel" aria-labelledby="product-form-title">
            <h2 id="product-form-title">
                <?= $editingProduct ? 'Edit product' : 'Add a product'; ?>
            </h2>
            <?php if (!$categories || !$brands): ?>
                <p>Add at least one category and a brand assigned to it before adding products.</p>
                <div class="admin-form-actions">
                    <a class="admin-edit-button" href="<?= htmlspecialchars(
                        app_url('views/admin/category.php'),
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">Manage categories</a>
                    <a class="admin-edit-button" href="<?= htmlspecialchars(
                        app_url('views/admin/brand.php'),
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>">Manage brands</a>
                </div>
            <?php else: ?>
                <form
                    class="admin-form"
                    method="post"
                    action="<?= htmlspecialchars(
                        app_url(
                            $editingProduct
                                ? 'actions/update_product_action.php'
                                : 'actions/add_product_action.php'
                        ),
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                    enctype="multipart/form-data"
                >
                    <?php if ($editingProduct): ?>
                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int) $editingProduct['product_id']; ?>"
                        >
                    <?php endif; ?>

                    <label for="product-title">Product name</label>
                    <input
                        id="product-title"
                        type="text"
                        name="product_title"
                        maxlength="200"
                        required
                        value="<?= htmlspecialchars(
                            $editingProduct ? $editingProduct['product_title'] : '',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                    >

                    <label for="product-price">Price</label>
                    <input
                        id="product-price"
                        type="number"
                        name="product_price"
                        min="0.01"
                        step="0.01"
                        required
                        value="<?= $editingProduct
                            ? htmlspecialchars(
                                (string) $editingProduct['product_price'],
                                ENT_QUOTES,
                                'UTF-8'
                            )
                            : ''; ?>"
                    >

                    <label for="product-category">Category</label>
                    <select id="product-category" name="product_cat" required>
                        <option value="">Choose a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option
                                value="<?= (int) $category['cat_id']; ?>"
                                <?= $editingProduct
                                    && (int) $editingProduct['product_cat']
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

                    <label for="product-brand">Brand</label>
                    <select
                        id="product-brand"
                        name="product_brand"
                        required
                        <?= $editingProduct ? '' : 'disabled'; ?>
                    >
                        <option value="">
                            <?= $editingProduct
                                ? 'Choose a brand'
                                : 'Choose a category first'; ?>
                        </option>
                        <?php foreach ($brands as $brand): ?>
                            <option
                                value="<?= (int) $brand['brand_id']; ?>"
                                data-category="<?= (int) $brand['brand_cat']; ?>"
                                hidden
                                <?= $editingProduct
                                    && (int) $editingProduct['product_brand']
                                        === (int) $brand['brand_id']
                                    ? 'selected'
                                    : ''; ?>
                            >
                                <?= htmlspecialchars(
                                    $brand['brand_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if ($editingProduct && !empty($editingProduct['product_image'])): ?>
                        <p>Current image</p>
                        <img
                            class="admin-product-preview"
                            src="<?= htmlspecialchars(
                                app_url($editingProduct['product_image']),
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            alt="<?= htmlspecialchars(
                                $editingProduct['product_title'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                        >
                        <label>
                            <input
                                type="checkbox"
                                name="remove_product_image"
                                value="1"
                            >
                            Remove current image
                        </label>
                    <?php endif; ?>

                    <label for="product-image">
                        <?= $editingProduct
                            ? 'Replace product image'
                            : 'Product image'; ?>
                    </label>
                    <input
                        id="product-image"
                        type="file"
                        name="product_image"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                        <?= $editingProduct ? '' : 'required'; ?>
                    >
                    <small>
                        <?= $editingProduct
                            ? 'Optional. '
                            : ''; ?>JPG, PNG, GIF, or WebP image up to 5 MB.
                    </small>

                    <label for="product-description">Description</label>
                    <textarea
                        id="product-description"
                        name="product_desc"
                        maxlength="500"
                        rows="5"
                    ><?= htmlspecialchars(
                        $editingProduct ? ($editingProduct['product_desc'] ?? '') : '',
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?></textarea>

                    <label for="product-keywords">Search keywords</label>
                    <input
                        id="product-keywords"
                        type="text"
                        name="product_keywords"
                        maxlength="100"
                        value="<?= htmlspecialchars(
                            $editingProduct
                                ? ($editingProduct['product_keywords'] ?? '')
                                : '',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                    >
                    <small>Optional comma-separated terms to help users find this product.</small>

                    <div class="admin-form-actions">
                        <button type="submit">
                            <?= $editingProduct ? 'Save changes' : 'Add product'; ?>
                        </button>
                        <?php if ($editingProduct): ?>
                            <a
                                class="admin-secondary-button"
                                href="<?= htmlspecialchars(
                                    app_url('views/admin/product.php'),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                            >Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>
        </section>

        <section class="admin-panel" aria-labelledby="products-title">
            <h2 id="products-title">Products</h2>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Image</th>
                            <th scope="col">Product</th>
                            <th scope="col">Category</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Price</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$products): ?>
                            <tr>
                                <td colspan="6">No products have been added yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['product_image'])): ?>
                                            <img
                                                class="admin-product-thumbnail"
                                                src="<?= htmlspecialchars(
                                                    app_url($product['product_image']),
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>"
                                                alt=""
                                            >
                                        <?php else: ?>
                                            No image
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars(
                                        $product['product_title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?></td>
                                    <td><?= htmlspecialchars(
                                        $product['cat_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?></td>
                                    <td><?= htmlspecialchars(
                                        $product['brand_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?></td>
                                    <td><?= htmlspecialchars(
                                        '₵ ' . number_format(
                                            (float) $product['product_price'],
                                            2
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?></td>
                                    <td class="admin-row-actions">
                                        <a
                                            class="admin-edit-button"
                                            href="<?= htmlspecialchars(
                                                app_url(
                                                    'views/admin/product.php?edit_id='
                                                        . (int) $product['product_id']
                                                ),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                        >Edit</a>
                                        <form
                                            method="post"
                                            action="<?= htmlspecialchars(
                                                app_url('actions/delete_product_action.php'),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                            onsubmit="return confirm('Are you sure you want to delete this product? Products in order history cannot be deleted.');"
                                        >
                                            <input
                                                type="hidden"
                                                name="product_id"
                                                value="<?= (int) $product['product_id']; ?>"
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

    <script>
        const categorySelect = document.getElementById('product-category');
        const brandSelect = document.getElementById('product-brand');
        const brandOptions = Array.from(brandSelect.options).filter(
            (option) => option.dataset.category
        );

        const updateBrandOptions = (resetSelection) => {
            const categoryId = categorySelect.value;
            if (resetSelection) {
                brandSelect.value = '';
            }
            brandOptions.forEach((option) => {
                option.hidden = option.dataset.category !== categoryId;
            });

            brandSelect.disabled = !categoryId;
            brandSelect.options[0].textContent = categoryId
                ? 'Choose a brand'
                : 'Choose a category first';
        };

        categorySelect.addEventListener('change', () => updateBrandOptions(true));
        updateBrandOptions(false);
    </script>
    <?php require __DIR__ . '/../layout/footer.php'; ?>
</body>
</html>