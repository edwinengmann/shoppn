<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/paystack.php';
require_once __DIR__ . '/../classes/OrderPaymentClass.php';

$reference = $_GET['reference'] ?? '';
if (!is_string($reference) || !preg_match('/\Ashoppn_[a-f0-9]{40}\z/', $reference)) {
    $_SESSION['error'] = 'The payment reference is invalid.';
    redirect(app_url('views/payment_failed.php'));
}

try {
    $orders = new OrderPaymentClass();
    $payment = $orders->getPaymentByReference($reference);
    if (!$payment) {
        $_SESSION['error'] = 'We could not match this payment to an order.';
        redirect(app_url('views/payment_failed.php'));
    }

    $verified = paystack_request(
        'GET',
        '/transaction/verify/' . rawurlencode($reference)
    );
    if (($verified['reference'] ?? '') !== $reference) {
        throw new RuntimeException('Paystack verification reference mismatch.');
    }

    $status = $verified['status'] ?? '';
    if ($status === 'success') {
        $confirmed = $orders->confirmPayment(
            $reference,
            (int) ($verified['amount'] ?? 0),
            (string) ($verified['currency'] ?? ''),
            (string) ($verified['id'] ?? ''),
            (string) ($verified['channel'] ?? '')
        );
        if (!$confirmed) {
            throw new RuntimeException('Verified Paystack amount or currency did not match the order.');
        }

        $_SESSION['payment_confirmed'] = true;
        redirect(app_url('views/payment_success.php'));
    }

    if ($status === 'failed') {
        $orders->markPaymentFailed($reference, (string) ($verified['id'] ?? ''));
    }

    $_SESSION['error'] = 'Payment was not confirmed. You can try again or contact the store.';
    redirect(app_url('views/payment_failed.php'));
} catch (Throwable $exception) {
    error_log('Verifying Paystack payment failed: ' . $exception->getMessage());
    $_SESSION['error'] = 'We could not confirm your payment yet. Please contact the store before trying again.';
    redirect(app_url('views/payment_failed.php'));
}
