<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../views/register.php');
}

$name = strip_tags(trim($_POST['customer_name'] ?? ''));
$email = strip_tags(trim($_POST['customer_email'] ?? ''));
$pass = $_POST['customer_pass'] ?? '';
$country = strip_tags(trim($_POST['customer_country'] ?? ''));
$city = strip_tags(trim($_POST['customer_city'] ?? ''));
$contact = strip_tags(trim($_POST['customer_contact'] ?? ''));

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
    $_SESSION['error'] = implode('<br>', $errors);
    redirect('../views/register.php');
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
    $_SESSION['success'] = 'Registration successful. Please login to continue.';
    redirect('../views/login.php');
}

$_SESSION['error'] = $result['error'];
redirect('../views/register.php');

?>
