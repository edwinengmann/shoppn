<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('views/login.php'));
}

$email = strip_tags(trim($_POST['customer_email'] ?? ''));
$pass = $_POST['customer_pass'] ?? '';
$_SESSION['_old_login'] = ['email' => $email];

if (empty($email) || empty($pass)) {
    $_SESSION['error'] = 'Email and password are required.';
    redirect(app_url('views/login.php'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    redirect(app_url('views/login.php'));
}

$controller = new CustomerController();
$result = $controller->login($email, $pass);

if (!$result['success']) {
    $_SESSION['error'] = $result['error'];
    redirect(app_url('views/login.php'));
}

$customer = $result['customer'];
unset($_SESSION['_old_login']);
session_regenerate_id(true);
$_SESSION['customer_id'] = $customer['customer_id'];
$_SESSION['customer_name'] = $customer['customer_name'];
$_SESSION['customer_email'] = $customer['customer_email'];
$_SESSION['user_role'] = (int) $customer['user_role'];

redirect(app_url('index.php?welcome=1'));

?>
