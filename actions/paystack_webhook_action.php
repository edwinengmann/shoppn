<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/paystack.php';
require_once __DIR__ . '/../classes/OrderPaymentClass.php';

header('Content-Type: application/json; charset=utf-8');

function paystack_webhook_response($statusCode, $message)
{
    http_response_code($statusCode);
    echo json_encode(['message' => $message]);
    exit;
}

$secretKey = paystack_secret_key();
if ($secretKey === '') {
    paystack_webhook_response(503, 'Payment service is not configured.');
}

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
if (
    !is_string($payload)
    || !is_string($signature)
    || !hash_equals(hash_hmac('sha512', $payload, $secretKey), $signature)
) {
    paystack_webhook_response(401, 'Invalid signature.');
}

$event = json_decode($payload, true);
if (!is_array($event) || !isset($event['event'], $event['data'])) {
    paystack_webhook_response(400, 'Invalid event payload.');
}

$data = $event['data'];
$reference = is_array($data) ? ($data['reference'] ?? '') : '';
if (
    !is_string($reference)
    || !preg_match('/\Ashoppn_[a-f0-9]{40}\z/', $reference)
) {
    paystack_webhook_response(200, 'Event ignored.');
}

try {
    $orders = new OrderPaymentClass();
    $payment = $orders->getPaymentByReference($reference);
    if (!$payment) {
        paystack_webhook_response(200, 'Event ignored.');
    }

    if ($event['event'] === 'charge.success') {
        $confirmed = $orders->confirmPayment(
            $reference,
            (int) ($data['amount'] ?? 0),
            (string) ($data['currency'] ?? ''),
            (string) ($data['id'] ?? ''),
            (string) ($data['channel'] ?? '')
        );
        if (!$confirmed) {
            error_log('Paystack webhook amount or currency mismatch for ' . $reference);
            paystack_webhook_response(200, 'Payment did not match the order.');
        }
    } elseif ($event['event'] === 'charge.failed') {
        $orders->markPaymentFailed($reference, (string) ($data['id'] ?? ''));
    }

    paystack_webhook_response(200, 'Event received.');
} catch (Throwable $exception) {
    error_log('Processing Paystack webhook failed: ' . $exception->getMessage());
    paystack_webhook_response(500, 'Event processing failed.');
}
