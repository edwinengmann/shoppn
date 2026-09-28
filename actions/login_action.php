<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../views/login.php');
}

$email = strip_tags(trim($_POST['customer_email'] ?? ''));
$pass = $_POST['customer_pass'] ?? '';

if (empty($email) || empty($pass)) {
    $_SESSION['error'] = 'Email and password are required.';
    redirect('../views/login.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    redirect('../views/login.php');
}

$controller = new CustomerController();
$result = $controller->login($email, $pass);

if (!$result['success']) {
    $_SESSION['error'] = $result['error'];
    redirect('../views/login.php');
}

$customer = $result['customer'];
session_regenerate_id(true);
$_SESSION['customer_id'] = $customer['customer_id'];
$_SESSION['customer_name'] = $customer['customer_name'];
$_SESSION['customer_email'] = $customer['customer_email'];
$_SESSION['user_role'] = (int) $customer['user_role'];

redirect('../index.php');

?>
