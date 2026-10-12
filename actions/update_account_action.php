<?php

require_once __DIR__ . '/../core/core.php';

require_login();

$redirectUrl = app_url('views/account/my_account.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Please submit the account form.';
    redirect($redirectUrl);
}

$customerId = (int) $_SESSION['customer_id'];
$submittedCountry = $_POST['customer_country'] ?? '';
$country = is_string($submittedCountry)
    ? trim(strip_tags($submittedCountry))
    : '';
$submittedCity = $_POST['customer_city'] ?? '';
$city = is_string($submittedCity)
    ? trim(strip_tags($submittedCity))
    : '';
$submittedContact = $_POST['customer_contact'] ?? '';
$contact = is_string($submittedContact)
    ? trim(strip_tags($submittedContact))
    : '';
$submittedAddress = $_POST['customer_address'] ?? '';
$address = is_string($submittedAddress)
    ? trim(strip_tags($submittedAddress))
    : '';

if (strlen($country) < 2 || strlen($country) > 30) {
    $_SESSION['error'] = 'Country must be between 2 and 30 characters.';
    redirect($redirectUrl);
}

if (strlen($city) < 2 || strlen($city) > 30) {
    $_SESSION['error'] = 'City must be between 2 and 30 characters.';
    redirect($redirectUrl);
}

if (!preg_match('/^[0-9+\-\s]{7,15}$/', $contact)) {
    $_SESSION['error'] = 'Enter a valid contact number.';
    redirect($redirectUrl);
}

if (strlen($address) > 255) {
    $_SESSION['error'] = 'Address must not exceed 255 characters.';
    redirect($redirectUrl);
}

require_once __DIR__ . '/../controllers/CustomerController.php';
require_once __DIR__ . '/../core/customer_image.php';

$controller = new CustomerController();
$customer = $controller->getCustomerById($customerId);
if (!$customer) {
    $_SESSION['error'] = 'Your account could not be found. Please log in again.';
    redirect(app_url('logout.php'));
}

$upload = save_customer_image_upload($_FILES['customer_image'] ?? []);
if ($upload['error'] !== null) {
    $_SESSION['error'] = $upload['error'];
    redirect($redirectUrl);
}

$removeImage = isset($_POST['remove_customer_image'])
    && $_POST['remove_customer_image'] === '1';
$imagePath = $upload['path'] ?? (
    $removeImage ? null : ($customer['customer_image'] ?? null)
);

$result = $controller->updateCustomerProfile(
    $customerId,
    [
        'country' => $country,
        'city' => $city,
        'contact' => $contact,
        'address' => $address,
        'image' => $imagePath
    ]
);

if (!$result['success']) {
    if ($upload['path'] !== null) {
        delete_customer_image($upload['path']);
    }
    $_SESSION['error'] = $result['error'];
    redirect($redirectUrl);
}

if ($upload['path'] !== null || $removeImage) {
    delete_customer_image($customer['customer_image'] ?? null);
}

$_SESSION['success'] = 'Your account details have been updated.';
redirect($redirectUrl);
