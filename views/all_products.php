<?php

require_once __DIR__ . '/../core/core.php';

require_login();

require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController();
$categories = $controller->getAllCategories();
$allBrands = array_values(array_filter(
    $controller->getAllBrands(),
    function ($brand) {
        return !empty($brand['brand_cat']);
    }
));

$categoryId = null;
if (isset($_GET['category'])) {
    $rawCategoryId = $_GET['category'];
    $validatedCategoryId = is_string($rawCategoryId)
        ? filter_var(
            $rawCategoryId,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        )
        : false;
    if ($validatedCategoryId !== false) {
        $categoryId = $validatedCategoryId;
    }
}

$brandId = null;
if (isset($_GET['brand'])) {
    $rawBrandId = $_GET['brand'];
    $validatedBrandId = is_string($rawBrandId)
        ? filter_var(
            $rawBrandId,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        )
        : false;
    if ($validatedBrandId !== false) {
        $brandId = $validatedBrandId;
    }
}

$searchInput = $_GET['search'] ?? '';
$search = is_string($searchInput) ? trim($searchInput) : '';
$search = $search === '' ? null : substr($search, 0, 100);
$requestedCategoryId = $categoryId;
$selectedBrand = $brandId === null
    ? false
    : $controller->getBrandById($brandId);

if ($selectedBrand) {
    $brandCategoryId = (int) $selectedBrand['brand_cat'];
    if (
        $requestedCategoryId !== null
        && $requestedCategoryId !== $brandCategoryId
    ) {
        $brandId = null;
        $selectedBrand = false;
    } else {
        $categoryId = $brandCategoryId;
    }
} elseif ($brandId !== null) {
    $brandId = null;
}

$selectedCategory = $categoryId === null
    ? false
    : $controller->getCategoryById($categoryId);
$brands = $controller->getBrandsByCategory($categoryId);
$products = $controller->getCatalogProducts($categoryId, $brandId, $search);

function catalog_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | Shoppn</title>
    <link
        rel="stylesheet"
        href="<?= catalog_escape(
            app_url('css/style.css')
                . '?v='
                . filemtime(__DIR__ . '/../css/style.css')
        ); ?>"
    >
</head>
<body class="catalog-page">
    <?php require __DIR__ . '/layout/header.php'; ?>

    <main class="catalog-content">
        <header class="catalog-heading">
            <p class="catalog-eyebrow">
                <?= $selectedBrand
                    ? catalog_escape($selectedBrand['brand_name'])
                    : 'SHOP THE COLLECTION'; ?>
            </p>
            <h1>
                <?= $selectedCategory
                    ? catalog_escape($selectedCategory['cat_name'])
                    : ($selectedBrand
                        ? catalog_escape($selectedBrand['brand_name'])
                        : 'Shop all products'); ?>
            </h1>
        </header>

        <form
            class="catalog-search"
            method="get"
            action="<?= catalog_escape(app_url('views/all_products.php')); ?>"
            role="search"
        >
            <label class="visually-hidden" for="catalog-search-input">
                Search products
            </label>
            <input
                id="catalog-search-input"
                type="search"
                name="search"
                maxlength="100"
                placeholder="Search products, brands, or categories"
                value="<?= catalog_escape($search ?? ''); ?>"
            >
            <?php if ($categoryId !== null): ?>
                <input type="hidden" name="category" value="<?= (int) $categoryId; ?>">
            <?php endif; ?>
            <?php if ($brandId !== null): ?>
                <input type="hidden" name="brand" value="<?= (int) $brandId; ?>">
            <?php endif; ?>
            <button type="submit">Search</button>
        </form>

        <section class="catalog-section" aria-labelledby="categories-heading">
            <div class="catalog-section-heading">
                <h2 id="categories-heading">Shop by category</h2>
                <?php if ($categoryId !== null): ?>
                    <a href="<?= catalog_escape(app_url('views/all_products.php')); ?>">
                        All categories
                    </a>
                <?php endif; ?>
            </div>
            <?php if ($categories): ?>
                <ul class="catalog-tag-list">
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a
                                class="<?= $categoryId === (int) $category['cat_id']
                                    ? 'is-active'
                                    : ''; ?>"
                                href="<?= catalog_escape(
                                    app_url(
                                        'views/all_products.php?category='
                                            . (int) $category['cat_id']
                                    )
                                ); ?>"
                            >
                                <?= catalog_escape($category['cat_name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="catalog-empty">No categories have been added yet.</p>
            <?php endif; ?>
        </section>

        <form
            class="catalog-filters"
            method="get"
            action="<?= catalog_escape(app_url('views/all_products.php')); ?>"
        >
            <?php if ($search !== null): ?>
                <input
                    type="hidden"
                    name="search"
                    value="<?= catalog_escape($search); ?>"
                >
            <?php endif; ?>
            <label>
                Category
                <select name="category" id="catalog-category-filter">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?= (int) $category['cat_id']; ?>"
                            <?= $categoryId === (int) $category['cat_id']
                                ? 'selected'
                                : ''; ?>
                        >
                            <?= catalog_escape($category['cat_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Brand
                <select name="brand" id="catalog-brand-filter">
                    <option value="">All brands</option>
                    <?php foreach ($allBrands as $brand): ?>
                        <option
                            value="<?= (int) $brand['brand_id']; ?>"
                            data-category="<?= (int) $brand['brand_cat']; ?>"
                            <?= $brandId === (int) $brand['brand_id']
                                ? 'selected'
                                : ''; ?>
                        >
                            <?= catalog_escape($brand['brand_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 7h16M4 17h16"></path>
                    <path d="M9 4v6m6 4v6"></path>
                </svg>
                Filter
            </button>
            <?php if ($categoryId !== null || $brandId !== null): ?>
                <a href="<?= catalog_escape(app_url('views/all_products.php')); ?>">
                    Clear filters
                </a>
            <?php endif; ?>
        </form>

        <?php if ($categoryId !== null): ?>
            <section class="catalog-section" aria-labelledby="brands-heading">
                <h2 id="brands-heading">
                    Brands<?= $selectedCategory
                        ? ' in ' . catalog_escape($selectedCategory['cat_name'])
                        : ''; ?>
                </h2>
                <?php if ($brands): ?>
                    <ul class="catalog-brand-grid">
                        <?php foreach ($brands as $brand): ?>
                            <li>
                                <a
                                    class="catalog-brand-card <?= $brandId
                                        === (int) $brand['brand_id']
                                        ? 'is-active'
                                        : ''; ?>"
                                    href="<?= catalog_escape(
                                        app_url(
                                            'views/all_products.php?category='
                                                . (int) $categoryId
                                                . '&brand='
                                                . (int) $brand['brand_id']
                                        )
                                    ); ?>"
                                >
                                    <?php if (!empty($brand['brand_image'])): ?>
                                        <img
                                            class="catalog-brand-logo"
                                            src="<?= catalog_escape(app_url($brand['brand_image'])); ?>"
                                            alt="<?= catalog_escape($brand['brand_name']); ?> logo"
                                            loading="lazy"
                                        >
                                    <?php else: ?>
                                        <span class="catalog-brand-placeholder" aria-hidden="true">
                                            <?= catalog_escape(
                                                strtoupper(substr($brand['brand_name'], 0, 1))
                                            ); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="catalog-brand-name">
                                        <?= catalog_escape($brand['brand_name']); ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="catalog-empty">
                        No brands have been added to this category yet.
                    </p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <section
            class="catalog-section"
            id="products"
            aria-labelledby="products-heading"
        >
            <div class="catalog-products-heading">
                <h2 id="products-heading">
                    <?= $search !== null ? 'Search results' : 'Products'; ?>
                </h2>
                <p><?= count($products); ?> products</p>
            </div>
            <?php if ($products): ?>
                <div class="catalog-product-grid">
                    <?php foreach ($products as $product): ?>
                        <a
                            class="catalog-product-card"
                            id="product-<?= (int) $product['product_id']; ?>"
                            href="<?= catalog_escape(
                                app_url(
                                    'views/single_product.php?id='
                                        . (int) $product['product_id']
                                )
                            ); ?>"
                        >
                            <?php if (!empty($product['product_image'])): ?>
                                <img
                                    class="catalog-product-image"
                                    src="<?= catalog_escape(app_url($product['product_image'])); ?>"
                                    alt="<?= catalog_escape($product['product_title']); ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <div class="catalog-product-image-placeholder">
                                    Image coming soon
                                </div>
                            <?php endif; ?>
                            <div class="catalog-product-details">
                                <p class="catalog-product-brand">
                                    <?= catalog_escape($product['brand_name']); ?>
                                </p>
                                <h3><?= catalog_escape($product['product_title']); ?></h3>
                                <p class="catalog-product-category">
                                    <?= catalog_escape($product['cat_name']); ?>
                                </p>
                                <p class="catalog-product-price">
                                    ₵
                                    <?= catalog_escape(
                                        number_format((float) $product['product_price'], 2)
                                    ); ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="catalog-empty">
                    No products are available yet. Check back soon.
                </p>
            <?php endif; ?>
        </section>
    </main>

    <?php require __DIR__ . '/layout/footer.php'; ?>
    <script>
        const searchInput = document.getElementById('catalog-search-input');
        const categoryFilter = document.getElementById('catalog-category-filter');
        const brandFilter = document.getElementById('catalog-brand-filter');
        const brandOptions = Array.from(brandFilter.options).filter(
            (option) => option.dataset.category
        );
        let searchTimeout;

        const updateBrandOptions = (resetSelection) => {
            if (resetSelection) {
                const selectedBrand = brandFilter.options[brandFilter.selectedIndex];
                if (
                    selectedBrand
                    && selectedBrand.dataset.category
                    && selectedBrand.dataset.category !== categoryFilter.value
                ) {
                    brandFilter.value = '';
                }
            }

            brandOptions.forEach((option) => {
                option.hidden = Boolean(
                    categoryFilter.value
                    && option.dataset.category !== categoryFilter.value
                );
            });
        };

        categoryFilter.addEventListener('change', () => {
            updateBrandOptions(true);
        });
        updateBrandOptions(false);

        searchInput.addEventListener('input', () => {
            window.clearTimeout(searchTimeout);
            searchTimeout = window.setTimeout(() => {
                searchInput.form.requestSubmit();
            }, 450);
        });
    </script>
</body>
</html>
