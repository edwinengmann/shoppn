<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/paystack.php';
require_once __DIR__ . '/../classes/OrderPaymentClass.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(app_url('views/checkout.php'));
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (
    !is_string($csrfToken)
    || empty($_SESSION['checkout_csrf_token'])
    || !hash_equals($_SESSION['checkout_csrf_token'], $csrfToken)
) {
    $_SESSION['error'] = 'Your checkout session expired. Please try again.';
    redirect(app_url('views/checkout.php'));
}

$allowedChannels = [
    'card',
    'mobile_money',
    'bank',
    'bank_transfer',
    'ussd',
    'qr'
];
$requestedChannels = $_POST['channels'] ?? [];
if (!is_array($requestedChannels)) {
    $requestedChannels = [];
}
$channels = array_values(array_unique(array_filter(
    $requestedChannels,
    function ($channel) use ($allowedChannels) {
        return is_string($channel)
            && in_array($channel, $allowedChannels, true);
    }
)));

if (!$channels) {
    $_SESSION['error'] = 'Select at least one payment method.';
    redirect(app_url('views/checkout.php'));
}
if (!paystack_is_configured()) {
    $_SESSION['error'] = 'Online payments are not configured. Please contact the store.';
    redirect(app_url('views/checkout.php'));
}

try {
    $customerId = (int) $_SESSION['customer_id'];
    $cartController = new CartController();
    $items = $cartController->getCartItems($customerId);
    if (!$items) {
        $_SESSION['error'] = 'Your shopping bag is empty.';
        redirect(app_url('views/cart.php'));
    }

    $customerController = new CustomerController();
    $customer = $customerController->getCustomerById($customerId);
    if (!$customer) {
        $_SESSION['error'] = 'Your account could not be found. Please log in again.';
        redirect(app_url('logout.php'));
    }

    $reference = 'shoppn_' . bin2hex(random_bytes(20));
    $orderModel = new OrderPaymentClass();
    $order = $orderModel->createPendingOrder(
        $customerId,
        $customer,
        $items,
        $reference,
        $channels
    );

    $payment = paystack_request('POST', '/transaction/initialize', [
        'email' => $customer['customer_email'],
        'amount' => $order['amount_minor'],
        'currency' => 'GHS',
        'reference' => $reference,
        'channels' => $channels,
        'callback_url' => paystack_callback_url(),
        'metadata' => [
            'order_id' => $order['order_id'],
            'customer_id' => $customerId
        ]
    ]);

    $authorizationUrl = $payment['authorization_url'] ?? '';
    $returnedReference = $payment['reference'] ?? '';
    if (
        !is_string($authorizationUrl)
        || !filter_var($authorizationUrl, FILTER_VALIDATE_URL)
        || parse_url($authorizationUrl, PHP_URL_SCHEME) !== 'https'
        || $returnedReference !== $reference
    ) {
        throw new RuntimeException('Paystack returned an invalid checkout URL.');
    }

    unset($_SESSION['checkout_csrf_token']);
    redirect($authorizationUrl);
} catch (Throwable $exception) {
    error_log('Starting Paystack checkout failed: ' . $exception->getMessage());
    $_SESSION['error'] = 'We could not start your payment. Your order details have been saved; please try again or contact the store.';
    redirect(app_url('views/checkout.php'));
}