<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('views/register.php'));
}

$name = strip_tags(trim($_POST['customer_name'] ?? ''));
$email = strip_tags(trim($_POST['customer_email'] ?? ''));
$pass = $_POST['customer_pass'] ?? '';
$country = strip_tags(trim($_POST['customer_country'] ?? ''));
$city = strip_tags(trim($_POST['customer_city'] ?? ''));
$contact = strip_tags(trim($_POST['customer_contact'] ?? ''));
$_SESSION['_old_register'] = [
    'name' => $name,
    'email' => $email,
    'country' => $country,
    'city' => $city,
    'contact' => $contact
];

$errors = [];

if (strlen($name) < 2 || strlen($name) > 100) {
    $errors[] = 'Name must be between 2 and 100 characters.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    $errors[] = 'Please enter a valid email address.';
}

if (strlen($pass) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}

if (!preg_match('/[A-Z]/', $pass)) {
    $errors[] = 'Password must contain at least one uppercase letter.';
}

if (!preg_match('/[a-z]/', $pass)) {
    $errors[] = 'Password must contain at least one lowercase letter.';
}

if (!preg_match('/[0-9]/', $pass)) {
    $errors[] = 'Password must contain at least one number.';
}

if (!preg_match('/[\W_]/', $pass)) {
    $errors[] = 'Password must contain at least one special character.';
}

if (strlen($country) < 2 || strlen($country) > 100) {
    $errors[] = 'Please select a country.';
}

if (strlen($city) < 2 || strlen($city) > 100) {
    $errors[] = 'City must be between 2 and 100 characters.';
}

if (!preg_match('/^[0-9+\-\s]{7,15}$/', $contact)) {
    $errors[] = 'Please enter a valid contact number.';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode("\n", $errors);
    redirect(app_url('views/register.php'));
}

$controller = new CustomerController();
$result = $controller->register([
    'name' => $name,
    'email' => $email,
    'pass' => $pass,
    'country' => $country,
    'city' => $city,
    'contact' => $contact
]);

if ($result['success']) {
    unset($_SESSION['_old_register']);
    $_SESSION['success'] = 'Registration successful. Please login to continue.';
    redirect(app_url('views/login.php'));
}

$_SESSION['error'] = $result['error'];
redirect(app_url('views/register.php'));
