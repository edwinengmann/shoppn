<?php

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}

?>


<header>


    <nav>

        <a href="<?= htmlspecialchars(app_url('index.php')); ?>">
            Home
        </a>


        <?php if (is_logged_in()): ?>


            <span>

                Welcome,
                <?= htmlspecialchars(
                    $_SESSION['customer_name']
                    ?? 'Customer'
                ); ?>

            </span>


            <a href="<?= htmlspecialchars(app_url('views/account/my_account.php')); ?>">
                My Account
            </a>


            <?php if (is_admin()): ?>

                <a href="<?= htmlspecialchars(app_url('views/admin/brand.php')); ?>">
                    Brands
                </a>

                <a href="<?= htmlspecialchars(app_url('views/admin/category.php')); ?>">
                    Categories
                </a>

                <a href="<?= htmlspecialchars(app_url('views/admin/product.php')); ?>">
                    Products
                </a>

            <?php endif; ?>


            <a href="<?= htmlspecialchars(app_url('logout.php')); ?>">
                Logout
            </a>


        <?php else: ?>


            <a href="<?= htmlspecialchars(app_url('views/register.php')); ?>">
                Register
            </a>


            <a href="<?= htmlspecialchars(app_url('views/login.php')); ?>">
                Login
            </a>


        <?php endif; ?>

    </nav>


    <!-- Search -->

    <form
        action="<?= htmlspecialchars(app_url('views/search_results.php')); ?>"
        method="GET"
    >

        <input
            type="search"
            name="user_query"
            placeholder="Search products"
        >

        <button type="submit">
            Search
        </button>

    </form>


</header>