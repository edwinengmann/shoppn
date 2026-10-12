<?php

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}

?>

<header class="site-header">
    <a
        href="<?= htmlspecialchars(
            app_url(
                is_logged_in()
                    ? 'index.php?welcome=1'
                    : 'index.php'
            ),
            ENT_QUOTES,
            'UTF-8'
        ); ?>"
        class="site-logo"
    >
        Shoppn
    </a>

    <nav class="site-nav">
        <a href="<?= htmlspecialchars(
            app_url('views/all_products.php'),
            ENT_QUOTES,
            'UTF-8'
        ); ?>">Shop</a>

        <?php if (is_admin()): ?>
            <a href="<?= htmlspecialchars(app_url('views/admin/customers.php'), ENT_QUOTES, 'UTF-8'); ?>">Customers</a>
            <a href="<?= htmlspecialchars(app_url('views/admin/brand.php'), ENT_QUOTES, 'UTF-8'); ?>">Brands</a>
            <a href="<?= htmlspecialchars(app_url('views/admin/category.php'), ENT_QUOTES, 'UTF-8'); ?>">Categories</a>
            <a href="<?= htmlspecialchars(app_url('views/admin/product.php'), ENT_QUOTES, 'UTF-8'); ?>">Products</a>
        <?php endif; ?>
    </nav>

    <nav class="site-utilities" aria-label="Account and shopping">
        <a
            class="site-icon-link"
            href="<?= htmlspecialchars(
                app_url('views/all_products.php#catalog-search-input'),
                ENT_QUOTES,
                'UTF-8'
            ); ?>"
            aria-label="Search products"
            title="Search"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="10.8" cy="10.8" r="6.8"></circle>
                <path d="m16 16 5 5"></path>
            </svg>
        </a>
        <a
            class="site-icon-link"
            href="<?= htmlspecialchars(
                app_url(
                    is_logged_in()
                        ? 'views/account/my_account.php'
                        : 'views/login.php'
                ),
                ENT_QUOTES,
                'UTF-8'
            ); ?>"
            aria-label="Account"
            title="Account"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="7.5" r="3.5"></circle>
                <path d="M4.5 21v-2.2a6 6 0 0 1 6-6h3a6 6 0 0 1 6 6V21"></path>
            </svg>
        </a>
        <?php if (is_logged_in()): ?>
            <?php if (!is_admin()): ?>
                <a
                    class="site-icon-link"
                    href="<?= htmlspecialchars(app_url('views/cart.php'), ENT_QUOTES, 'UTF-8'); ?>"
                    aria-label="Shopping cart"
                    title="Shopping cart"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3 4h2l2.2 11.2a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 1.9-1.4L22 8H6"></path>
                        <circle cx="10" cy="21" r="1"></circle>
                        <circle cx="18" cy="21" r="1"></circle>
                    </svg>
                </a>
            <?php endif; ?>
            <a
                class="site-icon-link"
                href="<?= htmlspecialchars(app_url('logout.php'), ENT_QUOTES, 'UTF-8'); ?>"
                aria-label="Log out"
                title="Log out"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5"></path>
                    <path d="M14 16l4-4-4-4m4 4H9"></path>
                </svg>
            </a>
        <?php elseif (!is_logged_in()): ?>
            <a
                class="site-icon-link"
                href="<?= htmlspecialchars(app_url('views/register.php'), ENT_QUOTES, 'UTF-8'); ?>"
                aria-label="Register"
                title="Register"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9.5" cy="7" r="4"></circle>
                    <path d="M19 8v6m-3-3h6"></path>
                </svg>
            </a>
        <?php endif; ?>
    </nav>
</header>