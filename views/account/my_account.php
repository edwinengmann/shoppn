<?php

require_once __DIR__ . '/../../core/core.php';

require_login();

require_once __DIR__ . '/../../controllers/CustomerController.php';

$controller = new CustomerController();
$customer = $controller->getCustomerById((int) $_SESSION['customer_id']);
if (!$customer) {
    $_SESSION['error'] = 'Your account could not be found. Please log in again.';
    redirect(app_url('logout.php'));
}

$successMessage = $_SESSION['success'] ?? '';
$errorMessage = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

function account_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Shoppn</title>
    <link
        rel="stylesheet"
        href="<?= account_escape(
            app_url('css/style.css')
                . '?v='
                . filemtime(__DIR__ . '/../../css/style.css')
        ); ?>"
    >
</head>
<body class="account-page">
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="account-content">
        <div class="account-heading">
            <div>
                <p class="account-eyebrow">SHOPPN ACCOUNT</p>
                <h1>My account</h1>
            </div>
            <a class="account-sign-out" href="<?= account_escape(app_url('logout.php')); ?>">
                Sign out
            </a>
        </div>

        <nav class="account-tabs" aria-label="Account sections">
            <a class="is-active" href="#profile">Profile</a>
            <a href="#shipping">Delivery details</a>
        </nav>

        <?php if ($successMessage !== ''): ?>
            <p class="account-message account-success" role="status">
                <?= account_escape($successMessage); ?>
            </p>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <p class="account-message account-error" role="alert">
                <?= account_escape($errorMessage); ?>
            </p>
        <?php endif; ?>

        <form
            id="profile"
            class="account-form"
            method="post"
            action="<?= account_escape(app_url('actions/update_account_action.php')); ?>"
            enctype="multipart/form-data"
        >
            <div class="account-photo">
                <?php if (!empty($customer['customer_image'])): ?>
                    <img
                        src="<?= account_escape(app_url($customer['customer_image'])); ?>"
                        alt="<?= account_escape($customer['customer_name']); ?> profile photo"
                    >
                <?php else: ?>
                    <span aria-hidden="true">
                        <?= account_escape(
                            strtoupper(substr($customer['customer_name'], 0, 1))
                        ); ?>
                    </span>
                <?php endif; ?>
                <label class="account-photo-button" for="customer-image">
                    Change photo
                </label>
                <input
                    id="customer-image"
                    type="file"
                    name="customer_image"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    disabled
                >
                <?php if (!empty($customer['customer_image'])): ?>
                    <label class="account-remove-photo">
                        <input
                            type="checkbox"
                            name="remove_customer_image"
                            value="1"
                            disabled
                        >
                        Remove current photo
                    </label>
                <?php endif; ?>
                <small>JPG, PNG, GIF, or WebP, up to 5 MB.</small>
            </div>

            <section class="account-form-section" aria-labelledby="personal-info-title">
                <h2 id="personal-info-title">Personal information</h2>
                <div class="account-fields">
                    <label class="account-field account-field-wide" for="customer-name">
                        Full name
                        <input
                            id="customer-name"
                            type="text"
                            name="customer_name"
                            maxlength="100"
                            autocomplete="name"
                            required
                            readonly
                            value="<?= account_escape($customer['customer_name']); ?>"
                        >
                    </label>
                    <label class="account-field account-field-wide" for="customer-email">
                        Email
                        <input
                            id="customer-email"
                            type="email"
                            name="customer_email"
                            maxlength="50"
                            autocomplete="email"
                            required
                            readonly
                            value="<?= account_escape($customer['customer_email']); ?>"
                        >
                    </label>
                </div>
            </section>

            <section
                id="shipping"
                class="account-form-section"
                aria-labelledby="shipping-info-title"
            >
                <h2 id="shipping-info-title">Contact and location</h2>
                <div class="account-fields">
                    <label class="account-field" for="customer-country">
                        Country
                        <input
                            id="customer-country"
                            type="text"
                            name="customer_country"
                            maxlength="30"
                            autocomplete="country-name"
                            required
                            data-profile-editable
                            readonly
                            value="<?= account_escape($customer['customer_country']); ?>"
                        >
                    </label>
                    <label class="account-field" for="customer-city">
                        City
                        <input
                            id="customer-city"
                            type="text"
                            name="customer_city"
                            maxlength="30"
                            autocomplete="address-level2"
                            required
                            data-profile-editable
                            readonly
                            value="<?= account_escape($customer['customer_city']); ?>"
                        >
                    </label>
                    <label class="account-field account-field-wide" for="customer-contact">
                        Phone number
                        <input
                            id="customer-contact"
                            type="tel"
                            name="customer_contact"
                            maxlength="15"
                            autocomplete="tel"
                            required
                            data-profile-editable
                            readonly
                            value="<?= account_escape($customer['customer_contact']); ?>"
                        >
                    </label>
                    <label class="account-field account-field-wide" for="customer-address">
                        Address
                        <textarea
                            id="customer-address"
                            name="customer_address"
                            maxlength="255"
                            autocomplete="street-address"
                            data-profile-editable
                            readonly
                        ><?= account_escape($customer['customer_address'] ?? ''); ?></textarea>
                    </label>
                </div>
            </section>

            <div class="account-form-actions">
                <button
                    class="account-edit-button"
                    type="button"
                    id="account-edit-button"
                    aria-pressed="false"
                >Edit details</button>
                <button type="submit" id="account-save-button" disabled>
                    Save changes
                </button>
            </div>
        </form>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script>
        (function () {
            const form = document.querySelector('.account-form');
            const editButton = document.getElementById('account-edit-button');
            const saveButton = document.getElementById('account-save-button');
            const editableFields = form.querySelectorAll('[data-profile-editable]');
            const photoInput = document.getElementById('customer-image');
            const removePhotoInput = form.querySelector(
                'input[name="remove_customer_image"]'
            );
            let isEditing = false;

            function setEditing(editing) {
                isEditing = editing;
                form.classList.toggle('is-editing', editing);
                editableFields.forEach(function (field) {
                    field.readOnly = !editing;
                });
                photoInput.disabled = !editing;
                if (removePhotoInput) {
                    removePhotoInput.disabled = !editing;
                }
                saveButton.disabled = !editing;
                editButton.textContent = editing ? 'Cancel' : 'Edit details';
                editButton.setAttribute('aria-pressed', String(editing));
            }

            editButton.addEventListener('click', function () {
                if (isEditing) {
                    form.reset();
                    setEditing(false);
                    return;
                }

                setEditing(true);
            });

            setEditing(false);
        }());
    </script>
</body>
</html>